<?php

include('session.php');

header('Content-Type: application/json');

$member_id = $_SESSION['member_id'] ?? 0;

/* =========================
   DISTANCE FUNCTION (Haversine, meters)
========================= */

function distanceMeter($lat1, $lon1, $lat2, $lon2)
{
    $earth = 6371000;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a =
        sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) *
        cos(deg2rad($lat2)) *
        sin($dLon / 2) *
        sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earth * $c;
}

/* =========================
   INPUT (all via POST from scan.php)
========================= */

$token   = $obj->test_input($_POST['token'] ?? '');
$lat     = floatval($_POST['lat'] ?? 0);
$lng     = floatval($_POST['lng'] ?? 0);
$acc     = floatval($_POST['accuracy'] ?? 0);
$address = $obj->test_input($_POST['address'] ?? '');
$city    = $obj->test_input($_POST['city'] ?? '');
$state   = $obj->test_input($_POST['state'] ?? '');
$country = $obj->test_input($_POST['country'] ?? '');

/* =========================
   VALIDATION
========================= */

if (!$member_id) {
    echo json_encode([
        'status'  => false,
        'title'   => 'Session Expired',
        'message' => 'Please login again.'
    ]);
    exit;
}

if (!$token || !$lat || !$lng) {
    echo json_encode([
        'status'  => false,
        'title'   => 'Invalid Request',
        'message' => 'QR token or GPS missing.'
    ]);
    exit;
}

/* =========================
   MEETING (still called meeting in DB) CHECK
   - QR token must be valid & active
   - No time window check — QR is valid until deleted or edited
========================= */

$meeting = $obj->executequery("
    SELECT *
    FROM bni_meetings
    WHERE qr_token = '$token'
      AND status = 1
    LIMIT 1
");

if (!$meeting) {
    echo json_encode([
        'status'  => false,
        'title'   => 'Invalid QR',
        'message' => 'QR code invalid or inactive.'
    ]);
    exit;
}

$meeting = $meeting[0];
$now     = date('Y-m-d H:i:s');

/* NOTE: Time-window check removed.
   QR is valid from creation until deleted or edited (status set to 0). */

/* =========================
   LOCATION CHECK (Haversine distance vs radius_meter)
========================= */

$distance = distanceMeter(
    $meeting['latitude'],
    $meeting['longitude'],
    $lat,
    $lng
);

if ($distance > $meeting['radius_meter']) {
    echo json_encode([
        'status'  => false,
        'title'   => 'Outside Location',
        'message' => 'You are ' . number_format($distance, 2) .
                    ' meter away. Allowed radius is ' .
                    $meeting['radius_meter'] . ' meter.'
    ]);
    exit;
}

/* =========================================================
   AUTO-DETECT IN vs OUT  (Option A: server-side toggle)
   - If there's an open IN session today for this member+meeting,
     this scan becomes an OUT (UPDATE out_time).
   - Otherwise, this scan becomes a new IN (INSERT new row).
========================================================= */

$today = date('Y-m-d');

$openRow = $obj->executequery("
    SELECT attendance_id, scan_time
    FROM bni_attendance
    WHERE userid  = '$member_id'
      AND company_id = '{$meeting['company_id']}'
      AND DATE(scan_time) = '$today'
      AND out_time IS NULL
    ORDER BY attendance_id DESC
    LIMIT 1
")[0] ?? null;

/* =========================
   CASE 1: MARK OUT
========================= */

if ($openRow) {
    $obj->update_record(
        'bni_attendance',
        ['attendance_id' => $openRow['attendance_id']],
        ['out_time' => $now]
    );

    $durationSec = strtotime($now) - strtotime($openRow['scan_time']);
    $durationStr = formatDurationAtt($durationSec);

    echo json_encode([
        'status'  => true,
        'action'  => 'out',
        'title'   => 'Marked OUT',
        'message' => 'OUT time: ' . date('h:i A', strtotime($now)) .
                     '. Session duration: ' . $durationStr . '.',
    ]);
    exit;
}

/* =========================
   CASE 2: MARK IN  (insert new GPS session row)
   shop_id is taken from the meeting/QR row so we know which shop
   the employee scanned at.
   Multi-session safe: no duplicate check, just insert.
========================= */

$fields = [
    'meeting_id'     => $meeting['meeting_id'],
    'member_id'      => $member_id,
    'shop_id'        => $meeting['shop_id'],
    'scan_time'      => $now,
    'latitude'       => $lat,
    'longitude'      => $lng,
    'gps_accuracy'   => $acc,
    'distance_meter' => $distance,
    'address'        => $address,
    'city'           => $city,
    'state'          => $state,
    'country'        => $country,
    'createdby'      => $member_id,
    'status'         => 'Present',
    'type'           => 'GPS',
    'ip_address'     => $_SERVER['REMOTE_ADDR']
];

$obj->insert_record('bni_attendance', $fields);

echo json_encode([
    'status'  => true,
    'action'  => 'in',
    'title'   => 'Marked IN',
    'message' => 'IN time: ' . date('h:i A', strtotime($now)) .
                 '. Distance: ' . number_format($distance, 2) . ' meter.',
]);

exit;

/* =========================
   Local helper: format seconds as "Xh Ym"
========================= */

function formatDurationAtt($sec) {
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    if ($h > 0) return "{$h}h {$m}m";
    return "{$m}m";
}

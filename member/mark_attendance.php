<?php

include('session.php');

header('Content-Type: application/json');

$member_id = $_SESSION['member_id'] ?? 0;

/* =========================
   DISTANCE FUNCTION
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
   INPUT
========================= */

$token = mysqli_real_escape_string(
    $obj->con,
    $_POST['token'] ?? ''
);

$lat = floatval($_POST['lat'] ?? 0);
$lng = floatval($_POST['lng'] ?? 0);
$acc = floatval($_POST['accuracy'] ?? 0);

$address = mysqli_real_escape_string(
    $obj->con,
    $_POST['address'] ?? ''
);

$city = mysqli_real_escape_string(
    $obj->con,
    $_POST['city'] ?? ''
);

$state = mysqli_real_escape_string(
    $obj->con,
    $_POST['state'] ?? ''
);

$country = mysqli_real_escape_string(
    $obj->con,
    $_POST['country'] ?? ''
);

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
   MEETING CHECK
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

$now = date('Y-m-d H:i:s');

/* =========================
   QR TIME CHECK
========================= */

if ($now < $meeting['meeting_start'] || $now > $meeting['meeting_end']) {
    echo json_encode([
        'status'  => false,
        'title'   => 'QR Expired',
        'message' => 'Attendance time window is closed.'
    ]);
    exit;
}

/* =========================
   LOCATION CHECK
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

/* =========================
   DUPLICATE CHECK
========================= */

$duplicate = $obj->getvalfield(
    'bni_attendance',
    'count(*)',
    "meeting_id = '{$meeting['meeting_id']}' 
     AND member_id = '$member_id'"
);

if ($duplicate > 0) {
    echo json_encode([
        'status'  => true,
        'title'   => 'Already Marked',
        'message' => 'Your attendance is already marked for this meeting.'
    ]);
    exit;
}

/* =========================
   SAVE ATTENDANCE
========================= */

$fields = [
    'meeting_id'     => $meeting['meeting_id'],
    'member_id'      => $member_id,
    'scan_time'      => $now,

    'latitude'       => $lat,
    'longitude'      => $lng,
    'gps_accuracy'   => $acc,
    'distance_meter' => $distance,

    'address'        => $address,
    'city'           => $city,
    'state'          => $state,
    'country'        => $country,

    'status'         => 'Present',
    'ip_address'     => $_SERVER['REMOTE_ADDR']
];

$obj->insert_record(
    'bni_attendance',
    $fields
);

/* =========================
   RESPONSE
========================= */

echo json_encode([
    'status'  => true,
    'title'   => 'Attendance Marked',
    'message' => 'Present marked successfully. Distance: ' .
        number_format($distance, 2) . ' meter.'
]);

exit;

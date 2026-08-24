<?php
include_once('session.php');

header('Content-Type: application/json');
$memberid = $_SESSION['member_id'];

// Only coordinators
if (empty($_SESSION['attendance_coordinator'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$member_id  = (int) ($_POST['member_id']  ?? 0);
$meeting_id = (int) ($_POST['meeting_id'] ?? 0);
$shop_id    = (int) ($_POST['shop_id']    ?? 0);   // 0 = no shop selected

/* =========================================================
   Action parameter:
     - in    : mark employee IN  (insert NEW row, scan_time = now)
     - out   : mark employee OUT (update most recent OPEN row's out_time)
     - reset : delete ALL of today's rows for this member+meeting
   Legacy compatibility: present=1 -> action=in, present=0 -> action=reset
========================================================= */

$action = $_POST['action'] ?? '';
if ($action === '') {
    $present = (int) ($_POST['present'] ?? 0);
    $action  = $present ? 'in' : 'reset';
}

$now  = date('Y-m-d H:i:s');
$today = date('Y-m-d');

if (!$member_id || !$meeting_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

if (!in_array($action, ['in', 'out', 'reset'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

/* =========================================================
   Guard 1: QR must exist and be active (no time window check)
========================================================= */

$meeting = $obj->executequery("
    SELECT meeting_id
    FROM bni_meetings
    WHERE meeting_id  = '$meeting_id'
      AND status      = 1
    LIMIT 1
")[0] ?? null;

if (!$meeting) {
    echo json_encode(['success' => false, 'message' => 'QR is inactive or does not exist.']);
    exit;
}

/* =========================================================
   Guard 2: member must exist and be active
   (Chapter restriction REMOVED - employees travel between shops,
    any coordinator can mark any employee)
========================================================= */

$member = $obj->executequery("
    SELECT member_id
    FROM bni_members
    WHERE member_id  = '$member_id'
      AND status     = 1
    LIMIT 1
")[0] ?? null;

if (!$member) {
    echo json_encode(['success' => false, 'message' => 'Employee not found or inactive']);
    exit;
}

/* =========================================================
   Helper: build the response payload for a member's state
   Returns all of today's IN/OUT sessions for this member+meeting.
   Each session: { in_time: "h:i A", out_time: "h:i A"|null, duration_sec: int }
========================================================= */

function buildStateResponse($obj, $member_id, $meeting_id)
{
    $today = date('Y-m-d');

    $rows = $obj->executequery("
        SELECT a.attendance_id, a.shop_id, a.scan_time AS in_time, a.out_time,
               c.chapter_name AS shop_name
        FROM bni_attendance a
        LEFT JOIN chapter_master c ON c.chapter_id = a.shop_id
        WHERE a.member_id  = '$member_id'
          AND a.meeting_id = '$meeting_id'
          AND DATE(a.scan_time) = '$today'
        ORDER BY a.attendance_id ASC
    ");

    $sessions  = [];
    $hasOpen   = false;
    $totalSec  = 0;

    foreach ($rows as $r) {
        $inTs  = strtotime($r['in_time']);
        $outTs = $r['out_time'] ? strtotime($r['out_time']) : null;

        if ($outTs !== null) {
            $totalSec += ($outTs - $inTs);
        }

        if ($outTs === null) {
            $hasOpen = true;
        }

        $sessions[] = [
            'attendance_id' => (int) $r['attendance_id'],
            'shop_id'       => $r['shop_id'] ? (int) $r['shop_id'] : null,
            'shop_name'     => $r['shop_name'] ?: null,
            'in_time'       => date('h:i A', $inTs),
            'out_time'      => $outTs ? date('h:i A', $outTs) : null,
            'duration_sec'  => $outTs ? ($outTs - $inTs) : 0,
        ];
    }

    if (empty($sessions)) {
        $state = 'absent';
    } elseif ($hasOpen) {
        $state = 'in';   // has an open IN session
    } else {
        $state = 'out';  // all sessions closed
    }

    // Late/Early detection based on first IN time vs shift
    // Grace-aware (Option C): no badge within grace window.
    // Split grace: IN grace for late, OUT grace for early.
    // Lateness is measured from (shift_start + grace_in).
    // Early-leave is measured from (shift_end - grace_out).
    $targetMember = $obj->select_record('bni_members', ['member_id' => $member_id]);
    $shift = $obj->getShift($targetMember['shift_id'] ?? null);
    $shiftStartTs = strtotime($today . ' ' . $shift['start_time']);
    $shiftEndTs   = strtotime($today . ' ' . $shift['end_time']);
    $graceInSec   = (int)(($shift['grace_in_minutes']  ?? $shift['grace_minutes']) ?? 0) * 60;
    $graceOutSec  = (int)(($shift['grace_out_minutes'] ?? $shift['grace_minutes']) ?? 0) * 60;
    $expectedWorkSec = (float)($shift['expected_work_hours'] ?? 0) * 3600;
    $lateSec      = 0;
    $earlySec     = 0;
    $shortSec     = 0;

    if (!empty($sessions)) {
        $firstInTs = strtotime($rows[0]['in_time']);
        if ($firstInTs > ($shiftStartTs + $graceInSec)) {
            $lateSec = $firstInTs - ($shiftStartTs + $graceInSec);
        }
        // Early-leave: only check on the last OUT
        $last = end($sessions);
        if ($last['out_time'] !== null && $last['duration_sec'] > 0) {
            $lastOutTs = strtotime($rows[count($rows) - 1]['out_time']);
            if ($lastOutTs < ($shiftEndTs - $graceOutSec)) {
                $earlySec = ($shiftEndTs - $graceOutSec) - $lastOutTs;
            }
        }
        // Short hours: only when no open session, compare worked vs expected
        if (!$hasOpen && $expectedWorkSec > 0 && $totalSec < $expectedWorkSec) {
            $shortSec = (int)($expectedWorkSec - $totalSec);
        }
    }

    // Day status for salary: complete / incomplete / pending / absent
    $dayStatus = 'absent';
    if (!empty($sessions)) {
        if ($hasOpen) {
            $dayStatus = 'pending';
        } elseif ($expectedWorkSec > 0 && $totalSec < $expectedWorkSec) {
            $dayStatus = 'incomplete';
        } else {
            $dayStatus = 'complete';
        }
    }

    return [
        'state'           => $state,
        'day_status'      => $dayStatus,
        'sessions'        => $sessions,
        'worked_sec'      => $totalSec,
        'late_sec'        => $lateSec,
        'early_sec'       => $earlySec,
        'short_sec'       => $shortSec,
        'expected_hours'  => (float)($shift['expected_work_hours'] ?? 0),
        'shift_start'  => date('h:i A', $shiftStartTs),
        'shift_end'    => date('h:i A', $shiftEndTs),
        'lunch_start'  => $shift['lunch_start'] ? date('h:i A', strtotime($today . ' ' . $shift['lunch_start'])) : null,
        'lunch_end'    => $shift['lunch_end']   ? date('h:i A', strtotime($today . ' ' . $shift['lunch_end']))   : null,
    ];
}

/* =========================================================
   ACTION: IN  -> always INSERT a new row (multi-session)
   Refuses if there's an open IN session (out_time IS NULL) - must mark OUT first.
========================================================= */

if ($action === 'in') {

    // Check for an open session today
    $openRow = $obj->executequery("
        SELECT attendance_id
        FROM bni_attendance
        WHERE member_id  = '$member_id'
          AND meeting_id = '$meeting_id'
          AND DATE(scan_time) = '$today'
          AND out_time IS NULL
        LIMIT 1
    ")[0] ?? null;

    if ($openRow) {
        echo json_encode([
            'success' => false,
            'message' => 'Already has an open IN session. Mark OUT first before starting a new one.',
        ]);
        exit;
    }

    $fields = [
        'meeting_id'  => $meeting['meeting_id'],
        'member_id'   => $member_id,
        'shop_id'     => $shop_id ?: null,
        'scan_time'   => $now,
        'status'      => 'Present',
        'type'        => 'Manual',
        'createdby'   => $memberid,
        'ip_address'  => $_SERVER['REMOTE_ADDR']
    ];

    $obj->insert_record('bni_attendance', $fields);

    echo json_encode([
        'success' => true,
        'message' => 'Marked IN at ' . date('h:i A', strtotime($now)),
    ] + buildStateResponse($obj, $member_id, $meeting_id));
    exit;
}

/* =========================================================
   ACTION: OUT -> update the most recent OPEN row's out_time
========================================================= */

if ($action === 'out') {

    $openRow = $obj->executequery("
        SELECT attendance_id
        FROM bni_attendance
        WHERE member_id  = '$member_id'
          AND meeting_id = '$meeting_id'
          AND DATE(scan_time) = '$today'
          AND out_time IS NULL
        ORDER BY attendance_id DESC
        LIMIT 1
    ")[0] ?? null;

    if (!$openRow) {
        echo json_encode(['success' => false, 'message' => 'No open IN session to mark OUT for.']);
        exit;
    }

    $obj->update_record(
        'bni_attendance',
        ['attendance_id' => $openRow['attendance_id']],
        ['out_time' => $now]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Marked OUT at ' . date('h:i A', strtotime($now)),
    ] + buildStateResponse($obj, $member_id, $meeting_id));
    exit;
}

/* =========================================================
   ACTION: RESET -> delete ALL of today's rows for this member+meeting
========================================================= */

if ($action === 'reset') {

    // Delete ALL of today's rows for this member+meeting
    $obj->execute("
        DELETE FROM bni_attendance
        WHERE member_id  = '$member_id'
          AND meeting_id = '$meeting_id'
          AND DATE(scan_time) = '$today'
    ");

    echo json_encode([
        'success' => true,
        'message' => 'All sessions reset for today.',
    ] + buildStateResponse($obj, $member_id, $meeting_id));
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
exit;

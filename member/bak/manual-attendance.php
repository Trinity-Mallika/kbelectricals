<?php
include('session.php');

$member_id              = $_SESSION['member_id'];
$chapter_id             = $_SESSION['chapter_id'];
$attendance_coordinator = $_SESSION['attendance_coordinator'];

if (!$attendance_coordinator) {
    header("Location: dashboard.php");
    exit;
}

// Fetch ALL active QRs (one per shop) so the coordinator can pick
// which shop's QR to mark attendance against.
$activeQrs = $obj->executequery("
    SELECT m.meeting_id, m.shop_id, m.title, c.chapter_name AS shop_name
    FROM bni_meetings m
    LEFT JOIN chapter_master c ON c.chapter_id = m.shop_id
    WHERE m.status = 1
    ORDER BY c.chapter_name
");

if (empty($activeQrs)) {
    $error = "No active Shop QR found. Please ask admin to create one.";
}

// Default to the latest QR if none selected
$meeting_id = intval($_GET['meeting_id'] ?? 0);
if (!$meeting_id && !empty($activeQrs)) {
    $meeting_id = $activeQrs[0]['meeting_id'];
}

// NOTE: No meeting_locked check — QR has no end time now.
// Coordinators can mark attendance any time as long as the QR exists.

$today = date('Y-m-d');

// Helper: format seconds as "Xh Ym"
function formatDuration($sec) {
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    if ($h > 0) return "{$h}h {$m}m";
    return "{$m}m";
}

// Fetch ALL active employees across all shops (employees travel between shops)
// and LEFT JOIN today's attendance sessions.
$rows = $meeting_id ? $obj->executequery("
    SELECT
        m.member_id,
        m.member_name,
        m.mobile,
        m.company_name,
        m.chapter_id AS home_shop_id,
        c.chapter_name AS shop_name,
        a.attendance_id,
        a.shop_id AS session_shop_id,
        a.scan_time AS in_time,
        a.out_time,
        sc.chapter_name AS session_shop_name
    FROM bni_members m
    LEFT JOIN chapter_master c
        ON c.chapter_id = m.chapter_id
    LEFT JOIN bni_attendance a
        ON a.member_id  = m.member_id
        AND a.meeting_id = '$meeting_id'
        AND DATE(a.scan_time) = '$today'
    LEFT JOIN chapter_master sc
        ON sc.chapter_id = a.shop_id
    WHERE m.status = 1
    ORDER BY c.chapter_name, m.member_name, a.attendance_id
") : [];

// Fetch all active shops for the per-card shop selector
$shops = $meeting_id ? $obj->executequery("
    SELECT chapter_id, chapter_name
    FROM chapter_master
    WHERE status = 1
    ORDER BY chapter_name
") : [];

// Group rows by member_id (one member can have multiple sessions today)
$members = [];
foreach ($rows as $r) {
    $mid = $r['member_id'];
    if (!isset($members[$mid])) {
        $members[$mid] = [
            'member_id'    => $r['member_id'],
            'member_name'  => $r['member_name'],
            'mobile'       => $r['mobile'],
            'company_name' => $r['company_name'],
            'home_shop_id' => $r['home_shop_id'],
            'shop_name'    => $r['shop_name'],
            'sessions'     => [],
        ];
    }
    if (!empty($r['attendance_id'])) {
        $members[$mid]['sessions'][] = [
            'attendance_id'      => $r['attendance_id'],
            'session_shop_id'    => $r['session_shop_id'],
            'session_shop_name'  => $r['session_shop_name'],
            'in_time'            => $r['in_time'],
            'out_time'           => $r['out_time'],
        ];
    }
}

// Compute summary stats
$totalMembers = count($members);
$present      = 0;  // any session today
$openCount    = 0;  // has at least one open session
$completed    = 0;  // last session has out_time (shift completed)
$absent       = 0;

foreach ($members as $m) {
    if (empty($m['sessions'])) {
        $absent++;
        continue;
    }
    $present++;
    $hasOpen = false;
    foreach ($m['sessions'] as $s) {
        if (empty($s['out_time'])) { $hasOpen = true; break; }
    }
    if ($hasOpen) $openCount++;
    else $completed++;
}

// Shift info for display (loaded from shift_master table)
$shift = $obj->getShift();
$shiftStartDisp = date('h:i A', strtotime($today . ' ' . $shift['start_time']));
$shiftEndDisp   = date('h:i A', strtotime($today . ' ' . $shift['end_time']));
$lunchStartDisp = $shift['lunch_start'] ? date('h:i A', strtotime($today . ' ' . $shift['lunch_start'])) : '—';
$lunchEndDisp   = $shift['lunch_end']   ? date('h:i A', strtotime($today . ' ' . $shift['lunch_end']))   : '—';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manual Attendance</title>

    <link rel="stylesheet" href="../admin/assets/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --blue-dark: #06163a;
            --blue-mid: #1a56a0;
            --blue-light: #287ab1;
            --green: #16a34a;
            --green-bg: #dcfce7;
            --orange: #f59e0b;
            --orange-bg: #fef3c7;
            --red: #dc2626;
            --red-bg: #fee2e2;
            --slate: #475569;
            --slate-bg: #f1f5f9;
            --surface: #f4f7fb;
            --card: #ffffff;
            --border: #e8edf5;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --shadow: 0 4px 20px rgba(6, 22, 58, .09);
        }

        *, *::before, *::after {
            box-sizing: border-box; margin: 0; padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--text-main);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--blue-dark) 0%, var(--blue-light) 100%);
            color: #fff;
            padding: 20px 16px 80px;
            border-radius: 0 0 30px 30px;
        }

        .header h1 { font-size: 1.25rem; font-weight: 700; }
        .header p  { font-size: .8rem; opacity: .75; margin-top: 2px; }

        .back-btn {
            display: flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff; border-radius: 50px;
            padding: 6px 14px; font-size: .8rem; font-weight: 500;
            text-decoration: none; transition: background .2s;
        }
        .back-btn:hover { background: rgba(255,255,255,.28); color: #fff; }

        .shift-banner {
            margin-top: 10px;
            display: flex; flex-wrap: wrap; gap: 8px;
            font-size: .72rem;
        }
        .shift-pill {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.22);
            padding: 4px 10px; border-radius: 50px;
            display: inline-flex; align-items: center; gap: 4px;
        }

        .wrap {
            max-width: 720px;
            margin: -65px auto 0;
            padding: 0 14px 32px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 14px;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            background: var(--card);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow);
            padding: 12px 6px;
            text-align: center;
            min-height: 72px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .stat-card .val { font-size: 1.4rem; font-weight: 700; line-height: 1; }
        .stat-card .lbl {
            font-size: .62rem; color: var(--text-muted);
            margin-top: 3px; font-weight: 500;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .val.blue   { color: var(--blue-mid); }
        .val.green  { color: var(--green); }
        .val.orange { color: var(--orange); }
        .val.absent { color: var(--red); }

        .search-wrap {
            position: sticky; top: 0; z-index: 50;
            background: var(--surface);
            padding: 10px 0 8px;
        }
        .search-inner { position: relative; }
        .search-inner i {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: 1rem; pointer-events: none;
        }
        #searchInput {
            width: 100%; padding: 13px 40px 13px 42px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-size: .95rem; background: var(--card);
            box-shadow: var(--shadow); outline: none;
            font-family: inherit; transition: border-color .2s;
        }
        #searchInput:focus { border-color: var(--blue-light); }
        .clear-btn {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; color: var(--text-muted);
            font-size: 1rem; cursor: pointer; display: none; padding: 4px;
        }

        .member-list { display: flex; flex-direction: column; gap: 10px; }

        .member-card {
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 13px 14px;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .member-card:hover {
            border-color: #b3cfec;
            box-shadow: 0 2px 12px rgba(40, 122, 177, .1);
        }

        .member-card.state-in {
            border-left: 4px solid var(--green);
        }
        .member-card.state-out {
            border-left: 4px solid var(--orange);
        }
        .member-card.state-absent {
            border-left: 4px solid var(--border);
        }

        .member-row {
            display: flex; align-items: center; gap: 12px;
        }

        .avatar {
            width: 40px; height: 40px; min-width: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-mid), var(--blue-light));
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .9rem;
        }
        .state-in  .avatar { background: linear-gradient(135deg, #16a34a, #22c55e); }
        .state-out .avatar { background: linear-gradient(135deg, #f59e0b, #f97316); }

        .member-info { flex: 1; min-width: 0; }
        .member-name {
            font-weight: 600; font-size: .95rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .member-shop {
            font-size: .72rem; color: var(--text-muted);
            margin-top: 1px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .member-shop i { color: var(--blue-light); }
        .member-meta {
            font-size: .72rem; color: var(--text-muted);
            margin-top: 4px;
            display: flex; gap: 6px; flex-wrap: wrap; align-items: center;
        }
        .meta-pill {
            display: inline-flex; align-items: center; gap: 3px;
            background: var(--slate-bg);
            color: var(--slate);
            padding: 2px 7px; border-radius: 50px;
            font-weight: 600;
        }
        .meta-pill.late  { background: var(--red-bg);    color: var(--red); }
        .meta-pill.early { background: var(--orange-bg); color: var(--orange); }
        .meta-pill.hours { background: var(--green-bg);  color: var(--green); }
        .meta-pill.shop  { background: var(--slate-bg);  color: var(--slate); }

        .action-area {
            display: flex; flex-direction: column; gap: 6px;
            align-items: stretch; min-width: 100px;
        }
        .action-btn {
            border: 0; border-radius: 12px;
            padding: 8px 12px; font-size: .82rem; font-weight: 600;
            cursor: pointer; transition: transform .1s, opacity .18s, background .18s;
            display: inline-flex; align-items: center; justify-content: center; gap: 5px;
            font-family: inherit;
        }
        .action-btn:active { transform: scale(.97); }
        .action-btn:disabled { opacity: .55; cursor: not-allowed; }
        .action-btn.btn-in   { background: var(--green);  color: #fff; }
        .action-btn.btn-out  { background: var(--orange); color: #fff; }
        .action-btn.btn-reset {
            background: var(--slate-bg); color: var(--slate);
            font-size: .72rem; padding: 5px 10px;
        }
        .action-btn.btn-reset:hover { background: var(--red-bg); color: var(--red); }

        .action-btn .spinner {
            display: none;
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        .action-btn.btn-reset .spinner {
            border-color: rgba(71,85,105,.3);
            border-top-color: var(--slate);
        }
        .action-btn.loading .spinner { display: inline-block; }
        .action-btn.loading .btn-label { opacity: .7; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Sessions list */
        .sessions-list {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed var(--border);
            display: flex; flex-direction: column; gap: 5px;
        }
        .session-row {
            display: flex; align-items: center; gap: 8px;
            font-size: .76rem;
            color: var(--text-main);
            background: var(--slate-bg);
            padding: 6px 10px;
            border-radius: 8px;
        }
        .session-row.open {
            background: var(--green-bg);
            color: var(--green);
            font-weight: 600;
        }
        .session-row .num {
            background: rgba(0,0,0,.06);
            color: var(--slate);
            width: 18px; height: 18px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .65rem; font-weight: 700;
            flex-shrink: 0;
        }
        .session-row.open .num {
            background: var(--green);
            color: #fff;
        }
        .session-row .in-pill,
        .session-row .out-pill {
            display: inline-flex; align-items: center; gap: 3px;
            font-weight: 600;
        }
        .session-row .arrow { color: var(--text-muted); }
        .session-row .dur {
            margin-left: auto;
            font-size: .7rem;
            color: var(--text-muted);
            background: rgba(255,255,255,.7);
            padding: 1px 6px; border-radius: 50px;
        }
        .session-row.open .dur {
            background: rgba(22,163,74,.18);
            color: var(--green);
        }
        .session-row .shop-pill {
            display: inline-flex; align-items: center; gap: 3px;
            background: rgba(40, 122, 177, .12);
            color: var(--blue-mid);
            padding: 2px 7px; border-radius: 50px;
            font-size: .68rem; font-weight: 600;
        }
        .session-row .shop-pill.muted {
            background: rgba(100,116,139,.12);
            color: var(--text-muted);
            font-style: italic;
        }
        .session-row.open .shop-pill {
            background: rgba(255,255,255,.85);
            color: var(--green);
        }

        /* Shop selector inside action-area */
        .shop-select {
            height: 32px;
            font-size: .8rem;
            padding: 4px 28px 4px 10px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--card);
            color: var(--text-main);
            font-family: inherit;
            cursor: pointer;
        }
        .shop-select:focus {
            outline: none;
            border-color: var(--blue-light);
            box-shadow: 0 0 0 2px rgba(40,122,177,.15);
        }

        .no-sessions {
            font-size: .72rem; color: var(--text-muted);
            font-style: italic; padding: 4px 0;
        }

        .no-results {
            text-align: center; padding: 32px;
            color: var(--text-muted); display: none;
        }

        .toast-msg {
            position: fixed; bottom: 24px; left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--blue-dark); color: #fff;
            padding: 12px 22px; border-radius: 50px;
            font-size: .9rem; font-weight: 500;
            box-shadow: 0 6px 24px rgba(0, 0, 0, .2);
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1), opacity .3s;
            opacity: 0; z-index: 999; white-space: nowrap; max-width: 90vw;
            overflow: hidden; text-overflow: ellipsis;
        }
        .toast-msg.show    { transform: translateX(-50%) translateY(0); opacity: 1; }
        .toast-msg.success { background: var(--green); }
        .toast-msg.error   { background: var(--red); }
        .toast-msg.absent  { background: var(--slate); }
        .toast-msg.warn    { background: var(--orange); }

        .ended-state {
            background: var(--card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 36px 24px; text-align: center;
            margin-bottom: 16px; margin-top: 70px;
        }
        .ended-icon {
            width: 72px; height: 72px;
            background: var(--red-bg); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 2rem; color: var(--red);
        }
        .ended-state h2 { font-size: 1.15rem; font-weight: 700; margin-bottom: 6px; }
        .ended-state p  { font-size: .85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px; }
        .ended-time-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--red-bg); color: var(--red);
            border: 1px solid #fca5a5; border-radius: 50px;
            padding: 6px 16px; font-size: .8rem; font-weight: 600;
            margin-bottom: 20px;
        }
        .ended-summary {
            display: flex; justify-content: center; gap: 24px; flex-wrap: wrap;
            border-top: 1px solid var(--border);
            padding-top: 18px; margin-top: 4px;
        }
        .ended-summary .s-val { font-size: 1.5rem; font-weight: 700; line-height: 1; }
        .ended-summary .s-lbl {
            font-size: .7rem; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: .05em; margin-top: 4px;
        }

        .error-banner {
            background: var(--red-bg); color: var(--red);
            border: 1px solid #fca5a5; border-radius: var(--radius-sm);
            padding: 10px 14px; font-size: .85rem; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 12px;
        }

        @media(max-width:520px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .stat-card .val { font-size: 1.5rem; }
            .wrap { max-width: 100%; }
        }
    </style>
</head>

<body>

    <div class="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1><i class="bi bi-person-check"></i> Manual Attendance</h1>
                <p>
                    <?php if (!empty($activeQrs)): ?>
                        <?= date('D, d M Y') ?>
                    <?php endif; ?>
                </p>
                <div class="shift-banner">
                    <span class="shift-pill"><i class="bi bi-clock"></i> Shift: <?= $shiftStartDisp ?> - <?= $shiftEndDisp ?></span>
                    <span class="shift-pill"><i class="bi bi-cup-hot"></i> Lunch: <?= $lunchStartDisp ?> - <?= $lunchEndDisp ?></span>
                </div>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <?php if (!empty($activeQrs)): ?>
        <div style="margin-top:14px;">
            <form method="GET" id="qrSelectForm">
                <label style="font-size:.7rem; opacity:.8; text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:4px;">
                    <i class="bi bi-qr-code-scan"></i> Select Shop QR
                </label>
                <select name="meeting_id"
                    style="width:100%; max-width:300px; height:36px; border-radius:10px;
                           border:1px solid rgba(255,255,255,.3); background:rgba(255,255,255,.12);
                           color:#fff; padding:4px 10px; font-size:.85rem; font-family:inherit;
                           cursor:pointer;"
                    onchange="this.form.submit()">
                    <?php foreach ($activeQrs as $qr): ?>
                        <option value="<?= $qr['meeting_id'] ?>"
                            <?= ($meeting_id == $qr['meeting_id']) ? 'selected' : '' ?>
                            style="color:#111;">
                            <?= htmlspecialchars($qr['shop_name'] ?? $qr['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="wrap">

        <?php if (!empty($error)): ?>
            <div class="error-banner"><i class="bi bi-exclamation-circle-fill"></i><?= $error ?></div>
        <?php endif; ?>

        <div class="stats">
                <div class="stat-card">
                    <div class="val blue" id="statTotal"><?= $totalMembers ?></div>
                    <div class="lbl">Total</div>
                </div>
                <div class="stat-card">
                    <div class="val green" id="statIn"><?= $present ?></div>
                    <div class="lbl">Present</div>
                </div>
                <div class="stat-card">
                    <div class="val orange" id="statOpen"><?= $openCount ?></div>
                    <div class="lbl">Open IN</div>
                </div>
                <div class="stat-card">
                    <div class="val absent" id="statAbsent"><?= $absent ?></div>
                    <div class="lbl">Absent</div>
                </div>
            </div>

        <div class="search-wrap">
            <div class="search-wrap">
                <div class="search-inner">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput"
                           placeholder="Search employee, company or shop…" autocomplete="off">
                    <button class="clear-btn" id="clearBtn" title="Clear">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        <div id="memberList" class="member-list">

            <?php foreach ($members as $m):
                $sessions = $m['sessions'];
                $hasOpen  = false;
                $totalSec = 0;
                $firstInTs = null;
                $lastOutTs = null;

                foreach ($sessions as $s) {
                    $inTs  = strtotime($s['in_time']);
                    $outTs = $s['out_time'] ? strtotime($s['out_time']) : null;
                    if ($firstInTs === null) $firstInTs = $inTs;
                    if ($outTs !== null) {
                        $lastOutTs = $outTs;
                        $totalSec += ($outTs - $inTs);
                    } else {
                        $hasOpen = true;
                    }
                }

                $state = empty($sessions) ? 'absent' : ($hasOpen ? 'in' : 'out');

                // Late / Early computation
                $shiftStartTs = strtotime($today . ' ' . $shift['start_time']);
                $shiftEndTs   = strtotime($today . ' ' . $shift['end_time']);
                $lateSec  = ($firstInTs && $firstInTs > $shiftStartTs) ? ($firstInTs - $shiftStartTs) : 0;
                $earlySec = ($lastOutTs && $lastOutTs < $shiftEndTs)   ? ($shiftEndTs - $lastOutTs)   : 0;

                $initials = implode('', array_map(
                    fn($w) => strtoupper($w[0] ?? ''),
                    array_slice(explode(' ', trim($m['member_name'])), 0, 2)
                ));
            ?>
                <div class="member-card state-<?= $state ?>"
                    data-member-id="<?= $m['member_id'] ?>"
                    data-meeting-id="<?= $meeting_id ?>"
                    data-name="<?= strtolower(htmlspecialchars($m['member_name'])) ?>"
                    data-company="<?= strtolower(htmlspecialchars($m['company_name'] ?? '')) ?>"
                    data-shop="<?= strtolower(htmlspecialchars($m['shop_name'] ?? '')) ?>">

                    <div class="member-row">
                        <div class="avatar"><?= $initials ?></div>

                        <div class="member-info">
                            <div class="member-name"><?= htmlspecialchars($m['member_name']) ?></div>
                            <div class="member-shop">
                                <i class="bi bi-shop"></i>
                                <?= htmlspecialchars($m['shop_name'] ?? 'No shop') ?>
                                <?php if (!empty($m['company_name'])): ?>
                                    · <?= htmlspecialchars($m['company_name']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="member-meta">
                                <?php if (!empty($sessions)): ?>
                                    <span class="meta-pill hours"><i class="bi bi-stopwatch"></i> <?= formatDuration($totalSec) ?></span>
                                    <?php if ($lateSec > 0): ?>
                                        <span class="meta-pill late"><i class="bi bi-clock-history"></i> Late <?= formatDuration($lateSec) ?></span>
                                    <?php endif; ?>
                                    <?php if ($earlySec > 0 && !$hasOpen): ?>
                                        <span class="meta-pill early"><i class="bi bi-box-arrow-right"></i> Early <?= formatDuration($earlySec) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                            <div class="action-area" data-state="<?= $state ?>">
                                <?php
                                // Shop selector - shown whenever an IN button is available
                                $showShopSelect = in_array($state, ['absent', 'out'], true);
                                $defaultShopId  = $m['home_shop_id'] ?? '';
                                ?>
                                <?php if ($showShopSelect): ?>
                                    <select class="form-select shop-select" data-shop-id>
                                        <option value="">— Select Shop —</option>
                                        <?php foreach ($shops as $shop): ?>
                                            <option value="<?= $shop['chapter_id'] ?>"
                                                <?= ((string)$defaultShopId === (string)$shop['chapter_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($shop['chapter_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>

                                <?php if ($state === 'absent'): ?>
                                    <button type="button" class="action-btn btn-in"
                                            onclick="doAction(this, 'in')">
                                        <span class="spinner"></span>
                                        <span class="btn-label"><i class="bi bi-box-arrow-in-right"></i> Mark IN</span>
                                    </button>
                                <?php elseif ($state === 'in'): ?>
                                    <button type="button" class="action-btn btn-out"
                                            onclick="doAction(this, 'out')">
                                        <span class="spinner"></span>
                                        <span class="btn-label"><i class="bi bi-box-arrow-right"></i> Mark OUT</span>
                                    </button>
                                <?php else: /* state === 'out' */ ?>
                                    <button type="button" class="action-btn btn-in"
                                            onclick="doAction(this, 'in')">
                                        <span class="spinner"></span>
                                        <span class="btn-label"><i class="bi bi-box-arrow-in-right"></i> Mark IN</span>
                                    </button>
                                <?php endif; ?>

                                <?php if (!empty($sessions)): ?>
                                    <button type="button" class="action-btn btn-reset"
                                            onclick="doAction(this, 'reset')">
                                        <span class="spinner"></span>
                                        <span class="btn-label"><i class="bi bi-arrow-counterclockwise"></i> Reset Day</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                    </div>

                    <?php if (!empty($sessions)): ?>
                        <div class="sessions-list">
                            <?php foreach ($sessions as $i => $s):
                                $isOpen = empty($s['out_time']);
                                $inTs  = strtotime($s['in_time']);
                                $outTs = $s['out_time'] ? strtotime($s['out_time']) : null;
                                $durSec = $outTs ? ($outTs - $inTs) : 0;
                            ?>
                                <div class="session-row <?= $isOpen ? 'open' : '' ?>">
                                    <span class="num"><?= $i + 1 ?></span>
                                    <?php if (!empty($s['session_shop_name'])): ?>
                                        <span class="shop-pill"><i class="bi bi-shop"></i> <?= htmlspecialchars($s['session_shop_name']) ?></span>
                                    <?php else: ?>
                                        <span class="shop-pill muted"><i class="bi bi-shop"></i> —</span>
                                    <?php endif; ?>
                                    <span class="in-pill"><i class="bi bi-box-arrow-in-right"></i> <?= date('h:i A', $inTs) ?></span>
                                    <span class="arrow">→</span>
                                    <?php if ($isOpen): ?>
                                        <span class="out-pill" style="color:var(--green);">
                                            <i class="bi bi-hourglass-split"></i> Open
                                        </span>
                                    <?php else: ?>
                                        <span class="out-pill"><i class="bi bi-box-arrow-right"></i> <?= date('h:i A', $outTs) ?></span>
                                    <?php endif; ?>
                                    <span class="dur"><?= $isOpen ? 'ongoing' : formatDuration($durSec) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="sessions-list">
                            <div class="no-sessions">No attendance marked today.</div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

        </div>

        <div class="no-results" id="noResults">
            <i class="bi bi-person-x" style="font-size:2rem; opacity:.3; display:block; margin-bottom:6px;"></i>
            No employees match "<span id="noResultsQuery"></span>"
        </div>
    </div>

    <div class="toast-msg" id="toast"></div>

    <script>
        const SHIFT_START_TS = <?= strtotime($today . ' ' . $shift['start_time']) * 1000 ?>;
        const SHIFT_END_TS   = <?= strtotime($today . ' ' . $shift['end_time'])   * 1000 ?>;

        // All active shops for the per-card selector (used by reRenderCard)
        const ALL_SHOPS = <?= json_encode(
            array_map(fn($s) => [
                'chapter_id'   => (int) $s['chapter_id'],
                'chapter_name' => $s['chapter_name'],
            ], $shops)
        ) ?>;

        async function doAction(btn, action) {
            if (btn.disabled) return;

            const card       = btn.closest('.member-card');
            const memberId   = card.dataset.memberId;
            const meetingId  = card.dataset.meetingId;
            const shopSelect = card.querySelector('.shop-select');
            const shopId     = shopSelect ? shopSelect.value : '';

            if (action === 'reset') {
                if (!confirm('Reset ALL attendance sessions for this employee today?')) {
                    return;
                }
            }

            // Require shop selection for action=in
            if (action === 'in' && !shopId) {
                showToast('Please select a shop before marking IN.', 'error');
                if (shopSelect) shopSelect.focus();
                return;
            }

            btn.classList.add('loading');
            btn.disabled = true;

            try {
                const body = new URLSearchParams();
                body.append('member_id',  memberId);
                body.append('meeting_id', meetingId);
                body.append('action',     action);
                if (action === 'in') body.append('shop_id', shopId);

                const res = await fetch('ajax_attendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                });
                const data = await res.json();

                if (data.success) {
                    reRenderCard(card, data);
                    updateStats();
                    showToast(
                        data.message || 'Updated',
                        action === 'in'  ? 'success' :
                        action === 'out' ? 'warn'    :
                                           'absent'
                    );
                } else {
                    showToast(data.message || 'Failed. Try again.', 'error');
                }
            } catch (err) {
                showToast('Network error. Try again.', 'error');
            } finally {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        }

        function formatDurationJs(sec) {
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            return h > 0 ? `${h}h ${m}m` : `${m}m`;
        }

        function reRenderCard(card, data) {
            // Update card state class
            card.classList.remove('state-absent', 'state-in', 'state-out');
            card.classList.add('state-' + data.state);

            // Update avatar color (CSS handles via .state-X .avatar)

            // Update meta pills (worked hours / late / early)
            const metaEl = card.querySelector('.member-meta');
            if (metaEl) {
                let html = '';
                if (data.sessions && data.sessions.length > 0) {
                    html += `<span class="meta-pill hours"><i class="bi bi-stopwatch"></i> ${formatDurationJs(data.worked_sec)}</span>`;
                    if (data.late_sec > 0) {
                        html += `<span class="meta-pill late"><i class="bi bi-clock-history"></i> Late ${formatDurationJs(data.late_sec)}</span>`;
                    }
                    if (data.early_sec > 0 && data.state === 'out') {
                        html += `<span class="meta-pill early"><i class="bi bi-box-arrow-right"></i> Early ${formatDurationJs(data.early_sec)}</span>`;
                    }
                }
                metaEl.innerHTML = html;
            }

            // Update sessions list
            let sessionsEl = card.querySelector('.sessions-list');
            if (!data.sessions || data.sessions.length === 0) {
                if (sessionsEl) {
                    sessionsEl.remove();
                }
                // Add "No attendance marked today" hint
                sessionsEl = document.createElement('div');
                sessionsEl.className = 'sessions-list';
                sessionsEl.innerHTML = '<div class="no-sessions">No attendance marked today.</div>';
                card.appendChild(sessionsEl);
            } else {
                if (!sessionsEl) {
                    sessionsEl = document.createElement('div');
                    sessionsEl.className = 'sessions-list';
                    card.appendChild(sessionsEl);
                }
                let rowsHtml = '';
                data.sessions.forEach((s, i) => {
                    const isOpen = !s.out_time;
                    const shopPill = s.shop_name
                        ? `<span class="shop-pill"><i class="bi bi-shop"></i> ${escapeHtml(s.shop_name)}</span>`
                        : `<span class="shop-pill muted"><i class="bi bi-shop"></i> —</span>`;
                    rowsHtml += `
                        <div class="session-row ${isOpen ? 'open' : ''}">
                            <span class="num">${i + 1}</span>
                            ${shopPill}
                            <span class="in-pill"><i class="bi bi-box-arrow-in-right"></i> ${s.in_time}</span>
                            <span class="arrow">→</span>
                            ${isOpen
                                ? `<span class="out-pill" style="color:var(--green);"><i class="bi bi-hourglass-split"></i> Open</span>`
                                : `<span class="out-pill"><i class="bi bi-box-arrow-right"></i> ${s.out_time}</span>`
                            }
                            <span class="dur">${isOpen ? 'ongoing' : formatDurationJs(s.duration_sec)}</span>
                        </div>`;
                });
                sessionsEl.innerHTML = rowsHtml;
            }

            // Update action area
            const actionEl = card.querySelector('.action-area');
            if (actionEl) {
                actionEl.dataset.state = data.state;

                // Preserve currently selected shop_id if any
                const oldSelect = actionEl.querySelector('.shop-select');
                const prevShopId = oldSelect ? oldSelect.value : '';

                let btnHtml = '';
                // Show shop selector when an IN button is available (absent or out)
                const showShopSelect = (data.state === 'absent' || data.state === 'out');
                if (showShopSelect) {
                    btnHtml += buildShopSelectHtml(prevShopId);
                }

                if (data.state === 'absent') {
                    btnHtml += `
                        <button type="button" class="action-btn btn-in" onclick="doAction(this, 'in')">
                            <span class="spinner"></span>
                            <span class="btn-label"><i class="bi bi-box-arrow-in-right"></i> Mark IN</span>
                        </button>`;
                } else if (data.state === 'in') {
                    btnHtml += `
                        <button type="button" class="action-btn btn-out" onclick="doAction(this, 'out')">
                            <span class="spinner"></span>
                            <span class="btn-label"><i class="bi bi-box-arrow-right"></i> Mark OUT</span>
                        </button>`;
                } else { // out
                    btnHtml += `
                        <button type="button" class="action-btn btn-in" onclick="doAction(this, 'in')">
                            <span class="spinner"></span>
                            <span class="btn-label"><i class="bi bi-box-arrow-in-right"></i> Mark IN</span>
                        </button>`;
                }
                // Always show Reset if there are sessions
                if (data.sessions && data.sessions.length > 0) {
                    btnHtml += `
                        <button type="button" class="action-btn btn-reset" onclick="doAction(this, 'reset')">
                            <span class="spinner"></span>
                            <span class="btn-label"><i class="bi bi-arrow-counterclockwise"></i> Reset Day</span>
                        </button>`;
                }
                actionEl.innerHTML = btnHtml;
            }
        }

        function buildShopSelectHtml(selectedId) {
            const options = ALL_SHOPS.map(s => {
                const sel = (String(selectedId) === String(s.chapter_id)) ? 'selected' : '';
                return `<option value="${s.chapter_id}" ${sel}>${escapeHtml(s.chapter_name)}</option>`;
            }).join('');
            return `<select class="form-select shop-select" data-shop-id>
                <option value="">— Select Shop —</option>
                ${options}
            </select>`;
        }

        function escapeHtml(str) {
            if (str == null) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function updateStats() {
            const total   = document.querySelectorAll('.member-card').length;
            const present = document.querySelectorAll('.member-card.state-in, .member-card.state-out').length;
            const open    = document.querySelectorAll('.member-card.state-in').length;
            const absent  = total - present;

            const elTotal  = document.getElementById('statTotal');
            const elIn     = document.getElementById('statIn');
            const elOpen   = document.getElementById('statOpen');
            const elAbsent = document.getElementById('statAbsent');

            if (elTotal)  elTotal.textContent  = total;
            if (elIn)     elIn.textContent     = present;
            if (elOpen)   elOpen.textContent   = open;
            if (elAbsent) elAbsent.textContent = absent;
        }

        const searchInput = document.getElementById('searchInput');
        const clearBtn    = document.getElementById('clearBtn');
        const cards       = document.querySelectorAll('.member-card');
        const noResults   = document.getElementById('noResults');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.trim().toLowerCase();
                clearBtn.style.display = q ? 'block' : 'none';

                let visible = 0;
                cards.forEach(card => {
                    const match =
                        card.dataset.name.includes(q) ||
                        card.dataset.company.includes(q) ||
                        card.dataset.shop.includes(q);
                    card.style.display = match ? '' : 'none';
                    if (match) visible++;
                });

                noResults.style.display = (!visible && q) ? 'block' : 'none';
                document.getElementById('noResultsQuery').textContent = q;
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                clearBtn.style.display = 'none';
                cards.forEach(c => c.style.display = '');
                noResults.style.display = 'none';
                searchInput.focus();
            });
        }

        function showToast(msg, type = '') {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast-msg ' + type;
            setTimeout(() => t.classList.add('show'), 10);
            setTimeout(() => t.classList.remove('show'), 2400);
        }
    </script>
</body>

</html>

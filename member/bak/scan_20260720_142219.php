<?php
include('session.php');

$token = $_GET['token'] ?? '';
$member_id = $_SESSION['member_id'];
$today = date('Y-m-d');

/* =========================================================
   Fetch today's sessions for this employee so the scan page
   knows what state they're in (open IN / closed / no sessions).
========================================================= */

$todaySessions = $obj->executequery("
    SELECT
        a.attendance_id,
        a.shop_id,
        a.scan_time AS in_time,
        a.out_time,
        a.type,
        s.chapter_name AS shop_name
    FROM bni_attendance a
    LEFT JOIN chapter_master s ON s.chapter_id = a.shop_id
    WHERE a.member_id = '$member_id'
      AND DATE(a.scan_time) = '$today'
    ORDER BY a.attendance_id ASC
");

$hasOpen   = false;
$workedSec = 0;
$openInTime = null;
foreach ($todaySessions as $s) {
    $inTs  = strtotime($s['in_time']);
    $outTs = $s['out_time'] ? strtotime($s['out_time']) : null;
    if ($outTs !== null) {
        $workedSec += ($outTs - $inTs);
    } else {
        $hasOpen = true;
        $openInTime = $s['in_time'];
    }
}

// Shift info for context banner (per employee)
$member = $obj->select_record('bni_members', ['member_id' => $member_id]);
$shift = $obj->getShift($member['shift_id'] ?? null);
$shiftStartDisp = date('h:i A', strtotime($today . ' ' . $shift['start_time']));
$shiftEndDisp   = date('h:i A', strtotime($today . ' ' . $shift['end_time']));
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Scan Attendance</title>

    <link rel="stylesheet" href="../admin/assets/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/assets/css/bootstrap.min.css">

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        body {
            background: #06163a;
            color: #fff;
        }

        .wrap {
            max-width: 560px;
            margin: auto;
            padding: 16px;
        }

        .scanner-card {
            background: #fff;
            color: #111;
            border-radius: 24px;
            padding: 16px;
            box-shadow: 0 16px 45px rgba(0, 0, 0, .3);
        }

        #reader {
            border-radius: 18px;
            overflow: hidden;
        }

        .location-box {
            font-size: 14px;
            line-height: 1.6;
        }

        .state-banner {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .state-banner.in {
            background: linear-gradient(135deg, #dcfce7, #f0fdf4);
            color: #16a34a;
            border: 1px solid #86efac;
        }

        .state-banner.out {
            background: linear-gradient(135deg, #fef3c7, #fffbeb);
            color: #f59e0b;
            border: 1px solid #fcd34d;
        }

        .state-banner.none {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .state-banner .icon {
            font-size: 28px;
            flex-shrink: 0;
        }

        .state-banner .text-block {
            flex: 1;
            min-width: 0;
        }

        .state-banner .title {
            font-weight: 700;
            font-size: 15px;
        }

        .state-banner .sub {
            font-size: 12px;
            opacity: .85;
            margin-top: 2px;
        }

        .shift-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(40, 122, 177, .12);
            color: #1a56a0;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }

        .sessions-mini {
            background: #f8fafc;
            border-radius: 12px;
            padding: 8px 10px;
            margin-bottom: 12px;
        }

        .sessions-mini h6 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: 6px;
        }

        .mini-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 4px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .mini-row:last-child { border-bottom: 0; }
        .mini-row .num {
            background: #e2e8f0;
            color: #475569;
            width: 18px; height: 18px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700;
            flex-shrink: 0;
        }
        .mini-row.open .num { background: #16a34a; color: #fff; }
        .mini-row .in-pill { color: #16a34a; font-weight: 600; }
        .mini-row .out-pill { color: #f59e0b; font-weight: 600; }
        .mini-row .arrow { color: #94a3b8; }
        .mini-row .shop {
            font-size: 10px; color: #1a56a0; font-weight: 600;
            background: rgba(40,122,177,.12);
            padding: 1px 6px; border-radius: 50px;
        }
        .mini-row .dur {
            margin-left: auto;
            font-size: 10px; color: #64748b;
            background: #fff;
            padding: 1px 6px; border-radius: 50px;
        }
    </style>
</head>

<body>

    <div class="wrap">

        <div class="text-center my-3">
            <h3>
                <i class="bi bi-qr-code-scan"></i>
                Employee Attendance
            </h3>

            <p class="text-info">
                Camera + GPS verification
            </p>
        </div>

        <div class="scanner-card">

            <!-- ============================================================
                 STATE BANNER - tells employee what scan will do
            ============================================================ -->
            <?php if ($hasOpen): ?>
                <div class="state-banner in">
                    <div class="icon"><i class="bi bi-box-arrow-in-right"></i></div>
                    <div class="text-block">
                        <div class="title">You are Checked IN</div>
                        <div class="sub">
                            Since <?php echo date('h:i A', strtotime($openInTime)); ?> · Worked
                            <?php
                            $elapsed = time() - strtotime($openInTime);
                            $h = floor($elapsed / 3600);
                            $m = floor(($elapsed % 3600) / 60);
                            echo $h > 0 ? "{$h}h {$m}m" : "{$m}m";
                            ?>
                        </div>
                    </div>
                </div>
                <p class="text-center small text-muted mb-2">
                    Scanning now will mark your <b>OUT time</b>.
                </p>
            <?php elseif (!empty($todaySessions)): ?>
                <div class="state-banner out">
                    <div class="icon"><i class="bi bi-box-arrow-right"></i></div>
                    <div class="text-block">
                        <div class="title">All sessions closed</div>
                        <div class="sub">
                            Total worked:
                            <?php
                            $h = floor($workedSec / 3600);
                            $m = floor(($workedSec % 3600) / 60);
                            echo $h > 0 ? "{$h}h {$m}m" : "{$m}m";
                            ?>
                        </div>
                    </div>
                </div>
                <p class="text-center small text-muted mb-2">
                    Scanning now will start a <b>new IN session</b>.
                </p>
            <?php else: ?>
                <div class="state-banner none">
                    <div class="icon"><i class="bi bi-moon"></i></div>
                    <div class="text-block">
                        <div class="title">Not checked in yet</div>
                        <div class="sub">Scan QR to mark your IN time</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 SHIFT INFO PILL
            ============================================================ -->
            <div class="text-center mb-3">
                <span class="shift-pill">
                    <i class="bi bi-clock"></i>
                    Shift: <?php echo $shiftStartDisp; ?> - <?php echo $shiftEndDisp; ?>
                </span>
            </div>

            <!-- ============================================================
                 TODAY'S SESSIONS MINI LIST (only if any)
            ============================================================ -->
            <?php if (!empty($todaySessions)): ?>
                <div class="sessions-mini">
                    <h6>Today's Sessions</h6>
                    <?php foreach ($todaySessions as $i => $s):
                        $isOpen = empty($s['out_time']);
                        $inTs  = strtotime($s['in_time']);
                        $outTs = $s['out_time'] ? strtotime($s['out_time']) : null;
                        $durSec = $outTs ? ($outTs - $inTs) : 0;
                    ?>
                        <div class="mini-row <?php echo $isOpen ? 'open' : ''; ?>">
                            <span class="num"><?php echo $i + 1; ?></span>
                            <?php if (!empty($s['shop_name'])): ?>
                                <span class="shop"><?php echo htmlspecialchars($s['shop_name']); ?></span>
                            <?php endif; ?>
                            <span class="in-pill"><i class="bi bi-box-arrow-in-right"></i> <?php echo date('h:i A', $inTs); ?></span>
                            <span class="arrow">→</span>
                            <?php if ($isOpen): ?>
                                <span class="out-pill" style="color:#16a34a;">Open</span>
                            <?php else: ?>
                                <span class="out-pill"><i class="bi bi-box-arrow-right"></i> <?php echo date('h:i A', $outTs); ?></span>
                            <?php endif; ?>
                            <span class="dur">
                                <?php
                                if ($isOpen) echo 'ongoing';
                                else {
                                    $h = floor($durSec / 3600);
                                    $m = floor(($durSec % 3600) / 60);
                                    echo $h > 0 ? "{$h}h {$m}m" : "{$m}m";
                                }
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 RESULT AREA
            ============================================================ -->
            <div id="result" class="mb-2"></div>

            <!-- ============================================================
                 SCANNER / TOKEN BUTTON
            ============================================================ -->
            <?php if ($token == '') { ?>

                <div id="reader"></div>

            <?php } else { ?>

                <div class="alert alert-info">
                    QR URL detected. Click below to capture GPS and mark attendance.
                </div>

                <button type="button"
                    class="btn btn-success w-100 btn-lg"
                    onclick="getLocationAndMark('<?php echo htmlspecialchars($token, ENT_QUOTES); ?>')">
                    <i class="bi bi-geo-alt"></i>
                    Capture GPS &amp; Mark Attendance
                </button>

            <?php } ?>

            <!-- <a href="dashboard.php"
                class="btn btn-outline-dark w-100 mt-3">
                Back to Dashboard
            </a> -->

        </div>

    </div>

    <script>
        const GOOGLE_API_KEY = "AIzaSyD60TsOPfBQDMpiGwEWusBT-UBUUM6Y8O8";

        const TARGET_ACCURACY = 25; // best target meter
        const MAX_WAIT_TIME = 30000; // 30 seconds
        let html5QrcodeScanner = null;

        function extractToken(text) {
            try {
                let url = new URL(text);
                return url.searchParams.get('token') || text;
            } catch (e) {
                return text;
            }
        }

        function onScanSuccess(decodedText) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }

            let token = extractToken(decodedText);
            getLocationAndMark(token);
        }

        <?php if ($token == '') { ?>
            html5QrcodeScanner = new Html5QrcodeScanner("reader", {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                },
                rememberLastUsedCamera: true
            });

            html5QrcodeScanner.render(onScanSuccess);
        <?php } ?>

        function getLocationAndMark(token) {
            if (!navigator.geolocation) {
                showError("Location not supported in this browser.");
                return;
            }

            let bestPosition = null;
            let bestAccuracy = 999999;
            let watchId = null;
            let finished = false;

            showLoader("Finding accurate GPS location...");

            watchId = navigator.geolocation.watchPosition(
                async function(position) {
                        let accuracy = position.coords.accuracy;

                        if (accuracy < bestAccuracy) {
                            bestAccuracy = accuracy;
                            bestPosition = position;

                            showLoader(
                                "Improving GPS accuracy...<br>" +
                                "Current accuracy: " + accuracy.toFixed(2) + " meter"
                            );
                        }

                        if (bestAccuracy <= TARGET_ACCURACY && !finished) {
                            finished = true;
                            navigator.geolocation.clearWatch(watchId);
                            await useBestLocation(token, bestPosition);
                        }
                    },

                    function(error) {
                        if (finished) return;

                        finished = true;

                        if (watchId !== null) {
                            navigator.geolocation.clearWatch(watchId);
                        }

                        let message = "Location permission denied. Please allow GPS.";

                        if (error.code === 1) {
                            message = "Location permission denied. Please allow location permission.";
                        } else if (error.code === 2) {
                            message = "Location unavailable. Please turn on GPS.";
                        } else if (error.code === 3) {
                            message = "Location timeout. Please try again.";
                        }

                        showError(message);
                    },

                    {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: MAX_WAIT_TIME
                    }
            );

            setTimeout(async function() {
                if (finished) return;

                finished = true;

                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                }

                if (bestPosition) {
                    await useBestLocation(token, bestPosition);
                } else {
                    showError("GPS location not found. Please turn on GPS and try again.");
                }

            }, MAX_WAIT_TIME);
        }

        async function useBestLocation(token, position) {
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;
            let accuracy = position.coords.accuracy;

            showLoader("Fetching address...");

            let locationData = await getFullAddress(lat, lng);

            document.getElementById('result').innerHTML = `
        <div class="alert alert-info location-box">
            <b>Your Location Found</b>
            <hr>
            <b>Latitude:</b> ${lat.toFixed(8)}<br>
            <b>Longitude:</b> ${lng.toFixed(8)}<br>
            <b>GPS Accuracy:</b> ${accuracy.toFixed(2)} Meter
            <hr>
            <b>Address:</b><br>
            ${locationData.address}
        </div>
    `;

            markAttendance(
                token,
                lat,
                lng,
                accuracy,
                locationData.address,
                locationData.city,
                locationData.state,
                locationData.country,
                locationData.pincode
            );
        }

        async function getFullAddress(lat, lng) {
            let result = {
                address: '',
                city: '',
                state: '',
                country: '',
                pincode: ''
            };

            try {
                let googleResponse = await fetch(
                    'https://maps.googleapis.com/maps/api/geocode/json?latlng=' +
                    lat + ',' + lng +
                    '&key=' + GOOGLE_API_KEY
                );

                let googleData = await googleResponse.json();

                if (googleData.status === 'OK' && googleData.results.length > 0) {
                    result.address = googleData.results[0].formatted_address;

                    let components = googleData.results[0].address_components;

                    components.forEach(function(item) {
                        if (item.types.includes('locality')) {
                            result.city = item.long_name;
                        }

                        if (item.types.includes('administrative_area_level_1')) {
                            result.state = item.long_name;
                        }

                        if (item.types.includes('country')) {
                            result.country = item.long_name;
                        }

                        if (item.types.includes('postal_code')) {
                            result.pincode = item.long_name;
                        }
                    });

                    return result;
                }

                console.log("Google Geocode:", googleData.status, googleData.error_message || "");
            } catch (e) {
                console.log("Google Geocode failed", e);
            }

            return await getOsmAddress(lat, lng);
        }

        async function getOsmAddress(lat, lng) {
            let result = {
                address: 'Address not found',
                city: '',
                state: '',
                country: '',
                pincode: ''
            };

            try {
                let osmResponse = await fetch(
                    'https://nominatim.openstreetmap.org/reverse?format=json' +
                    '&lat=' + lat +
                    '&lon=' + lng +
                    '&zoom=18&addressdetails=1'
                );

                let osmData = await osmResponse.json();

                result.address = osmData.display_name || 'Address not found';

                if (osmData.address) {
                    result.city =
                        osmData.address.city ||
                        osmData.address.town ||
                        osmData.address.village ||
                        osmData.address.county ||
                        '';

                    result.state = osmData.address.state || '';
                    result.country = osmData.address.country || '';
                    result.pincode = osmData.address.postcode || '';
                }
            } catch (e) {
                console.log("OSM failed", e);
            }

            return result;
        }

        function markAttendance(token, lat, lng, accuracy, address, city, state, country, pincode) {
            fetch('mark_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'token=' + encodeURIComponent(token) +
                        '&lat=' + encodeURIComponent(lat) +
                        '&lng=' + encodeURIComponent(lng) +
                        '&accuracy=' + encodeURIComponent(accuracy) +
                        '&address=' + encodeURIComponent(address) +
                        '&city=' + encodeURIComponent(city) +
                        '&state=' + encodeURIComponent(state) +
                        '&country=' + encodeURIComponent(country) +
                        '&pincode=' + encodeURIComponent(pincode)
                })
                .then(response => response.json())
                .then(data => {
                    // Server auto-detects IN vs OUT and returns data.action
                    let alertClass = data.status ? 'success' : 'danger';
                    let iconClass  = data.action === 'out' ? 'bi-box-arrow-right' :
                                    (data.action === 'in'  ? 'bi-box-arrow-in-right' : 'bi-info-circle');

                    let html = `
            <div class="alert alert-${alertClass} mt-2">
                <h5 class="alert-heading">
                    <i class="bi ${iconClass}"></i>
                    ${data.title}
                </h5>
                <hr>
                ${data.message}
            </div>`;

                    // If success, suggest going back to dashboard
                    if (data.status) {
                        html += `
                <a href="dashboard.php" class="btn btn-primary w-100 mt-2">
                    <i class="bi bi-house"></i> Back to Dashboard
                </a>
                <button type="button" class="btn btn-outline-secondary w-100 mt-2"
                        onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Scan Again
                </button>`;
                    }

                    document.getElementById('result').innerHTML += html;

                    // Scroll to result
                    document.getElementById('result').scrollIntoView({ behavior: 'smooth', block: 'center' });
                })
                .catch(() => {
                    document.getElementById('result').innerHTML += `
            <div class="alert alert-danger mt-2">
                Server error. Please try again.
            </div>`;
                });
        }

        function showLoader(message) {
            document.getElementById('result').innerHTML = `
        <div class="alert alert-warning text-center">
            <div class="spinner-border text-primary mb-2"></div>
            <br>
            <b>${message}</b>
            <br>
            <small>Keep GPS, WiFi and mobile data ON.</small>
        </div>
    `;
        }

        function showError(message) {
            document.getElementById('result').innerHTML = `
        <div class="alert alert-danger">
            ${message}
        </div>
    `;
        }
    </script>

</body>

</html>

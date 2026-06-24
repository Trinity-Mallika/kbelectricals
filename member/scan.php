<?php
include('session.php');

$token = $_GET['token'] ?? '';
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

        .manual-box {
            background: #eef5ff;
            border-radius: 16px;
            padding: 12px;
        }

        .location-box {
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>

<body>

    <div class="wrap">

        <div class="text-center my-3">
            <h3>
                <i class="bi bi-qr-code-scan"></i>
                Attendance Entry
            </h3>

            <p class="text-info">
                Camera + GPS verification
            </p>
        </div>

        <div class="scanner-card">

            <div id="result" class="mb-2"></div>

            <?php if ($token == '') { ?>

                <div id="reader"></div>

                <!-- <div class="manual-box mt-3">
                    <label class="form-label">
                        QR URL / Token
                    </label>

                    <input type="text"
                        id="manualToken"
                        class="form-control"
                        placeholder="Paste QR URL or token">

                    <button type="button"
                        onclick="manualSubmit()"
                        class="btn btn-primary w-100 mt-2">
                        Submit Token
                    </button>
                </div> -->

            <?php } else { ?>

                <div class="alert alert-info">
                    QR URL detected. Click below to mark attendance.
                </div>

                <button type="button"
                    class="btn btn-success w-100 btn-lg"
                    onclick="getLocationAndMark('<?php echo htmlspecialchars($token); ?>')">
                    <i class="bi bi-geo-alt"></i>
                    Allow Location & Mark Attendance
                </button>

            <?php } ?>

            <a href="dashboard.php"
                class="btn btn-outline-dark w-100 mt-3">
                Back Dashboard
            </a>

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

        function manualSubmit() {
            let token = extractToken(document.getElementById('manualToken').value.trim());

            if (token) {
                getLocationAndMark(token);
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
                    let alertClass = data.status ? 'success' : 'danger';

                    document.getElementById('result').innerHTML += `
            <div class="alert alert-${alertClass} mt-2">
                <b>${data.title}</b><br>
                ${data.message}
            </div>
        `;
                })
                .catch(() => {
                    document.getElementById('result').innerHTML += `
            <div class="alert alert-danger mt-2">
                Server error. Please try again.
            </div>
        `;
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
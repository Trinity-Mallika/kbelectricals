<?php

include("../adminsession.php");

$title    = "Shop QR Master";
$pagename = "meeting-master.php";

/* =========================================================
   SAVE / UPDATE
========================================================= */

if (isset($_POST['save'])) {
  $meeting_id = $obj->test_input($_POST['meeting_id']);
  $company_id    = $obj->test_input($_POST['company_id']);
  $title_val  = $obj->test_input(trim($_POST['title']));

  // Auto-fill title from shop name if blank
  if ($title_val === '') {
    $shop_name = $obj->getvalfield(
      "chapter_master",
      "chapter_name",
      "company_id='$company_id'"
    );
    $title_val = $shop_name ? $obj->test_input($shop_name) : 'Shop QR';
  }

  $token = !empty($_POST['qr_token'])
    ? $obj->test_input($_POST['qr_token'])
    : bin2hex(random_bytes(16));

  $fields = [
    'company_id'       => $company_id,
    'title'         => $title_val,
    'location_name' => $obj->test_input($_POST['location_name']),
    'latitude'      => $obj->test_input($_POST['latitude']),
    'longitude'     => $obj->test_input($_POST['longitude']),
    'radius_meter'  => $obj->test_input($_POST['radius_meter']),
    'qr_token'      => $token,
    'status'        => $obj->test_input($_POST['status'])
  ];

  /* ── One-QR-per-shop check ──
       Only one ACTIVE QR allowed per shop.
       When editing, exclude the current row from the check.        */
  $existing = $obj->getvalfield(
    "bni_meetings",
    "COUNT(*)",
    "company_id='$company_id' AND status=1 AND meeting_id!='$meeting_id'"
  );

  if ($existing > 0) {
    echo "<script>
                alert('This shop already has an active QR. Edit or delete it first.');
                history.back();
              </script>";
    exit;
  }

  if ($meeting_id == '') {
    $obj->insert_record('bni_meetings', $fields);
    $action = "1";
  } else {
    $obj->update_record(
      'bni_meetings',
      ['meeting_id' => $meeting_id],
      $fields
    );
    $action = "2";
  }

  echo "<script>location='meeting-master.php?action=$action';</script>";
  exit;
}

/* =========================================================
   DELETE
========================================================= */

if (isset($_GET['del'])) {
  $meeting_id = intval($_GET['del']);

  $obj->delete_record(
    "bni_meetings",
    ['meeting_id' => $meeting_id]
  );

  echo "<script>location='meeting-master.php?msg=del';</script>";
}

/* =========================================================
   EDIT DATA
========================================================= */

$edit = [];

if (isset($_GET['edit'])) {
  $meeting_id = intval($_GET['edit']);

  $result = $obj->select_record(
    "bni_meetings",
    ["meeting_id" => $meeting_id]
  );

  if ($result) {
    $edit = $result;
  }
}

/* =========================================================
   FETCH LIST  (JOIN chapter_master for shop name)
========================================================= */

$rows = $obj->executequery("
    SELECT m.*, c.company_name AS shop_name
    FROM bni_meetings m
    LEFT JOIN company_setting c ON c.company_id = m.company_id
    ORDER BY m.meeting_id DESC
");

/* =========================================================
   FETCH ACTIVE SHOPS for dropdown
========================================================= */

$shops = $obj->executequery("
    SELECT company_id, company_name
    FROM company_setting
    ORDER BY company_name
");

?>

<!DOCTYPE html>
<html>

<head>
  <?php include('component/css.php'); ?>
</head>

<body class="bg-light">

  <?php include('component/sidebar.php'); ?>

  <div class="main w-auto">

    <?php include('component/header.php'); ?>

    <div class="container-fluid py-3">

      <div class="row g-3">

        <!-- =====================================================
                 FORM SECTION
                ====================================================== -->

        <div class="col-lg-4">

          <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body">

              <h5 class="mb-3">
                Generate Shop QR
              </h5>

              <form method="POST">

                <input type="hidden"
                  name="meeting_id"
                  value="<?php echo $edit['meeting_id'] ?? ''; ?>">

                <input type="hidden"
                  name="qr_token"
                  value="<?php echo $edit['qr_token'] ?? ''; ?>">

                <!-- SHOP NAME -->

                <div class="mb-3">

                  <label class="form-label">
                    Shop Name<span class="text-danger fw-bold">*</span>
                  </label>

                  <?php
                  $selectedShop = count($shops) == 1
                    ? $shops[0]['company_id']
                    : ($edit['company_id'] ?? '');
                  ?>

                  <select name="company_id" id="company_id" class="form-select mb-3" required>
                    <option value="">Select Shop</option>

                    <?php foreach ($shops as $s) { ?>
                      <option value="<?php echo $s['company_id']; ?>"
                        <?php echo ($selectedShop == $s['company_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['company_name']); ?>
                      </option>
                    <?php } ?>
                  </select>

                </div>
                <div class="mb-3">

                  <label class="form-label">
                    QR Title <small class="text-muted">(optional — defaults to shop name)</small>
                  </label>

                  <input type="text"
                    name="title"
                    class="form-control"
                    placeholder="Auto-filled from shop name if blank"
                    value="<?php echo htmlspecialchars($edit['title'] ?? ''); ?>">

                </div>

                <!-- LOCATION -->

                <div class="mb-3">

                  <label class="form-label">
                    Location Name
                  </label>

                  <input type="text"
                    name="location_name"
                    class="form-control"
                    placeholder="Enter location / shop address"
                    required
                    value="<?php echo htmlspecialchars($edit['location_name'] ?? ''); ?>">

                </div>

                <!-- LAT / LONG -->

                <div class="row">

                  <div class="col-6 mb-3">

                    <label class="form-label">
                      Latitude
                    </label>

                    <input type="text"
                      name="latitude"
                      class="form-control"
                      placeholder="21.2514"
                      required
                      value="<?php echo htmlspecialchars($edit['latitude'] ?? ''); ?>">

                  </div>

                  <div class="col-6 mb-3">

                    <label class="form-label">
                      Longitude
                    </label>

                    <input type="text"
                      name="longitude"
                      class="form-control"
                      placeholder="81.6296"
                      required
                      value="<?php echo htmlspecialchars($edit['longitude'] ?? ''); ?>">

                  </div>

                </div>

                <!-- CURRENT LOCATION -->

                <div class="mb-3">

                  <button type="button"
                    class="btn btn-outline-primary btn-sm"
                    onclick="getVenueLocation()">

                    <i class="bi bi-geo-alt"></i>
                    Use Current Location

                  </button>

                </div>
                <div id="gps_status"></div>

                <!-- RADIUS -->

                <div class="mb-3">

                  <label class="form-label">
                    Allowed Radius (meter)
                  </label>

                  <input type="number"
                    name="radius_meter"
                    class="form-control"
                    placeholder="Enter radius in meter"
                    required
                    value="<?php echo htmlspecialchars($edit['radius_meter'] ?? 10); ?>">

                </div>

                <!-- STATUS -->

                <div class="mb-3">

                  <label class="form-label">
                    Status
                  </label>

                  <select name="status" class="form-select">

                    <option value="1"
                      <?php echo (($edit['status'] ?? 1) == 1) ? 'selected' : ''; ?>>
                      Active
                    </option>

                    <option value="0"
                      <?php echo (($edit['status'] ?? 1) == 0) ? 'selected' : ''; ?>>
                      Inactive
                    </option>

                  </select>

                </div>

                <!-- BUTTON -->

                <button type="submit"
                  name="save"
                  class="btn btn-primary w-100"
                  onclick="return checkinputmaster('company_id,location_name,latitude,longitude,radius_meter,status');">

                  <i class="bi bi-qr-code"></i>
                  <?php echo !empty($edit['meeting_id']) ? 'Update QR' : 'Generate QR'; ?>
                </button>

              </form>

            </div>

          </div>

        </div>

        <!-- =====================================================
                 TABLE SECTION
                ====================================================== -->

        <div class="col-lg-8">

          <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body">

              <h5 class="mb-3">
                Shop QR List
              </h5>

              <div class="table-responsive">

                <table class="table table-bordered table-striped datatable">

                  <thead>

                    <tr>

                      <th>Shop</th>
                      <th>Title</th>
                      <th>Loction</th>
                      <th>Radius</th>
                      <th>Status</th>
                      <th>QR</th>
                      <th>Action</th>

                    </tr>

                  </thead>

                  <tbody>

                    <?php foreach ($rows as $r) { ?>

                      <tr>

                        <td>
                          <strong><?php echo htmlspecialchars($r['shop_name'] ?? '—'); ?></strong>
                        </td>

                        <td>
                          <?php echo htmlspecialchars($r['title']); ?>
                        </td>

                        <td>
                          <?php echo htmlspecialchars($r['location_name']); ?>
                        </td>

                        <td>
                          <?php echo $r['radius_meter']; ?>m
                        </td>

                        <td>
                          <?php if ($r['status']) { ?>
                            <span class="badge bg-success">Active</span>
                          <?php } else { ?>
                            <span class="badge bg-danger">Inactive</span>
                          <?php } ?>
                        </td>

                        <td>

                          <a href="qr-display.php?meeting_id=<?php echo $r['meeting_id']; ?>"
                            class="btn btn-dark btn-sm">

                            QR

                          </a>

                        </td>

                        <td>

                          <a href="?edit=<?php echo $r['meeting_id']; ?>"
                            class="btn btn-info btn-sm">

                            Edit

                          </a>

                          <a href="?del=<?php echo $r['meeting_id']; ?>"
                            onclick="return confirm('Delete this QR?')"
                            class="btn btn-danger btn-sm">

                            Delete

                          </a>

                        </td>

                      </tr>

                    <?php } ?>

                  </tbody>

                </table>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

  <?php include('component/script.php'); ?>

  <script>
    const GOOGLE_API_KEY = "AIzaSyD60TsOPfBQDMpiGwEWusBT-UBUUM6Y8O8";

    let gpsLoaderInterval = null;

    function startGpsLoader() {
      let dots = 0;
      gpsLoaderInterval = setInterval(function() {
        dots++;
        if (dots > 3) dots = 1;
        let dotText = '.'.repeat(dots);
        document.getElementById('gps_status').innerHTML = `
                    <div class="alert alert-warning text-center">
                        <div class="spinner-border text-primary mb-2"></div>
                        <h6 class="mb-1">Finding Accurate GPS ${dotText}</h6>
                        <small>Please wait...<br>Keep GPS + WiFi + Mobile Data ON</small>
                    </div>
                `;
      }, 500);
    }

    function stopGpsLoader() {
      clearInterval(gpsLoaderInterval);
    }

    function getVenueLocation() {
      if (!navigator.geolocation) {
        alert('Location not supported');
        return;
      }

      startGpsLoader();

      let bestPosition = null;
      let bestAccuracy = 999999;
      let watchId = null;

      watchId = navigator.geolocation.watchPosition(

        async function(position) {
            let accuracy = position.coords.accuracy;
            if (accuracy < bestAccuracy) {
              bestAccuracy = accuracy;
              bestPosition = position;

              document.getElementById('gps_status').innerHTML = `
                            <div class="alert alert-info">
                                <b>Better GPS Found</b>
                                <hr>
                                <b>Latitude:</b> ${position.coords.latitude.toFixed(8)}<br>
                                <b>Longitude:</b> ${position.coords.longitude.toFixed(8)}<br>
                                <b>Accuracy:</b> ${accuracy.toFixed(2)} Meter
                            </div>
                        `;
            }

            if (bestAccuracy <= 15) {
              navigator.geolocation.clearWatch(watchId);
              stopGpsLoader();
              useBestLocation(bestPosition);
            }
          },

          function(error) {
            stopGpsLoader();
            let msg = 'GPS Error';
            if (error.code == 1) msg = 'Please allow location permission';
            else if (error.code == 2) msg = 'Location unavailable';
            else if (error.code == 3) msg = 'GPS timeout';
            document.getElementById('gps_status').innerHTML = `
                        <div class="alert alert-danger">${msg}</div>
                    `;
          },

          {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 60000
          }
      );

      setTimeout(function() {
        navigator.geolocation.clearWatch(watchId);
        stopGpsLoader();
        if (bestPosition) {
          useBestLocation(bestPosition);
        } else {
          document.getElementById('gps_status').innerHTML = `
                        <div class="alert alert-danger">
                            GPS location not found.<br>Please move outdoor and try again.
                        </div>
                    `;
        }
      }, 30000);
    }

    async function useBestLocation(position) {
      let lat = position.coords.latitude;
      let lng = position.coords.longitude;
      let accuracy = position.coords.accuracy;

      document.querySelector('[name=latitude]').value = lat.toFixed(8);
      document.querySelector('[name=longitude]').value = lng.toFixed(8);

      let address = await getGoogleAddress(lat, lng);

      if (document.querySelector('[name=location_name]') && !document.querySelector('[name=location_name]').value) {
        document.querySelector('[name=location_name]').value = address;
      }

      let accuracyClass = accuracy <= 15 ? 'success' : 'warning';
      document.getElementById('gps_status').innerHTML = `
                <div class="alert alert-${accuracyClass}">
                    <h6>GPS Location Found</h6>
                    <hr>
                    <b>Latitude:</b> ${lat.toFixed(8)}<br>
                    <b>Longitude:</b> ${lng.toFixed(8)}<br>
                    <b>Accuracy:</b> ${accuracy.toFixed(2)} Meter
                    <hr>
                    <b>Detected Address:</b><br>${address}
                </div>
            `;
    }

    async function getGoogleAddress(lat, lng) {
      try {
        let url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" +
          lat + "," + lng + "&key=" + GOOGLE_API_KEY;
        let response = await fetch(url);
        let data = await response.json();
        if (data.status === "OK" && data.results.length > 0) {
          return data.results[0].formatted_address;
        }
      } catch (e) {
        console.log(e);
      }
      return "Address not found";
    }
  </script>

</body>

</html>

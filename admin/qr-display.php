<?php include("../adminsession.php");

$title    = "QR Display";
$pagename = "qr-display.php";

/* =========================
   GET MEETING/QR
========================= */

$meeting_id = intval($_GET['meeting_id'] ?? 0);

if (!$meeting_id) {
  $latest = $obj->executequery("
        SELECT *
        FROM bni_meetings
        WHERE status = 1
        ORDER BY meeting_id DESC
        LIMIT 1
    ");

  if ($latest) {
    $meeting_id = $latest[0]['meeting_id'];
  }
}

$meeting = [];

if ($meeting_id) {
  $result = $obj->executequery("
        SELECT m.*, c.company_name AS shop_name
        FROM bni_meetings m
        LEFT JOIN company_setting c ON c.company_id = m.company_id
        WHERE m.meeting_id = '$meeting_id'
        AND m.status = 1
        LIMIT 1
    ");

  if ($result) {
    $meeting = $result[0];
  }
}

/* =========================
   QR LIST (active QRs with shop names) for dropdown
========================= */

$qrs = $obj->executequery("
    SELECT m.meeting_id, m.title, m.company_id, c.company_name AS shop_name
    FROM bni_meetings m
    LEFT JOIN company_setting c ON c.company_id = m.company_id
    WHERE m.status = 1
    ORDER BY c.company_name
");

/* =========================
   QR URL
========================= */

$protocol = (
  isset($_SERVER['HTTPS']) &&
  $_SERVER['HTTPS'] == 'on'
) ? 'https' : 'http';

$base_url =
  $protocol .
  '://' .
  $_SERVER['HTTP_HOST'] .
  dirname(dirname($_SERVER['PHP_SELF']));

$scan_url = '';

if ($meeting) {
  $scan_url =
    $base_url .
    '/member/scan.php?token=' .
    $meeting['qr_token'];
}

$qr_api =
  'https://api.qrserver.com/v1/create-qr-code/?size=360x360&margin=18&data=' .
  urlencode($scan_url);

?>

<!DOCTYPE html>
<html>

<head>

  <?php include('component/css.php'); ?>

  <style>
    .qr-card {
      background: linear-gradient(41deg, #0a2f50, #1399d5, #1a6ca8);
      border-radius: 26px;
      color: #fff;
    }

    .qr-box {
      background: #fff;
      border-radius: 22px;
      padding: 18px;
      display: inline-block;
    }

    .copy-url {
      background: rgba(255, 255, 255, .12);
      border: 1px solid rgba(255, 255, 255, .2);
      color: #fff;
    }

    .copy-url:focus {
      background: rgba(255, 255, 255, .16);
      color: #fff;
      box-shadow: none;
    }

    @media print {
      body * {
        visibility: hidden;
      }

      .print-area,
      .print-area * {
        visibility: visible;
      }

      .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }

      .no-print {
        display: none !important;
      }
    }
  </style>

</head>

<body class="bg-light">

  <?php include('component/sidebar.php'); ?>

  <div class="main w-auto">

    <?php include('component/header.php'); ?>

    <div class="container-fluid py-3">

      <div class="row g-3">

        <!-- SELECT QR -->

        <div class="col-lg-4">

          <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body">

              <h5 class="mb-3">
                Select Shop QR
              </h5>

              <form method="GET">

                <select name="meeting_id"
                  class="form-select mb-3"
                  onchange="this.form.submit()">

                  <option value="">
                    Select Shop QR
                  </option>

                  <?php foreach ($qrs as $q) { ?>

                    <option value="<?php echo $q['meeting_id']; ?>"
                      <?php echo ($meeting_id == $q['meeting_id']) ? 'selected' : ''; ?>>

                      <?php
                      echo htmlspecialchars($q['shop_name'] ?? $q['title']);
                      ?>

                    </option>

                  <?php } ?>

                </select>

              </form>

              <?php if ($meeting) { ?>

                <div class="alert alert-info">

                  <b>Shop:</b>
                  <?php echo htmlspecialchars($meeting['shop_name'] ?? '—'); ?>
                  <br>

                  <b>Venue:</b>
                  <?php echo htmlspecialchars($meeting['location_name']); ?>
                  <br>

                  <b>Radius:</b>
                  <?php echo $meeting['radius_meter']; ?> meter
                  <br>

                  <b>Status:</b>
                  <?php if ($meeting['status']) { ?>
                    <span class="badge bg-success">Active</span>
                  <?php } else { ?>
                    <span class="badge bg-danger">Inactive</span>
                  <?php } ?>

                </div>

              <?php } ?>

            </div>

          </div>

        </div>

        <!-- QR DISPLAY -->

        <div class="col-lg-8">

          <?php if ($meeting) { ?>

            <div class="card qr-card border-0 shadow-lg print-area">

              <div class="card-body text-center p-4">

                <h3 class="mb-1">
                  <?php echo htmlspecialchars($meeting['title']); ?>
                </h3>

                <p class="text-info mb-1">

                  <i class="bi bi-shop"></i>

                  <?php echo htmlspecialchars($meeting['shop_name'] ?? '—'); ?>

                </p>

                <p class="text-white mb-3" style="font-size:13px;">

                  <i class="bi bi-geo-alt"></i>

                  <?php echo htmlspecialchars($meeting['location_name']); ?>
                  &nbsp;|&nbsp; Radius: <?php echo $meeting['radius_meter']; ?>m

                </p>

                <div class="qr-box shadow">

                  <img src="<?php echo $qr_api; ?>"
                    alt="Shop QR"
                    class="img-fluid"
                    id="qrImage">

                </div>

                <div class="mt-4 no-print">

                  <label class="text-start d-block mb-1">
                    QR Attendance URL
                  </label>

                  <div class="input-group">

                    <input type="text"
                      id="scanUrl"
                      class="form-control copy-url"
                      value="<?php echo $scan_url; ?>"
                      style="background: #ffffff47;"
                      readonly
                      placeholder="QR attendance URL">

                    <button type="button"
                      class="btn btn-light"
                      onclick="copyUrl()">

                      <i class="bi bi-clipboard"></i>
                      Copy URL

                    </button>

                  </div>

                </div>

                <div class="d-flex gap-2 justify-content-center flex-wrap mt-4 no-print">

                  <a href="<?php echo $scan_url; ?>"
                    class="btn btn-light rounded-pill"
                    target="_blank">

                    <i class="bi bi-box-arrow-up-right"></i>
                    Open Scan URL

                  </a>

                  <a href="<?php echo $qr_api; ?>"
                    class="btn btn-success rounded-pill"
                    download="shop-qr.png" target="_blank">

                    <i class="bi bi-download"></i>
                    Download QR

                  </a>

                  <button type="button"
                    class="btn btn-outline-light rounded-pill"
                    onclick="window.print()">

                    <i class="bi bi-printer"></i>
                    Print QR

                  </button>

                </div>

              </div>

            </div>

          <?php } else { ?>

            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i>
              Please create a Shop QR first from <a href="meeting-master.php">Create QR</a> page.
            </div>

          <?php } ?>

        </div>

      </div>

    </div>

  </div>

  <?php include('component/script.php'); ?>

  <script>
    function copyUrl() {
      let url = document.getElementById('scanUrl').value;

      navigator.clipboard.writeText(url).then(function() {
        alert('QR URL copied');
      });
    }
  </script>

</body>

</html>
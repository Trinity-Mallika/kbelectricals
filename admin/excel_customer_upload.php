<?php include("../adminsession.php");
$module = $title = "Excel Upload Customer";
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/SimpleXLSX.php';

function clean_val($val)
{
    return ucwords(strtolower(trim(str_replace("\xC2\xA0", '', $val))));
}

$duplicate_accounts = [];
$error_rows = [];

if (isset($_POST['submit'])) {

    if (empty($_FILES['excel_file']['tmp_name'])) {
        die("No file uploaded.");
    }

    if (!$xlsx = SimpleXLSX::parse($_FILES['excel_file']['tmp_name'])) {
        die("Invalid Excel file.");
    }

    // ================= CACHE =================
    $area_cache = [];
    $account_cache = [];
    $route_cache = [];
    $user_cache = [];
    $route_plan_cache = [];

    foreach ($obj->executequery("SELECT area_id, area_name FROM area_master") as $a) {
        $area_cache[strtolower($a['area_name'])] = $a['area_id'];
    }

    foreach ($obj->executequery("SELECT account_id, account_name, area_id FROM account") as $a) {
        $key = strtolower($a['account_name'] . '_' . $a['area_id']);
        $account_cache[$key] = $a['account_id'];
    }

    foreach ($obj->executequery("SELECT route_id, route_name, day_of_week FROM route") as $r) {
        $key = strtolower(trim($r['route_name'])) . '_' . strtolower($r['day_of_week']);
        $route_cache[$key] = $r['route_id'];
    }

    foreach ($obj->executequery("SELECT userid, fullname FROM user") as $u) {
        $user_cache[strtolower($u['fullname'])] = $u['userid'];
    }

    $obj->begin();

    try {

        $firstRow = true;

        foreach ($xlsx->rows() as $r) {

            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            $sequence      = (int)($r[0] ?? 0);
            $account_name  = clean_val($r[1] ?? '');
            $area_name     = clean_val($r[2] ?? '');
            $status        = trim($r[3] ?? '');
            $class         = trim($r[4] ?? '');
            $route_name = preg_replace('/\s+/', ' ', trim($r[5] ?? ''));
            $route_name = ucwords(strtolower($route_name));

            $week          = (int)($r[6] ?? 1);
            $user_name     = strtolower(trim($r[7] ?? ''));

            if (!$account_name || !$area_name || !$route_name) continue;

            if (!isset($area_cache[strtolower($area_name)])) {

                $area_id = $obj->insert_record_lastid("area_master", [
                    "area_name" => $area_name,
                    "createdby" => $loginid,
                    "createdate" => $createdate,
                    "ipaddress" => $ipaddress
                ]);

                $area_cache[strtolower($area_name)] = $area_id;
            }

            $area_id = $area_cache[strtolower($area_name)];

            $acc_key = strtolower($account_name . '_' . $area_id);

            if (!isset($account_cache[$acc_key])) {

                $account_id = $obj->insert_record_lastid("account", [
                    'account_name' => $account_name,
                    'area_id' => $area_id,
                    'status' => $status,
                    'class' => $class,
                    'type' => 'customer',
                    'createdate' => $createdate,
                    'ipaddress' => $ipaddress
                ]);

                $account_cache[$acc_key] = $account_id;
            } else {
                $account_id = $account_cache[$acc_key];
            }

            preg_match('/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday)/i', $route_name, $dayMatch);
            $day = ucfirst(strtolower($dayMatch[1] ?? ''));

            if (!$day) {
                $error_rows[] = "Invalid day in route: $route_name";
                continue;
            }

            $route_key = strtolower(trim($route_name)) . '_' . strtolower($day);

            if (!isset($route_cache[$route_key])) {

                $route_id = $obj->getvalfield(
                    "route",
                    "route_id",
                    "LOWER(route_name)=LOWER('$route_name') AND day_of_week='$day'"
                );

                if (!$route_id) {
                    $route_id = $obj->insert_record_lastid("route", [
                        "route_name" => $route_name,
                        "day_of_week" => $day,
                        "createdby" => $loginid,
                        "createdate" => $createdate,
                        "ipaddress" => $ipaddress
                    ]);
                }

                $route_cache[$route_key] = $route_id;
            } else {
                $route_id = $route_cache[$route_key];
            }

            $user_id = $user_cache[$user_name] ?? 0;

            if (!$user_id) {
                $error_rows[] = "User not found: $user_name";
                continue;
            }

            $plan_key = $route_id . '_' . $user_id . '_' . $week;

            if (!isset($route_plan_cache[$plan_key])) {

                $route_plan_id = $obj->getvalfield(
                    "route_plan",
                    "route_planid",
                    "route_id='$route_id' AND sales_executive_id='$user_id' AND week_number='$week'"
                );

                if (!$route_plan_id) {

                    $route_plan_id = $obj->insert_record_lastid("route_plan", [
                        "route_id" => $route_id,
                        "sales_executive_id" => $user_id,
                        "week_number" => $week,
                        "createdate" => $createdate
                    ]);
                }

                $route_plan_cache[$plan_key] = $route_plan_id;
            }

            $route_plan_id = $route_plan_cache[$plan_key];

            // ================= ROUTE PLAN DETAILS =================
            $exists = $obj->getvalfield(
                "route_plan_details",
                "route_plan_detail_id",
                "route_plan_id='$route_plan_id' AND account_id='$account_id'"
            );

            if (!$exists) {

                $obj->insert_record("route_plan_details", [
                    "route_plan_id" => $route_plan_id,
                    "account_id" => $account_id,
                    "sequence" => $sequence
                ]);
            }
        }

        $obj->commit();
    } catch (Exception $e) {
        $obj->rollback();
        die("Error: " . $e->getMessage());
    }

    $_SESSION['duplicate_accounts'] = $duplicate_accounts;
    $_SESSION['error_rows'] = $error_rows;

    echo "<script>location='$pagename';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .card-header {
            background-color: #06163a;
        }
    </style>
</head>

<body class="bg-light">
    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend><?php echo $module; ?></legend>
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="card">
                                <div class="card-header text-white"><?php echo $module; ?></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label for="heading">Upload Excel<span class="text-danger fw-bold">*</span></label>
                                            <input type="file" class="form-control form-control-sm" name="excel_file" id="imgname" accept=".xlsx">
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="Save" onClick="return checkinputmaster('imgname');">
                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
                <div class="col-lg-12 mt-2">
                    <div class="card">
                        <div class="card-header text-white">List</div>
                        <div class="card-body">
                            <div class="row">

                                <?php if (!empty($duplicate_accounts)) { ?>

                                    <h5 class="mb-3 text-danger">
                                        Duplicate Records (<?= count($duplicate_accounts) ?>)
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Row</th>
                                                    <th>Account</th>
                                                    <th>Area</th>
                                                    <th>Route</th>
                                                    <th>Day</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($duplicate_accounts as $d) { ?>
                                                    <tr>
                                                        <td><?= $d['row'] ?></td>
                                                        <td><?= $d['account_name'] ?></td>
                                                        <td><?= $d['area_name'] ?></td>
                                                        <td><?= $d['route'] ?></td>
                                                        <td><?= $d['day'] ?></td>
                                                        <td>
                                                            <span class="badge <?= ($d['type'] == 'DB Duplicate') ? 'bg-danger' : 'bg-warning' ?>">
                                                                <?= $d['type'] ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                <?php } else { ?>

                                    <div class="alert alert-success mb-0">
                                        No duplicate records found.
                                    </div>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

<?php include('component/script.php'); ?>
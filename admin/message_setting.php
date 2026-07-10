<?php
include("../adminsession.php");

$title = "Message Master";
$pagename = "message_setting.php";
$module = "Message Master";
$submodule = "Message Master List";
$btn_name = "Save";
$tblname = "m_message";
$tblpkey = "message_id";
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

$messages = [];
$result = $obj->executequery("SELECT * FROM $tblname WHERE companyid='$companyid'");
foreach ($result as $row) {
    $messages[$row['type']] = $row;
}

if (isset($_POST['submit'])) {

    $message      = trim($_POST['message']);
    $message_type = trim($_POST['message_type']);

    if ($message == "") {

        echo "<script>alert('Please enter message');</script>";
    } else {

        $form_data = array(
            "message"   => $message,
            "type"      => $message_type,
            "createdby" => $loginid,
            "ipaddress" => $ipaddress,
            "companyid" => $companyid
        );

        if (isset($messages[$message_type])) {
            $form_data["lastupdated"] = $createdate;
            $where = array(
                $tblpkey => $messages[$message_type][$tblpkey]
            );
            $obj->update_record($tblname, $where, $form_data);
            $action = 2;
        } else {
            $form_data["createdate"] = $createdate;
            $obj->insert_record($tblname, $form_data);
            $action = 1;
        }

        echo "<script>location='$pagename?action=$action';</script>";
        exit;
    }
}

function getMessage($messages, $type)
{
    return isset($messages[$type]['message'])
        ? $messages[$type]['message']
        : "";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php include('component/css.php'); ?>

    <style>
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
                        <legend>
                            <?php echo $title; ?>
                        </legend>
                        <?php include('component/alert.php'); ?>
                        <form action="" method="post">
                            <div class="card">
                                <div class="card-header text-white">
                                    Daily Visit Message
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <textarea class="form-control form-control-sm"
                                                name="message"
                                                rows="12"><?php echo htmlspecialchars(getMessage($messages, 'daily_visit')); ?></textarea>
                                            <input type="hidden" name="message_type" value="daily_visit">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <form action="" method="post">
                            <div class="card">
                                <div class="card-header text-white">
                                    Ledger Message
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <textarea class="form-control form-control-sm"
                                                name="message"
                                                rows="12"><?php echo htmlspecialchars(getMessage($messages, 'ledger_msg')); ?></textarea>
                                            <input type="hidden" name="message_type" value="ledger_msg">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
    <?php include('component/script.php'); ?>
</body>

</html>
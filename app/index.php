<?php include("../action.php");
if (isset($_COOKIE['kbelectrical_mobile_app'])) {
    $mobile_no = $_COOKIE['kbelectrical_mobile_app'];
    $password = $_COOKIE['kbelectrical_password_app'];
} else {
    $mobile_no = "";
    $password = "";
}

if (isset($_POST['login'])) {

    $mobile_no = $obj->test_input($_POST['mobile_no']);
    $password  = $obj->test_input($_POST['password']);

    if ($mobile_no != "" && $password != "") {

        $dbmobile = $obj->getvalfield("user", "mobile", "mobile='$mobile_no'");
        if (!$dbmobile) {
            echo "mobile_not_found";
            exit;
        }

        $dbpassword = $obj->getvalfield("user", "password", "mobile='$mobile_no' AND password='$password'");
        if (!$dbpassword) {
            echo "password_incorrect";
            exit;
        }

        $session_data = $obj->session_method_app("user", $mobile_no, $password);

        if ($session_data['usertype'] == "sales") {
            $_SESSION['salesuserid'] = $session_data['userid'];
            $_SESSION['usertype'] = $session_data['usertype'];
            $_SESSION['companyid'] = $session_data['companyid'];

            setcookie('kbelectrical_mobile_app', $mobile_no, time() + (365 * 24 * 60 * 60));
            setcookie('kbelectrical_password_app', $password, time() + (365 * 24 * 60 * 60));

            echo "success";
        } else {
            echo "user_not_sales";
        }
    } else {
        echo "error";
    }

    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <?php include("inc/css-file.php"); ?>
</head>

<body>
    <section class=" logo-card-top">
        <div class="m-auto text-center logo">
            <div class="img-div">
                <img src="img/logo.png" alt="KBELECTRICAL" class="img-fluid rounded-3" style="width: 250px;">
            </div>
            <!-- <h1 class="text-logo">KBELECTRICAL</h1> -->
        </div>
        <div class="card p-4 input-card border-0 shadow-lg">
            <h2 class="title text-center mb-4">Sales Executive Login</h2>
            <!-- <form action="dashboard.php" method="POST"> -->
            <form method="POST" id="loginForm">
                <div class="mb-3">
                    <label for="mobile" class="form-label">Mobile No.</label>
                    <input type="text" class="form-control shadow-sm" id="mobile" name="mobile"
                        placeholder="Mobile number" value="<?= $mobile_no ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control shadow-sm" id="password" name="password"
                        placeholder="Password" value="<?= $password ?>">
                </div>
                <div class="d-grid mt-4">
                    <a class="btn" id="loginBtn" onclick="Login()">Login</a>
                </div>
            </form>
        </div>
    </section>

    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function Login() {
        let mobile_no = document.getElementById('mobile').value.trim();
        let password = document.getElementById('password').value.trim();

        if (mobile_no === "") {
            Swal.fire({
                icon: 'warning',
                title: 'Mobile Number Required',
                text: 'Please enter your Registration Mobile Number.',
                confirmButtonColor: '#198754'
            }).then(() => {
                document.getElementById('mobile').focus();
            });
            return;
        }

        if (password === "") {
            Swal.fire({
                icon: 'warning',
                title: 'Password Required',
                text: 'Please enter your Password.',
                confirmButtonColor: '#198754'
            }).then(() => {
                document.getElementById('password').focus();
            });
            return;
        }


        // AJAX to same page
        fetch("", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "login=1&mobile_no=" + encodeURIComponent(mobile_no) +
                    "&password=" + encodeURIComponent(password)
            })
            .then(res => res.text())
            .then(data => {
                data = data.trim();

                if (data === "success") {
                    window.location.href = "dashboard.php";
                } else if (data === "user_not_sales") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Access Denied',
                        text: 'This user is not a sales executive',
                        confirmButtonColor: '#dc3545'
                    });
                } else if (data === "mobile_not_found") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Mobile Not Registered',
                        text: 'This mobile number is not registered in the system',
                        confirmButtonColor: '#dc3545'
                    });
                } else if (data === "password_incorrect") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Wrong Password',
                        text: 'The password you entered is incorrect',
                        confirmButtonColor: '#dc3545'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: 'Invalid Mobile Number or Password',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Try again later.',
                    confirmButtonColor: '#dc3545'
                });
            });
    }
</script>

</html>
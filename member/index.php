<?php

include('../action.php');

if (isset($_SESSION['member_id'])) {
    echo "<script>location='dashboard.php'</script>";
}

$msg = '';

if (isset($_POST['password'])) {


    $mobile = $obj->test_input(
        trim($_POST['mobile'])
    );

    $password = $obj->test_input(
        trim($_POST['password'])
    );

    $query = "
        SELECT *
        FROM user
        WHERE mobile = '$mobile'
        AND password = '$password'
        AND status = '1'
        LIMIT 1
    ";

    $result = $obj->executequery($query);

    if (!empty($result)) {
        $_SESSION['member_id'] =
            $result[0]['userid'];

        $_SESSION['member_name'] =
            $result[0]['fullname'];

        $_SESSION['chapter_id'] =
            $result[0]['companyid'];


        echo "
        <script>
            location='dashboard.php'
        </script>";
        exit;
    } else {
        $msg = "Invalid mobile number or password";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Employee Login</title>

    <link rel="stylesheet" href="../admin/assets/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/assets/css/bootstrap.min.css">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(40, 122, 177, .45), transparent 35%),
                linear-gradient(135deg, #06163a, #287ab1);
            display: flex;
            align-items: center;
        }

        .login-card {
            border: 0;
            border-radius: 28px;
            overflow: hidden;
            background: rgba(255, 255, 255, .97);
            backdrop-filter: blur(12px);
        }

        .brand {
            background: linear-gradient(135deg, #06163a, #0d3b77);
            color: #fff;
            padding: 32px 24px;
        }

        .logo-circle {
            height: 76px;
            width: 76px;
            border-radius: 50%;
            background: #fff;
            color: #06163a;
            display: grid;
            place-items: center;
            font-size: 36px;
            margin: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        }

        .form-control {
            height: 52px;
            border-radius: 14px;
            padding-left: 44px;
        }

        .form-control:focus {
            border-color: #287ab1;
            box-shadow: 0 0 0 .2rem rgba(40, 122, 177, .18);
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
        }

        .eye-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 5;
        }

        .password-input {
            padding-right: 46px;
        }

        .btn-primary {
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0d6efd, #287ab1);
            border: 0;
            font-weight: 600;
        }

        .btn-primary:hover {
            opacity: .92;
        }

        .small-note {
            font-size: 13px;
        }

        @media (max-width: 576px) {
            body {
                align-items: flex-start;
                padding-top: 28px;
            }

            .brand {
                padding: 26px 18px;
            }

            .card-body {
                padding: 22px !important;
            }
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-4 col-md-6 col-12">

                <div class="card login-card shadow-lg">

                    <div class="brand text-center">

                        <div class="logo-circle">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>

                        <h3 class="mt-3 mb-1">
                            Employee Login
                        </h3>

                        <p class="mb-0 text-info">
                            QR + GPS Attendance
                        </p>

                    </div>

                    <div class="card-body p-4">

                        <?php if ($msg != '') { ?>

                            <div class="alert alert-danger">
                                <?php echo $msg; ?>
                            </div>

                        <?php } ?>

                        <form method="POST" id="loginForm">

                            <div class="mb-3">

                                <label class="form-label">
                                    Mobile Number
                                </label>

                                <div class="position-relative">

                                    <i class="bi bi-phone input-icon"></i>

                                    <input type="text"
                                        name="mobile"
                                        class="form-control"
                                        placeholder="Enter registered mobile number"
                                        maxlength="10"
                                        pattern="[0-9]{10}"
                                        inputmode="numeric"
                                        required>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <div class="position-relative">

                                    <i class="bi bi-lock input-icon"></i>

                                    <input type="password"
                                        name="password"
                                        id="password"
                                        class="form-control password-input"
                                        placeholder="Enter your password"
                                        required>

                                    <span class="eye-icon"
                                        onclick="togglePassword()">

                                        <i class="bi bi-eye" id="eyeIcon"></i>

                                    </span>

                                </div>

                            </div>

                            <button type="submit"
                                name="login"
                                id="loginBtn"
                                class="btn btn-primary w-100">

                                <span id="loginText">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    Login
                                </span>

                            </button>

                        </form>



                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        function togglePassword() {
            let password = document.getElementById('password');
            let eyeIcon = document.getElementById('eyeIcon');

            if (password.type === 'password') {
                password.type = 'text';

                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';

                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginText').innerHTML =
                '<span class="spinner-border spinner-border-sm"></span> Please wait...';

            document.getElementById('loginBtn').disabled = true;
        });
    </script>

</body>

</html>
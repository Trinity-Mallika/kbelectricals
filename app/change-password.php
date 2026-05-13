<?php
include("appsession.php");
$pagename = 'change-password.php';
$title = 'Change Password';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Bhorawat</title>
    <?php include("inc/css-file.php"); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="dashboard">
    <section class="top-sec">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="card border-0 shadow-lg mb-3">
                <form id="changePassForm">
                    <div class="mb-3">
                        <label class="form-label">Old Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control shadow-sm" id="old_password" name="old_password"
                                placeholder="Old Password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('old_password')">👁️</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control shadow-sm" id="new_password" name="new_password"
                                placeholder="New Password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('new_password')">👁️</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control shadow-sm" id="confirm_password" name="confirm_password"
                                placeholder="Confirm Password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password')">👁️</button>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php include("inc/js-file.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle password visibility
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }

        document.getElementById("changePassForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const oldPass = document.getElementById("old_password").value.trim();
            const newPass = document.getElementById("new_password").value.trim();
            const confirmPass = document.getElementById("confirm_password").value.trim();

            // 🔹 Client-side validation
            if (!oldPass || !newPass || !confirmPass) {
                Swal.fire("Error", "All fields are required", "warning");
                return;
            }

            if (newPass !== confirmPass) {
                Swal.fire("Error", "New Password and Confirm Password do not match", "warning");
                return;
            }

            const submitBtn = document.getElementById("submitBtn");
            submitBtn.disabled = true; // prevent double submit

            let formData = new FormData(this);

            fetch("update-password.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    submitBtn.disabled = false;

                    switch (data.trim()) {
                        case "success":
                            Swal.fire({
                                icon: "success",
                                title: "Password Updated Successfully",
                                timer: 1500,
                                showConfirmButton: false
                            });
                            this.reset();
                            break;

                        case "notmatch":
                            Swal.fire("Error", "Passwords do not match", "error");
                            break;

                        case "wrong":
                            Swal.fire("Error", "Old password is incorrect", "error");
                            break;

                        default:
                            Swal.fire("Error", "Something went wrong", "error");
                            break;
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    Swal.fire("Error", "Network or server error", "error");
                    console.error(err);
                });
        });
    </script>
</body>

</html>
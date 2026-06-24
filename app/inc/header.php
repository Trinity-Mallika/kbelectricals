<header class="rounded-4 ms-2 me-2">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-3 d-flex align-items-center">

                <a href="dashboard.php" class="me-2">
                    <i class="bi bi-house-door text-white fs-5"></i>
                </a>

                <a href="javascript:void(0)" onclick="refreshPage()" class="me-2">
                    <i class="bi bi-arrow-clockwise text-white fs-5"></i>
                </a>

            </div>

            <div class="col-6 text-center">
                <h6 class="text-white mb-0"><?= $title ?></h6>
            </div>

            <div class="col-3 d-flex justify-content-end align-items-center">

                <div class="dropdown me-2">
                    <button class="btn btn-sm position-relative p-0"
                        type="button"
                        data-bs-toggle="dropdown"
                        style="background:none;border:none;">

                        <i class="bi bi-bell fs-5 text-white"></i>

                        <span id="notificationCount"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size:10px;display:none;">
                            0
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end"
                        id="notificationList">
                        <li>
                            <span class="dropdown-item-text text-muted">
                                No Notifications
                            </span>
                        </li>
                    </ul>
                </div>

                <a data-bs-toggle="offcanvas"
                    href="#offcanvasExample">
                    <i class="bi bi-list-nested fs-3 text-white"></i>
                </a>

            </div>

        </div>
    </div>
</header>

<!-- sidemenu  -->
<div class="offcanvas offcanvas-start rounded-end-5" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel" style="background: linear-gradient(45deg, #157bb0, #023a5b);">
    <div class="offcanvas-body">
        <div class="row">
            <div class="col-9 col-lg-9 col-md-9 p-2">
                <h2 class="text-white">KB Electricals</h2>
            </div>
            <div class="col-3 col-lg-3 col-md-3 positon-relative text-center">
                <button type="button" class="btn btn-light positon-absolute rounded-circle bg-blue text-white border-2 pt-1 pb-1 ps-2 pe-2" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-chevron-left"></i></button>
            </div>
            <hr>
            <div class="col-12 col-lg-12 col-md-12 p-2">
                <div class="d-flex">
                    <div class="profile-bg">
                        <i class="bi bi-person-square fs-1 text-white mt-1"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="text-white mb-0">
                            Hi, <?php echo $obj->getvalfield("user", "fullname", "userid='$loginid'"); ?>
                        </h4>

                        <small class="text-wlight">
                            +91 <?php echo $obj->getvalfield("user", "mobile", "userid='$loginid'"); ?>
                        </small>
                    </div>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <hr>
            </div>
            <div class="col-12">
                <ul class="list-group list-group-flush side-menu-list">
                    <a href="dashboard.php" class="mt-2">
                        <li class="list-group-item border-0"><span><i class="bi bi-house-fill"></i></span>&nbsp; Home</li>
                    </a>
                    <a href="check-in.php" class="mt-2">
                        <li class="list-group-item border-0"><span> <i class="bi bi-box"></i></span>&nbsp; Daily Visit Entry</li>
                    </a>
                    <a href="create-counter.php" class="mt-2">
                        <li class="list-group-item border-0"><span> <i class="bi bi-box"></i></span>&nbsp; Create Counter</li>
                    </a>
                    <a href="my-order.php" class="mt-2">
                        <li class="list-group-item border-0"><span> <i class="bi bi-person-add"></i></span>&nbsp; Order Entry</li>
                    </a>

                    <a href="order-list.php" class="mt-2">
                        <li class="list-group-item border-0"><span><i class="bi bi-list-columns-reverse"></i></span>&nbsp; Order List</li>
                    </a>

                    <a href="add_payment.php" class="mt-2">
                        <li class="list-group-item border-0"><span><i class="bi bi-credit-card"></i></span>&nbsp; Add Payment</li>
                    </a>
                    <a href="monthly_target.php" class="mt-2">
                        <li class="list-group-item border-0"><span><i class="bi bi-credit-card"></i></span>&nbsp; Monthly Target</li>
                    </a>
                    <a href="electrician.php" class="mt-2">
                        <li class="list-group-item border-0"><span><i class="bi bi-credit-card"></i></span>&nbsp; Electrician</li>
                    </a>
                    <hr>
                    <a href="change-password.php" class="mt-2">
                        <li class="list-group-item border-0"><span><i class="bi bi-lock-fill"></i></span>&nbsp; Change Password</li>
                    </a>
                    <a href="logout.php" class="mt-2">
                        <li class="list-group-item border-0"><span><i class="bi bi-box-arrow-right"></i></span>&nbsp; Log-Out</li>
                    </a>
                </ul>
            </div>
        </div>
    </div>
</div>
<nav class="navbar shadow-sm border-bottom" style="background: linear-gradient(292deg, #124069, #169cd8);position: sticky;
    top: 0px;
    z-index: 9;">

    <div class="container-fluid ">

        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop" aria-controls="staticBackdrop">

            <span class="navbar-toggler-icon"></span>

        </button>
        <div class="d-flex">
            <div class="dropdown">


                <button class="btn btn-light btn-sm me-2 redius-2" data-bs-toggle="modal" data-bs-target="#companyModal">
                    <?php echo $obj->getvalfield("company_setting", "company_name", "company_id='$companyid'"); ?> </button>
                <a href="#" class="text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">

                    <i class="bi bi-person-circle text-white"></i>

                </a>



                <ul class="dropdown-menu end-0 text-small shadow" aria-labelledby="dropdownUser1" style="left: auto;">

                    <li><a class="dropdown-item" href="user-master.php"><?php echo "Hi, " . ucfirst($obj->getvalfield("user", "username", "userid='$loginid'")); ?></a></li>



                    <li>

                        <hr class="dropdown-divider">

                    </li>

                    <li><a class="dropdown-item" href="logout.php">Sign out</a></li>

                </ul>

            </div>

        </div>

    </div>

</nav>
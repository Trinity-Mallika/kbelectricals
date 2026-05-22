<style>
    /* width */

    ::-webkit-scrollbar {

        width: 5px;

    }



    /* Track */

    ::-webkit-scrollbar-track {

        background: #f1f1f1;

    }



    /* Handle */

    ::-webkit-scrollbar-thumb {

        background: #888;

    }



    /* Handle on hover */

    ::-webkit-scrollbar-thumb:hover {

        background: #555;

    }
</style>



<div class="offcanvas show shadow-sm text-white offcanvas-start sidebar-offcanvas" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="staticBackdrop" aria-labelledby="staticBackdropLabel" style="width: 230px;background: linear-gradient(292deg, #124069, #169cd8);">

    <div class="offcanvas-header shadow-sm">

        <img src="../logo.png" alt="" class="w-100 rounded-2">

        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>

    </div>

    <hr class="mt-0" />

    <div class="offcanvas-body p-0">

        <ul class="nav flex-column mt-3">

            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "dashboard.php") ? "active" : ""; ?>" href="dashboard.php">

                    <i class="bi bi-speedometer2"></i> &nbsp; Dashboard

                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                </a>

            </li>

            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "company.php" || $pagename == "add_behavioural_points.php" || $pagename == "kra_behavioural_aspect.php" || $pagename == "upload_mrp_excel.php" || $pagename == "session-master.php" || $pagename == "area_master.php" || $pagename == "user-master.php" || $pagename == "category_master.php" || $pagename == "product_master.php" || $pagename == "brand_master.php" || $pagename == "unit_master.php" || $pagename == "accounts.php" || $pagename == "bank_master.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#master" aria-expanded="true">
                    <i class="bi bi-pencil-square"></i> &nbsp; Master
                    <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                </a>

                <div class="collapse <?php echo ($pagename == "company.php" || $pagename == "add_behavioural_points.php" || $pagename == "kra_behavioural_aspect.php" || $pagename == "upload_mrp_excel.php" || $pagename == "session-master.php" || $pagename == "area_master.php" || $pagename == "user-master.php" || $pagename == "category_master.php" || $pagename == "product_master.php" || $pagename == "brand_master.php" || $pagename == "unit_master.php" || $pagename == "accounts.php" || $pagename == "bank_master.php") ? "show" : ""; ?>" id="master">

                    <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                        <li>

                            <a href="company.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "company.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Company Setting

                            </a>

                        </li>
                        <li>

                            <a href="session-master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "session-master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Session Master

                            </a>

                        </li>
                        <li>

                            <a href="area_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "area_master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Area Master

                            </a>

                        </li>
                        <li>

                            <a href="brand_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "brand_master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Brand Master

                            </a>

                        </li>
                        <li>

                            <a href="category_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "category_master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Category Master

                            </a>

                        </li>
                        <li>

                            <a href="unit_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "unit_master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Unit Master

                            </a>

                        </li>
                        <li>

                            <a href="product_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "product_master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Product Master

                            </a>

                        </li>
                        <li>

                            <a href="upload_mrp_excel.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "upload_mrp_excel.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Upload Product MRP

                            </a>

                        </li>
                        <li>
                            <a href="accounts.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "accounts.php") ? "active" : ""; ?>">
                                <i class="bi bi-chevron-right"></i> &nbsp; Counter Master
                            </a>
                        </li>
                        <li>

                            <a href="bank_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "bank_master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Bank Master

                            </a>

                        </li>

                        <li>

                            <a href="user-master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "user-master.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; User Master

                            </a>

                        </li>

                        <li>

                            <a href="kra_behavioural_aspect.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "kra_behavioural_aspect.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; KRA Behavioural Aspects

                            </a>

                        </li>

                        <li>

                            <a href="add_behavioural_points.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "add_behavioural_points.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Add Behavioural Points

                            </a>

                        </li>

                    </ul>

                </div>

            </li>

            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "route_wise_counter.php" || $pagename == "route.php" || $pagename == "assign_route.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#route_setting" aria-expanded="true">
                    <i class="bi bi-pin-map"></i>&nbsp; Route Setting
                    <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                </a>

                <div class="collapse <?php echo ($pagename == "route_wise_counter.php" || $pagename == "route.php" || $pagename == "assign_route.php") ? "show" : ""; ?>" id="route_setting">

                    <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                        <li>

                            <a href="route.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "route.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Route Entry
                            </a>

                        </li>
                        <li>

                            <a href="route_wise_counter.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "route_wise_counter.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Routewise Counter Setting
                            </a>

                        </li>
                        <li>

                            <a href="assign_route.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "assign_route.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Assign Route
                            </a>

                        </li>
                    </ul>

                </div>

            </li>
            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "kra_setting_report.php") ? "active" : ""; ?>" href="kra_setting_report.php">

                    <i class="bi bi-receipt"></i> &nbsp; KRA Setting

                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                </a>

            </li>
            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "quotation.php" || $pagename == "quotation_list.php") ? "active" : ""; ?>" href="quotation.php">

                    <i class="bi bi-receipt"></i> &nbsp; Quotation

                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                </a>

            </li>
            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "scheme_entry.php") ? "active" : ""; ?>" href="scheme_entry.php">

                    <i class="bi bi-receipt"></i> &nbsp; Scheme Entry

                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                </a>

            </li>
            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "order_list.php") ? "active" : ""; ?>" href="order_list.php">

                    <i class="bi bi-receipt"></i> &nbsp; Order List

                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                </a>

            </li>
            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "daily_visit_list.php" || $pagename == "accounts_list.php" || $pagename == "payment_list.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#reports" aria-expanded="true">
                    <i class="bi bi-bar-chart"></i>&nbsp; Reports
                    <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                </a>

                <div class="collapse <?php echo ($pagename == "daily_visit_list.php" || $pagename == "accounts_list.php" || $pagename == "payment_list.php") ? "show" : ""; ?>" id="reports">

                    <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                        <li>
                            <a href="accounts_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "accounts_list.php") ? "active" : ""; ?>">
                                <i class="bi bi-chevron-right"></i> &nbsp; New Counter List
                            </a>
                        </li>
                        <li>

                            <a href="daily_visit_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "daily_visit_list.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Daily Visit's Entries
                            </a>

                        </li>

                        <li>

                            <a href="payment_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "payment_list.php") ? "active" : ""; ?>">

                                <i class="bi bi-chevron-right"></i> &nbsp; Payment List
                            </a>

                        </li>
                    </ul>

                </div>

            </li>

            <li class="nav-item shadow-sm">

                <a class="nav-link <?php echo ($pagename == "change-password.php") ? "active" : ""; ?>" href="change-password.php">

                    <i class="bi bi-lock"></i> &nbsp; Change Password

                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                </a>

            </li>


        </ul>

    </div>



</div>

<!-- modal -->
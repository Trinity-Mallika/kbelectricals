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

<?php $comp_logo_side = $obj->getvalfield("company_setting", "comp_logo", "company_id='$companyid'");
$sideImg = '/uploaded/company/' . $comp_logo_side; ?>

<div class="offcanvas show shadow-sm text-white offcanvas-start sidebar-offcanvas" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="staticBackdrop" aria-labelledby="staticBackdropLabel" style="width: 230px;background: #1a6ca8;">

    <div class="offcanvas-header shadow-sm">

        <img src="<?= ($sideImg != '') ? 'uploaded/company/' . $comp_logo_side : "../logo.png" ?>" alt="" class="w-100 rounded-2">

        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>

    </div>

    <hr class="mt-0" />

    <div class="offcanvas-body p-0">

        <ul class="nav flex-column mt-3">

            <li class="nav-item ">

                <a class="nav-link <?php echo ($pagename == "dashboard.php") ? "active" : ""; ?>" href="dashboard.php">

                    <i class="bi bi-speedometer2"></i> &nbsp; Dashboard

                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                </a>

            </li>
            <?php
            $master_chk = $obj->checkmenu("Master", $loginid);
            if ($master_chk != '0' || $_SESSION['usertype'] == 'admin') {
            ?>

                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "company.php" || $pagename == "message_setting.php" || $pagename == "setting.php" || $pagename == "upload_mrp_excel.php" || $pagename == "session-master.php" || $pagename == "area_master.php" || $pagename == "user-master.php" || $pagename == "category_master.php" || $pagename == "product_master.php" || $pagename == "brand_master.php" || $pagename == "unit_master.php" || $pagename == "document_master.php" || $pagename == "accounts.php" || $pagename == "electrician.php" || $pagename == "bank_master.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#master" aria-expanded="true">
                        <i class="bi bi-pencil-square"></i> &nbsp; Master
                        <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                    </a>

                    <div class="collapse <?php echo ($pagename == "company.php" || $pagename == "message_setting.php" || $pagename == "setting.php" || $pagename == "upload_mrp_excel.php" || $pagename == "session-master.php" || $pagename == "area_master.php" || $pagename == "user-master.php" || $pagename == "category_master.php" || $pagename == "product_master.php" || $pagename == "brand_master.php" || $pagename == "unit_master.php" || $pagename == "document_master.php" || $pagename == "accounts.php" || $pagename == "electrician.php" || $pagename == "bank_master.php") ? "show" : ""; ?>" id="master">

                        <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                            <?php
                            $chkmenu = $obj->check_menuname("company.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="company.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "company.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Company Setting

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("message_setting.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="message_setting.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "message_setting.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Message Setting

                                    </a>

                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("setting.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>

                                <li>

                                    <a href="setting.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "setting.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Attendance Setting

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("session-master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="session-master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "session-master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Session Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("area_master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="area_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "area_master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Area Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("brand_master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="brand_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "brand_master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Brand Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("category_master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="category_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "category_master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Category Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("unit_master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="unit_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "unit_master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Unit Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("product_master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="product_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "product_master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Product Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("upload_mrp_excel.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="upload_mrp_excel.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "upload_mrp_excel.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Upload Product MRP

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("accounts.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>
                                    <a href="accounts.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "accounts.php") ? "active" : ""; ?>">
                                        <i class="bi bi-chevron-right"></i> &nbsp; Counter Master
                                    </a>
                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("electrician.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>
                                    <a href="electrician.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "electrician.php") ? "active" : ""; ?>">
                                        <i class="bi bi-chevron-right"></i> &nbsp; Electrician Master
                                    </a>
                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("bank_master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="bank_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "bank_master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Bank Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("document_master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="document_master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "document_master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Document Master

                                    </a>

                                </li>
                            <?php }
                            $chkmenu = $obj->check_menuname("user-master.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>

                                <li>

                                    <a href="user-master.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "user-master.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; User Master

                                    </a>

                                </li>
                            <?php } ?>
                        </ul>

                    </div>

                </li>
            <?php }
            $master_chk = $obj->checkmenu("Route Setting", $loginid);
            if ($master_chk != '0' || $_SESSION['usertype'] == 'admin') {
            ?>
                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "route_wise_counter.php" || $pagename == "route.php" || $pagename == "assign_route.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#route_setting" aria-expanded="true">
                        <i class="bi bi-signpost-split"></i>&nbsp; Route Setting
                        <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                    </a>

                    <div class="collapse <?php echo ($pagename == "route_wise_counter.php" || $pagename == "route.php" || $pagename == "assign_route.php") ? "show" : ""; ?>" id="route_setting">

                        <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                            <?php
                            $chkmenu = $obj->check_menuname("route.php", $loginid);
                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                            ?>
                                <li>

                                    <a href="route.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "route.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Route Entry
                                    </a>

                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("route_wise_counter.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>

                                    <a href="route_wise_counter.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "route_wise_counter.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Routewise Counter Setting
                                    </a>

                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("assign_route.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>

                                    <a href="assign_route.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "assign_route.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Assign Route
                                    </a>

                                </li>
                            <?php } ?>
                        </ul>

                    </div>

                </li>
            <?php }
            $chkmenu = $obj->check_menuname("kra_setting_report.php", $loginid);
            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
            ?>

                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "kra_setting_report.php") ? "active" : ""; ?>" href="kra_setting_report.php">

                        <i class="bi bi-bullseye"></i> &nbsp; KRA Setting
                        <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                    </a>

                </li>
            <?php }
            $chkmenu = $obj->check_menuname("incentive_setting_report.php", $loginid);
            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
            ?>
                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "incentive_setting_report.php") ? "active" : ""; ?>" href="incentive_setting_report.php">

                        <i class="bi bi-cash-coin"></i> &nbsp; Incentive Setting

                        <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                    </a>

                </li>
            <?php }

            ?>
            <?php

            $chkmenu = $obj->check_menuname("scheme_entry.php", $loginid);

            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

            ?>
                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "scheme_entry.php") ? "active" : ""; ?>" href="scheme_entry.php">

                        <i class="bi bi-gift"></i> &nbsp; Scheme Entry

                        <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                    </a>

                </li>
            <?php }

            $chkmenu = $obj->checkmenu("Quotation Entry", $loginid);

            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

            ?>
                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "quotation.php"  || $pagename == "quotation_list.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#quotation" aria-expanded="true">
                        <i class="bi bi-file-earmark-text"></i>&nbsp; Quotation
                        <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                    </a>

                    <div class="collapse <?php echo ($pagename == "quotation.php"  || $pagename == "quotation_list.php") ? "show" : ""; ?>" id="quotation">

                        <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                            <?php

                            $chkmenu = $obj->check_menuname("quotation.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>
                                    <a href="quotation.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "quotation.php") ? "active" : ""; ?>">
                                        <i class="bi bi-chevron-right"></i> &nbsp; Create Quotation
                                    </a>
                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("quotation_list.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>

                                    <a href="quotation_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "quotation_list.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Quotation List
                                    </a>

                                </li>
                            <?php } ?>
                        </ul>

                    </div>

                </li>
            <?php }

            $chkmenu = $obj->checkmenu("Order Entry", $loginid);

            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

            ?>

                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "order_list.php"  || $pagename == "order-entry.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#neworder" aria-expanded="true">
                        <i class="bi bi-cart-check"></i></i>&nbsp; Order
                        <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                    </a>

                    <div class="collapse <?php echo ($pagename == "order_list.php"  || $pagename == "order-entry.php") ? "show" : ""; ?>" id="neworder">

                        <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                            <?php

                            $chkmenu = $obj->check_menuname("order-entry.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>
                                    <a href="order-entry.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "order-entry.php") ? "active" : ""; ?>">
                                        <i class="bi bi-chevron-right"></i> &nbsp; Create Order
                                    </a>
                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("order_list.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>

                                    <a href="order_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "order_list.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Order List & Dispatch
                                    </a>

                                </li>
                            <?php } ?>
                        </ul>

                    </div>

                </li>
            <?php }

            $chkmenu = $obj->checkmenu("Reports", $loginid);

            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

            ?>
                <li class="nav-item ">

                    <a class="nav-link <?php echo ($pagename == "daily_visit_list.php" || $pagename == "monthly_target_approval.php" || $pagename == "accounts_list.php" || $pagename == "payment_list.php" || $pagename == "salesman_wise_report.php") ? "active" : ""; ?> " href="#" data-bs-toggle="collapse" data-bs-target="#reports" aria-expanded="true">
                        <i class="bi bi-bar-chart-line"></i>&nbsp; Reports
                        <span class="float-end down"><i class="bi bi-chevron-right"></i></span>
                    </a>

                    <div class="collapse <?php echo ($pagename == "daily_visit_list.php" || $pagename == "monthly_target_approval.php" || $pagename == "accounts_list.php" || $pagename == "payment_list.php" || $pagename == "salesman_wise_report.php") ? "show" : ""; ?>" id="reports">

                        <ul class="btn-toggle-nav list-group list-unstyled fw-normal pb-1 small">
                            <?php

                            $chkmenu = $obj->check_menuname("accounts_list.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>
                                    <a href="accounts_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "accounts_list.php") ? "active" : ""; ?>">
                                        <i class="bi bi-chevron-right"></i> &nbsp; New Counter List
                                    </a>
                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("daily_visit_list.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>

                                    <a href="daily_visit_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "daily_visit_list.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Daily Visit's Entries
                                    </a>

                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("monthly_target_approval.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>

                                    <a href="monthly_target_approval.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "monthly_target_approval.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Monthly Target List
                                    </a>

                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("payment_list.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>
                                <li>

                                    <a href="payment_list.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "payment_list.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Payment List
                                    </a>

                                </li>
                            <?php }

                            $chkmenu = $obj->check_menuname("salesman_wise_report.php", $loginid);

                            if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                            ?>

                                <li>

                                    <a href="salesman_wise_report.php" class="list-group-item bg-submenu list-group-item-action <?php echo ($pagename == "salesman_wise_report.php") ? "active" : ""; ?>">

                                        <i class="bi bi-chevron-right"></i> &nbsp; Sales Man Wise Report
                                    </a>

                                </li>
                            <?php } ?>

                        </ul>

                    </div>

                </li>
                <?php

                $chkmenu = $obj->check_menuname("store_location.php", $loginid);

                if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {

                ?>
                    <li class="nav-item ">

                        <a class="nav-link <?php echo ($pagename == "store_location.php") ? "active" : ""; ?>" href="store_location.php">

                            <i class="bi bi-shop"></i> &nbsp; Store Location

                            <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                        </a>

                    </li>
                <?php }
                $chkmenu = $obj->check_menuname("qr-display.php", $loginid);
                if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                ?>
                    <li class="nav-item ">

                        <a class="nav-link <?php echo ($pagename == "qr-display.php") ? "active" : ""; ?>" href="qr-display.php">

                            <i class="bi bi-qr-code-scan"></i> &nbsp; QR Display

                            <span class="float-end"><i class="bi bi-chevron-right"></i></span>

                        </a>

                    </li>
                <?php }
                $chkmenu = $obj->check_menuname("payment.php", $loginid);
                if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                ?>
                    <li class="nav-item ">
                        <a class="nav-link <?php echo ($pagename == "payment.php") ? "active" : ""; ?>" href="payment.php">
                            <i class="bi bi-paypal"></i> &nbsp; Payment
                            <span class="float-end"><i class="bi bi-chevron-right"></i></span>
                        </a>
                    </li>
                <?php }
                $chkmenu = $obj->check_menuname("customer_ledger.php", $loginid);
                if ($chkmenu > 0 || $_SESSION['usertype'] == 'admin') {
                ?>
                    <li class="nav-item ">
                        <a class="nav-link <?php echo ($pagename == "customer_ledger.php") ? "active" : ""; ?>" href="customer_ledger.php">
                            <i class="bi bi-file-spreadsheet"></i> &nbsp; Customer Ledger
                            <span class="float-end"><i class="bi bi-chevron-right"></i></span>
                        </a>
                    </li>

            <?php }
            } ?>

            <li class="nav-item ">
                <a class="nav-link <?php echo ($pagename == "month-wise-details.php") ? "active" : ""; ?>" href="month-wise-details.php">
                    <i class="bi bi-calendar3"></i> &nbsp; Month Wise Details
                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>
                </a>
            </li>

            <li class="nav-item ">
                <a class="nav-link <?php echo ($pagename == "change-password.php") ? "active" : ""; ?>" href="change-password.php">
                    <i class="bi bi-shield-lock"></i> &nbsp; Change Password
                    <span class="float-end"><i class="bi bi-chevron-right"></i></span>
                </a>
            </li>
        </ul>
    </div>

</div>

<!-- modal -->
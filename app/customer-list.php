<?php include("appsession.php");
$title = "Customer List";
$crit = "1=1";
if (isset($_GET["route_planid"]) && $_GET["route_planid"] != '') {
    $route_planid = $obj->test_input($_GET["route_planid"]);
    if ($route_planid > 0) {
        $crit .= " and rc.batch_no = '$route_planid'";
    }
} else {
    $route_planid = '';
}



$avatarColors = ['#3a55e8', '#7c3aed', '#059669', '#dc2626', '#d97706', '#0891b2', '#be185d', '#16a34a'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <?php include("inc/css-file.php"); ?>

    <style>
        /* ── KB Design Tokens ───────────────────────── */
        :root {
            --kb-bg: #f7f7f8;
            --kb-surface: #ffffff;
            --kb-border: #e8e8ea;
            --kb-text: #1c1c1f;
            --kb-muted: #8a8a92;
            --kb-accent: #3a55e8;
            --kb-accent-soft: #eef1ff;
            --kb-success-soft: #dcfce7;
        }

        body.dashboard {
            background: var(--kb-bg);
        }

        /* ── Filter ─────────────────────────────────── */
        .kb-filter {
            gap: 8px;
            margin: 6px 0 22px;
        }

        .kb-filter-field {
            flex: 1;
            position: relative;
        }

        .kb-filter-field>label {
            position: absolute;
            top: -8px;
            left: 10px;
            z-index: 2;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--kb-muted);
            background: var(--kb-bg);
            padding: 0 4px;
        }

        .kb-filter-field .chosen-container-single .chosen-single {
            height: auto;
            line-height: normal;
            border: 1px solid var(--kb-border);
            border-radius: 10px;
            box-shadow: none;
            background: var(--kb-surface);
            padding: 13px 28px 9px 12px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--kb-text);
        }


        .kb-filter-field .chosen-container .chosen-results li.highlighted {
            background: var(--kb-accent);
            color: #fff;
        }

        .kb-search-btn {
            width: 48px;
            min-width: 48px;
            border: none !important;
            border-radius: 10px !important;
            box-shadow: none !important;
            background: var(--kb-accent) !important;
            color: #fff !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px -4px rgba(58, 85, 232, .5) !important;
        }

        /* ── Section label ──────────────────────────── */
        .kb-section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--kb-muted);
            margin-bottom: 14px;
            padding: 0 2px;
        }

        /* ── Timeline ───────────────────────────────── */
        .kb-timeline {
            position: relative;
            padding-left: 46px;
        }

        .kb-timeline::before {
            content: "";
            position: absolute;
            left: 19px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--kb-accent) 0%, var(--kb-border) 100%);
            border-radius: 2px;
        }

        .kb-tl-item {
            position: relative;
            margin-bottom: 14px;
        }

        .kb-tl-avatar {
            position: absolute;
            left: -46px;
            top: 14px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            border: 2px solid var(--kb-bg);
            box-shadow: 0 0 0 2px var(--kb-accent);
        }

        .kb-tl-card {
            background: var(--kb-surface);
            border: 1px solid var(--kb-border);
            border-radius: 14px;
            padding: 12px 14px;
            position: relative;
        }

        .kb-tl-card::before {
            content: "";
            position: absolute;
            left: -7px;
            top: 18px;
            width: 12px;
            height: 12px;
            background: var(--kb-surface);
            border-left: 1px solid var(--kb-border);
            border-bottom: 1px solid var(--kb-border);
            transform: rotate(45deg);
            border-radius: 0 0 0 3px;
        }

        .kb-tl-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }

        .kb-tl-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--kb-text);
            line-height: 1.3;
        }

        .kb-tl-type {
            font-size: 11.5px;
            color: var(--kb-muted);
            margin-top: 2px;
        }

        .kb-badge {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .04em;
            padding: 3px 8px;
            border-radius: 20px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .kb-badge-active {
            background: var(--kb-success-soft);
            color: #15803d;
        }

        .kb-badge-inactive {
            background: #f1f1f3;
            color: var(--kb-muted);
        }

        .kb-tl-divider {
            height: 1px;
            background: var(--kb-border);
            margin: 10px 0;
        }

        .kb-tl-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .kb-tl-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--kb-text);
        }

        .kb-tl-row svg {
            flex-shrink: 0;
            color: var(--kb-muted);
        }

        .kb-tl-row .kb-val {
            flex: 1;
            min-width: 0;
        }

        .kb-cls {
            font-size: 11px;
            font-weight: 700;
            color: var(--kb-muted);
        }

        .kb-tl-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
        }

        .kb-last-visit {
            font-size: 11px;
            color: var(--kb-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .kb-gps {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            color: var(--kb-accent);
            background: var(--kb-accent-soft);
            padding: 3px 8px;
            border-radius: 20px;
        }

        /* Action buttons */
        .kb-tl-actions {
            display: flex;
            gap: 6px;
            margin-top: 10px;
        }

        .kb-act-btn {
            flex: 1;
            height: 32px;
            border: 1px solid var(--kb-border);
            border-radius: 8px;
            background: var(--kb-bg);
            font-size: 12px;
            font-weight: 600;
            color: var(--kb-text);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .kb-act-btn-primary {
            background: var(--kb-accent);
            border-color: var(--kb-accent);
            color: #fff;
        }

        .kb-act-btn-wa {
            background: #25d366;
            border-color: #25d366;
            color: #fff;
        }

        /* ── FAB ────────────────────────────────────── */
        .kb-fab {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 52px;
            height: 52px;
            border-radius: 50% !important;
            background: var(--kb-accent) !important;
            color: #fff !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px -6px rgba(58, 85, 232, .6) !important;
            border: none !important;
            z-index: 20;
        }

        /* SVG icon shorthands used inline */
    </style>
</head>

<body class="dashboard">
    <section class="top-sec">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="row">

                <!-- Route Filter -->
                <form>
                    <div class="col-12">
                        <div class="kb-filter">
                            <div class="kb-filter-field">
                                <label for="route_planid">Route <span class="text-danger">*</span></label>
                                <select name="route_planid" id="route_planid" class="chosen-select form-control form-control-sm" onchange="set_customer(this.value);">
                                    <option value="">All Route's</option>
                                    <?php
                                    $routeList = $obj->executequery("SELECT R.batch_no, R.route_name,GROUP_CONCAT(R.day_of_week ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')
                                            SEPARATOR ', ') AS days
                                    FROM route R
                                    LEFT JOIN route_plan RP ON R.batch_no = RP.batch_no
                                    WHERE R.companyid = '$companyid' AND RP.sales_executive_id = '$loginid'
                                    GROUP BY R.batch_no, R.route_name
                                    ORDER BY R.route_name ASC
                                ");
                                    foreach ($routeList as $k): ?>
                                        <option value="<?= $k['batch_no'] ?>">
                                            <?= htmlspecialchars($k['route_name']) ?> [<?= htmlspecialchars($k['days']) ?>]
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <script>
                                    document.getElementById('route_planid').value = '<?= $route_planid ?>';
                                </script>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Customer Timeline -->
                <div class="col-12 mb-4">
                    <?php
                    $res = $obj->executequery("
    SELECT a.*, cm.common_name, am.area_name,
           de.last_visit_date
    FROM account a
    INNER JOIN route_counter rc ON rc.account_id = a.account_id
    LEFT JOIN (
        SELECT account_id, MAX(createdate) AS last_visit_date
        FROM daily_entries
        GROUP BY account_id
    ) de ON de.account_id = a.account_id
    LEFT JOIN common_master cm ON cm.common_id = a.common_id
    LEFT JOIN area_master am ON am.area_id = a.area_id
    WHERE $crit
    ORDER BY a.account_name ASC
");

                    if ($res && count($res) > 0):
                        $total = count($res);
                    ?>
                        <div class="kb-section-label"><?= $total ?> stop<?= $total == 1 ? '' : 's' ?> on this route.
                         <div class="btn-group float-end">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Actions
                                </button>

                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-whatsapp text-success"></i> Ledger Balance</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-whatsapp text-success"></i> Visiting Msg</a></li>
                                </ul>
                            </div>
                        </div>



                        <div class="kb-timeline">
                            <?php $sno = 1;
                            foreach ($res as $i => $key):
                                $color   = $avatarColors[$i % count($avatarColors)];
                                $isActive = (strtolower($key['status'] ?? 'active') === 'active');
                                $mobile   = htmlspecialchars($key['mobile_no'] ?? '');
                                $whatsapp = htmlspecialchars($key['whatsapp_no'] ?? $key['mobile_no'] ?? '');
                                $address  = htmlspecialchars($key['address'] ?? '');
                                $gps      = $key['location_address'] ?? '';
                                $lastVisit = !empty($key['last_visit_date'])
                                    ? date('d-M-Y', strtotime($key['last_visit_date']))
                                    : '';
                            ?>
                                <div class="kb-tl-item">
                                    <!-- Coloured initial avatar -->
                                    <div class="kb-tl-avatar" style="background:<?= $color ?>;"><?= $sno++ ?></div>

                                    <div class="kb-tl-card">
                                        <!-- Name + status -->
                                        <div class="kb-tl-top">
                                            <div>
                                                <div class="kb-tl-name"><?= htmlspecialchars($key['account_name']) ?></div>
                                                <div class="kb-tl-type">
                                                    <?= htmlspecialchars($key['common_name'] ?? '') ?>
                                                    <?php if (!empty($key['area_name'])): ?> · <?= htmlspecialchars($key['area_name']) ?><?php endif; ?>
                                                </div>
                                            </div>
                                            <span class="kb-badge <?= $isActive ? 'kb-badge-active' : 'kb-badge-inactive' ?>">
                                                <?= $isActive ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </div>

                                        <div class="kb-tl-divider"></div>

                                        <!-- Info rows -->
                                        <div class="kb-tl-info">
                                            <!-- Mobile + Class on same row -->
                                            <div class="kb-tl-row">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82A16 16 0 0 0 15 15.91l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                                                </svg>
                                                <span class="kb-val"><?= $mobile ?: '—' ?></span>
                                                <?php if (!empty($key['class'])): ?>
                                                    <span class="kb-cls">Class <?= htmlspecialchars($key['class']) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Address -->
                                            <?php if ($address): ?>
                                                <div class="kb-tl-row">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                                        <circle cx="12" cy="10" r="3" />
                                                    </svg>
                                                    <span class="kb-val"><?= $address ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Footer: last visit + GPS pill -->
                                        <div class="kb-tl-footer">
                                            <span class="kb-last-visit">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                                    <line x1="16" y1="2" x2="16" y2="6" />
                                                    <line x1="8" y1="2" x2="8" y2="6" />
                                                    <line x1="3" y1="10" x2="21" y2="10" />
                                                </svg>
                                                <?= $lastVisit ? 'Last Visit ' . $lastVisit : 'No visit recorded' ?>
                                            </span>
                                            <?php if ($gps): ?>
                                                <span class="kb-gps">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                                        <circle cx="12" cy="10" r="3" />
                                                    </svg>
                                                    GPS Saved
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Action buttons -->
                                        <!-- <div class="kb-tl-actions">
                                            <a href="tel:<?= preg_replace('/\D/', '', $mobile) ?>" class="kb-act-btn">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82A16 16 0 0 0 15 15.91l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                                                </svg>
                                                Call
                                            </a>
                                            <a href="https://wa.me/91<?= preg_replace('/\D/', '', $whatsapp) ?>" target="_blank" class="kb-act-btn kb-act-btn-wa">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.135.562 4.13 1.54 5.862L.057 23.857a.5.5 0 0 0 .612.612l6.046-1.48A11.934 11.934 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.9a9.844 9.844 0 0 1-5.031-1.378l-.36-.214-3.733.914.944-3.638-.235-.374A9.855 9.855 0 0 1 2.1 12c0-5.464 4.436-9.9 9.9-9.9 5.464 0 9.9 4.436 9.9 9.9 0 5.464-4.436 9.9-9.9 9.9z" />
                                                </svg>
                                                WhatsApp
                                            </a>
                                            <a href="view-counter.php?id=<?= urlencode($key['account_id']) ?>" class="kb-act-btn kb-act-btn-primary">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                                View
                                            </a>
                                        </div> -->
                                    </div><!-- /.kb-tl-card -->
                                </div><!-- /.kb-tl-item -->
                            <?php endforeach; ?>
                        </div><!-- /.kb-timeline -->

                    <?php endif; ?>
                </div>

            </div><!-- /.row -->
        </div><!-- /.container -->

        <!-- FAB Add Customer -->
        <a href="create-counter.php" class="kb-fab" aria-label="Add customer">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
        </a>

    </section>

    <?php include("inc/js-file.php"); ?>
    <script>
        $(document).ready(function() {
            $(".chosen-select").chosen({
                width: "100%",
                search_contains: true
            });
        });

        function set_customer(route_planid) {
            if (route_planid > 0) {
                location = "?route_planid=" + route_planid;
            } else {
                location = "customer-list.php";
            }

        }
    </script>
</body>

</html>
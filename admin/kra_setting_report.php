<?php include("../adminsession.php");
$title = "KRA SETTING";
$pagename = "kra_setting_report.php";
$companyid = $_SESSION['companyid'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        .kra-wrapper {
            padding: 20px;
            background: #f4f6f9;
            min-height: 100vh;
        }

        /* ── Page title ── */
        .kra-page-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a3f5c;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kra-page-title::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 18px;
            background: #1a6ca8;
            border-radius: 2px;
        }

        /* ── Grid ── */
        .kra-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .kra-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .kra-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Card ── */
        .kra-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #dde3ea;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        }

        .kra-card-head {
            background: #1a6ca8;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kra-card-head span {
            color: #fff;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .btn-add-slab {
            background: #2ecc71;
            border: none;
            color: #fff;
            font-size: 1rem;
            line-height: 1;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
            flex-shrink: 0;
        }

        .btn-add-slab:hover {
            background: #27ae60;
        }

        .kra-card-body {
            padding: 12px;
        }

        /* ── Column header ── */
        .slab-col-head {
            display: grid;
            grid-template-columns: 1fr 10px 1fr 72px 28px;
            gap: 6px;
            padding: 0 4px 6px;
        }

        .slab-col-head span {
            font-size: 0.68rem;
            font-weight: 600;
            color: #8a9ab0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: center;
        }

        .slab-col-head span:first-child {
            text-align: left;
            padding-left: 2px;
        }

        /* ── Slab row ── */
        .slab-row {
            display: grid;
            grid-template-columns: 1fr 10px 1fr 72px 28px;
            gap: 6px;
            align-items: center;
            margin-bottom: 6px;
        }

        .slab-row:last-child {
            margin-bottom: 0;
        }

        .slab-input {
            width: 100%;
            border: 1px solid #dde3ea;
            border-radius: 6px;
            padding: 5px 7px;
            font-size: 0.82rem;
            color: #2c3e50;
            background: #f8fafc;
            outline: none;
            text-align: center;
            transition: border-color .15s, background .15s;
        }

        .slab-input:focus {
            border-color: #1a6ca8;
            background: #fff;
        }

        .slab-input.ok {
            border-color: #2ecc71;
            background: #f0fdf4;
        }

        .slab-input.err {
            border-color: #e74c3c;
            background: #fff5f5;
        }

        /* hide spinners */
        .slab-input[type="number"]::-webkit-inner-spin-button,
        .slab-input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }

        .slab-input[type="number"] {
            -moz-appearance: textfield;
        }

        .slab-sep {
            text-align: center;
            color: #8a9ab0;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .pts-input {
            background: #eaf2fb;
            border-color: #b8d4ed;
            color: #1a6ca8;
            font-weight: 600;
        }

        .pts-input:focus {
            border-color: #1a6ca8;
            background: #fff;
        }

        .btn-del {
            width: 28px;
            height: 28px;
            border: 1px solid #fbd5d5;
            background: #fff5f5;
            border-radius: 6px;
            color: #e74c3c;
            cursor: pointer;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, border-color .15s;
            flex-shrink: 0;
        }

        .btn-del:hover {
            background: #fee2e2;
            border-color: #e74c3c;
        }

        /* ── Empty state ── */
        .slab-empty {
            text-align: center;
            padding: 18px 0 8px;
            color: #b0bec5;
            font-size: 0.78rem;
        }

        /* ── Behaviour card table ── */
        .beh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.83rem;
        }

        .beh-table th {
            background: #eaf2fb;
            color: #1a6ca8;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 7px 12px;
            border-bottom: 2px solid #b8d4ed;
            text-align: left;
        }

        .beh-table th:last-child {
            text-align: center;
            width: 80px;
        }

        .beh-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eef2f6;
            color: #34495e;
        }

        .beh-table tr:last-child td {
            border-bottom: none;
        }

        .beh-table tr:hover td {
            background: #f8fafc;
        }

        .beh-table td:last-child {
            text-align: center;
        }

        .beh-score {
            width: 60px;
            border: 1px solid #dde3ea;
            border-radius: 6px;
            padding: 4px 6px;
            font-size: 0.82rem;
            text-align: center;
            color: #1a6ca8;
            font-weight: 600;
            background: #eaf2fb;
            outline: none;
            margin: 0 auto;
            display: block;
            transition: border-color .15s;
        }

        .beh-score:focus {
            border-color: #1a6ca8;
            background: #fff;
        }

        .beh-score::-webkit-inner-spin-button,
        .beh-score::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }

        .beh-score {
            -moz-appearance: textfield;
        }

        /* ── Toast ── */
        #kra-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #1a6ca8;
            color: #fff;
            box-shadow: 0 4px 16px rgba(26, 108, 168, .3);
            z-index: 9999;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .2s, transform .2s;
            pointer-events: none;
        }

        #kra-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        #kra-toast.err {
            background: #e74c3c;
            box-shadow: 0 4px 16px rgba(231, 76, 60, .3);
        }
    </style>
</head>

<body class="bg-light">

    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>

        <div class="kra-wrapper">
            <div class="kra-page-title">KRA Settings</div>

            <div class="kra-grid">

                <?php
                $kras = [
                    'visit'        => 'Average Counter Visit / Day / Beat',
                    'productivity' => 'Beat Wise Productivity (%)',
                    'product_mix'  => 'Product Mix',
                    'business'     => 'Overall Business (Lakh)',
                ];
                foreach ($kras as $key => $label):
                ?>
                    <div class="kra-card">
                        <div class="kra-card-head">
                            <span><?= $label ?></span>
                            <!-- <button type="button" class="btn-add-slab add-row" data-kra="<?= $key ?>" title="Add slab">+</button> -->
                        </div>
                        <div class="kra-card-body">

                            <?php
                            $rows = $obj->executequery("SELECT * FROM kra_config WHERE kra_key='$key' ORDER BY min_value ASC");
                            if (!empty($rows)): ?>
                                <div class="slab-col-head">
                                    <span>From</span><span></span><span>To</span><span>Points</span><span></span>
                                </div>
                                <?php foreach ($rows as $row): ?>
                                    <div class="slab-row">
                                        <input type="number" class="slab-input range-input"
                                            value="<?= $row['min_value'] ?>"
                                            data-id="<?= $row['kra_config_id'] ?>" data-type="min" placeholder="0">
                                        <span class="slab-sep">–</span>
                                        <input type="number" class="slab-input range-input"
                                            value="<?= $row['max_value'] ?>" placeholder="∞"
                                            data-id="<?= $row['kra_config_id'] ?>" data-type="max" placeholder="∞">
                                        <input type="text" class="slab-input pts-input point-input"
                                            value="<?= $row['points'] ?>"
                                            data-id="<?= $row['kra_config_id'] ?>" placeholder="pts">
                                        <button class="btn-del delete-row" data-id="<?= $row['kra_config_id'] ?>" title="Remove">✕</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="slab-empty">No slabs — click + to add</div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Behaviour card -->
                <div class="kra-card">
                    <div class="kra-card-head">
                        <span>Behaviour Score <span style="opacity:.75;font-weight:400;">(Max 4)</span></span>
                    </div>
                    <div class="kra-card-body" style="padding:0;">
                        <table class="beh-table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $brows = $obj->executequery("SELECT * FROM kra_behaviour WHERE companyid='$companyid'");
                                foreach ($brows as $row): ?>
                                    <tr>
                                        <td><?= $row['name'] ?></td>
                                        <td>
                                            <input type="number" class="beh-score behaviour-input"
                                                value="<?= $row['max_score'] ?>"
                                                data-id="<?= $row['kra_behaviour_id'] ?>"
                                                min="0" max="1" step="0.5">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /kra-grid -->
        </div><!-- /kra-wrapper -->
    </div><!-- /main -->

    <div id="kra-toast"></div>

    <?php include('component/script.php'); ?>
    <script>
        function toast(msg, isErr) {
            const t = document.getElementById('kra-toast');
            t.textContent = msg;
            t.className = 'show' + (isErr ? ' err' : '');
            clearTimeout(t._t);
            t._t = setTimeout(() => t.className = '', 2200);
        }

        $(document).on('change', '.range-input', function() {
            let el = $(this);
            let val = el.val();
            let type = el.data('type');

            if (val === '') val = null;

            let row = el.closest('.slab-row');
            let nextRow = row.next('.slab-row');
            if (type === 'max' && nextRow.length && val !== null) {
                let nextMin = nextRow.find('[data-type="min"]');
                let nextVal = parseFloat(val) + 1;

                nextMin.val(nextVal);

                $.post('update_kra_range.php', {
                    id: nextMin.data('id'),
                    field: 'min',
                    value: nextVal
                });
            }

            $.post('update_kra_range.php', {
                id: el.data('id'),
                field: type,
                value: val
            }, function(res) {
                if (res === 'overlap') {
                    el.addClass('err');
                    toast('Range overlap not allowed', true);
                    setTimeout(() => el.removeClass('err'), 800);
                } else if (res === 'infinity_exists') {
                    el.addClass('err');
                    toast('Only one ∞ slab allowed', true);
                    setTimeout(() => el.removeClass('err'), 800);
                } else {
                    el.removeClass('err').addClass('ok');
                    toast('Saved');
                    setTimeout(() => el.removeClass('ok'), 500);
                }
            });
        });

        $(document).on('change', '.point-input', function() {
            const el = $(this);
            $.post('update_kra_range.php', {
                id: el.data('id'),
                field: 'points',
                value: el.val()
            }, function() {
                el.addClass('ok');
                toast('Points saved');
                setTimeout(() => el.removeClass('ok'), 700);
            });
        });

        $(document).on('change', '.behaviour-input', function() {
            $.post('update_behaviour.php', {
                id: $(this).data('id'),
                score: $(this).val()
            }, function() {
                toast('Score saved');
            });
        });

        $(document).on('click', '.delete-row', function() {

            if (!confirm('Delete this slab?')) return;

            let id = $(this).data('id');

            $.post('delete_kra_slab.php', {
                id: id
            }, function(res) {

                if (res === 'last_row') {
                    toast('At least one slab required', true);
                    return;
                }

                toast('Slab deleted');
                location.reload();
            });

        });
    </script>
</body>

</html>
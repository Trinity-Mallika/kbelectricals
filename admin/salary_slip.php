<?php
include("../adminsession.php");

require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

$salary_id = intval($_GET['salary_id'] ?? 0);

if ($salary_id <= 0) {
  echo "Invalid salary ID.";
  exit;
}

// Fetch salary record
$salary = $obj->executequery("
  SELECT es.*, m.member_name, m.designation, m.mobile, m.email
  FROM emp_salary es
  LEFT JOIN bni_members m ON m.member_id = es.member_id
  WHERE es.salary_id = '$salary_id'
");

if (empty($salary)) {
  echo "Salary record not found.";
  exit;
}

$s = $salary[0];

// Company info
$company = $obj->executequery("SELECT * FROM company_setting LIMIT 1");
$company = !empty($company) ? $company[0] : [];

$companyName    = 'Machine Point';
$companyMobile  = $company['mobile'] ?? '';
$companyAddress = $company['address'] ?? '';
$companyEmail   = $company['email'] ?? '';
$companyLogo    = !empty($company['logo']) ? 'uploaded/' . $company['logo'] : '';

$monthName = date('F', strtotime($s['year'] . '-' . $s['month'] . '-01'));
$yearName  = $s['year'];
$period    = $monthName . ' ' . $yearName;

$logoHtml = '';
if (!empty($companyLogo) && file_exists(__DIR__ . '/' . $companyLogo)) {
  $logoData = base64_encode(file_get_contents(__DIR__ . '/' . $companyLogo));
  $logoHtml = '<img src="data:image/png;base64,' . $logoData . '" style="height:50px;"> ';
}

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
  .slip { width: 100%; padding: 15px; }
  .header { text-align: center; border-bottom: 2px solid #06163a; padding-bottom: 8px; margin-bottom: 10px; }
  .header h2 { color: #06163a; font-size: 16px; margin: 2px 0; }
  .header p { color: #666; font-size: 9px; }
  .title { text-align: center; background: #06163a; color: #fff; padding: 5px; font-size: 13px; font-weight: bold; border-radius: 3px; margin-bottom: 10px; }
  .info-table { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
  .info-table td { padding: 2px 6px; font-size: 10px; border: 1px solid #ddd; }
  .info-table td:first-child { font-weight: bold; width: 100px; color: #555; background: #f5f5f5; }
  .section-title { background: #e8eef6; padding: 3px 8px; font-weight: bold; font-size: 10px; color: #06163a; border-left: 3px solid #06163a; margin: 8px 0 4px; }
  table.calc { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  table.calc th, table.calc td { border: 1px solid #ddd; padding: 3px 6px; font-size: 10px; }
  table.calc th { background: #f5f5f5; text-align: left; color: #555; }
  table.calc td { text-align: right; }
  .cols2 { display: flex; gap: 10px; }
  .cols2 > div { flex: 1; }
  .net-pay { background: #06163a; color: #fff; text-align: center; padding: 6px; font-size: 14px; font-weight: bold; border-radius: 3px; margin: 8px 0; }
  .footer { text-align: center; font-size: 8px; color: #999; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 5px; }
</style>
</head>
<body>
<div class="slip">

  <div class="header">
    ' . $logoHtml . '
    <h2>' . htmlspecialchars($companyName) . '</h2>
    <p>' . htmlspecialchars($companyAddress) . ' | ' . htmlspecialchars($companyMobile) . ' | ' . htmlspecialchars($companyEmail) . '</p>
  </div>

  <div class="title">SALARY SLIP — ' . $period . '</div>

  <table class="info-table">
    <tr>
      <td>Employee Name</td><td>' . htmlspecialchars($s['member_name']) . '</td>
      <td style="width:100px;">Month / Year</td><td>' . $period . '</td>
    </tr>
    <tr>
      <td>Designation</td><td>' . htmlspecialchars($s['designation'] ?? '—') . '</td>
      <td>Mobile</td><td>' . htmlspecialchars($s['mobile'] ?? '—') . '</td>
    </tr>
  </table>

  <div class="section-title">ATTENDANCE SUMMARY</div>
  <table class="calc">
    <tr><th class="label">Total Days</th><td>' . $s['total_days'] . '</td>
        <th class="label">Present</th><td>' . $s['present_days'] . '</td>
        <th class="label">Late</th><td>' . $s['late_days'] . '</td>
        <th class="label">Absent</th><td>' . $s['absent_days'] . '</td>
        <th class="label">Incomplete</th><td>' . $s['incomplete_days'] . '</td>
        <th class="label">Daily Rate</th><td>₹' . number_format($s['daily_rate'], 2) . '</td></tr>
  </table>

  <div class="cols2">
    <div>
      <div class="section-title">EARNINGS</div>
      <table class="calc">
        <tr><th class="label">Gross Salary</th><td>₹' . number_format($s['gross_salary'], 2) . '</td></tr>
        <tr><th class="label">TA</th><td>₹' . number_format($s['ta_amt'], 2) . '</td></tr>
        <tr><th class="label">DA</th><td>₹' . number_format($s['da_amt'], 2) . '</td></tr>
        <tr><th class="label">Bonus</th><td>₹' . number_format($s['bonus_amt'], 2) . '</td></tr>
        <tr><th class="label">Overtime</th><td>₹' . number_format($s['overtime_amt'], 2) . '</td></tr>
        <tr style="background:#e8ffe8;"><th class="label" style="font-weight:bold;">Total Earnings</th><td style="font-weight:bold;color:#16a34a;">₹' . number_format($s['total_earnings'], 2) . '</td></tr>
      </table>
    </div>
    <div>
      <div class="section-title">DEDUCTIONS</div>
      <table class="calc">
        <tr><th class="label">Fine</th><td>₹' . number_format($s['fine_amt'], 2) . '</td></tr>
        <tr><th class="label">PF</th><td>₹' . number_format($s['pf_amt'], 2) . '</td></tr>
        <tr><th class="label">ESIC</th><td>₹' . number_format($s['esic_amt'], 2) . '</td></tr>
        <tr><th class="label">Advance Paid</th><td>₹' . number_format($s['advance_paid'], 2) . '</td></tr>
        <tr style="background:#ffe8e8;"><th class="label" style="font-weight:bold;">Total Deductions</th><td style="font-weight:bold;color:#dc2626;">₹' . number_format($s['total_deductions'], 2) . '</td></tr>
      </table>
    </div>
  </div>

  <div class="net-pay">NET PAY: ₹' . number_format($s['net_pay'], 2) . '</div>

  <div class="footer">
    This is a computer-generated salary slip. Generated on ' . date('d-m-Y H:i A') . '
  </div>

</div>
</body>
</html>
';

$mpdf = new Mpdf(['format' => 'A5', 'orientation' => 'P', 'margin_left' => 8, 'margin_right' => 8, 'margin_top' => 8, 'margin_bottom' => 8]);
$mpdf->WriteHTML($html);
$mpdf->shrink_tables_to_fit = 1;
$mpdf->Output($s['member_name'] . ' Salary Slip ' . $period . '.pdf', 'I');

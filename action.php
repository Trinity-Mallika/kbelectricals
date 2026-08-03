<?php
require_once("config.php");

session_start();

class DataOperation extends Database
{
	private PDO $db;

	public function __construct()
	{
		parent::__construct();
		$this->db = $this->getConnection();
	}


	public function begin()
	{
		$this->db->beginTransaction();
	}

	public function commit()
	{
		$this->db->commit();
	}

	public function rollback()
	{
		$this->db->rollBack();
	}


	public function getRouteDashboardData(int $loginid, int $companyid)
	{
		$weekday = date('l');
		$day = date('j');
		$firstDay = date('Y-m-01');
		$firstWeekday = date('w', strtotime($firstDay));
		$week = ceil(($day + $firstWeekday) / 7);

		$total_weeks = (int) $this->getvalfield(
			"route_plan",
			"COUNT(DISTINCT week_number)",
			"sales_executive_id='$loginid'
         AND companyid='$companyid'
         AND week_number > 0"
		);

		$calendarWeeks = ceil(
			(date('t') + date('w', strtotime(date('Y-m-01')))) / 7
		);

		$weekOccurrences = [];

		for ($calendarWeek = 1; $calendarWeek <= $calendarWeeks; $calendarWeek++) {

			$routeWeek = ($total_weeks > 1)
				? (($calendarWeek - 1) % $total_weeks) + 1
				: 1;

			$weekOccurrences[$routeWeek] =
				($weekOccurrences[$routeWeek] ?? 0) + 1;
		}
		$effectiveWeek = ($total_weeks > 1)
			? (($week - 1) % $total_weeks) + 1
			: 1;

		$sql = "SELECT rp.route_planid, rp.batch_no
        FROM route_plan rp
        JOIN route r ON r.batch_no = rp.batch_no
        WHERE rp.sales_executive_id = :uid
          AND LOWER(r.day_of_week) = LOWER(:day)
          AND rp.companyid = :companyid
        ORDER BY
            CASE
                WHEN rp.week_number = :week THEN 1
                WHEN rp.week_number IS NULL OR rp.week_number = 0 THEN 2
                ELSE 3
            END
    ";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':uid' => $loginid,
			':day' => $weekday,
			':week' => $effectiveWeek,
			':companyid' => $companyid
		]);

		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$routePlanIds = [];
		$batchNos = [];

		foreach ($rows as $row) {
			$routePlanIds[] = $row['route_planid'];
			$batchNos[] = $row['batch_no'];
		}

		$routeplanid = !empty($routePlanIds) ? implode(',', $routePlanIds) : '0';
		$batchNosSql = !empty($batchNos) ? implode(',', array_map('intval', $batchNos)) : '0';

		$currenttotal = (int) $this->getvalfield(
			"route_counter",
			"COUNT(DISTINCT account_id)",
			"batch_no IN ($batchNosSql)
     AND is_active=1
     AND companyid='$companyid'"
		);

		$todayvisit = (int) $this->getvalfield("daily_entries", "COUNT(DISTINCT account_id)", "DATE(createdate)=CURDATE() AND createdby='$loginid' AND companyid='$companyid'");

		$monthvisit = (int) $this->getvalfield(
			"daily_entries",
			"COUNT(DISTINCT account_id)",
			"MONTH(createdate)=MONTH(CURDATE()) AND YEAR(createdate)=YEAR(CURDATE())   AND createdby='$loginid'
         AND companyid='$companyid'"
		);

		$Monthtotal = 0;

		$sqlMonthTarget = "SELECT
        rp.week_number,
        COUNT(DISTINCT rc.account_id) AS customer_count
    FROM route_counter rc
    INNER JOIN route_plan rp ON rp.batch_no = rc.batch_no
    WHERE rp.sales_executive_id = '$loginid'
      AND rc.is_active = 1
      AND rp.companyid = '$companyid'
      AND rc.companyid = '$companyid'
    GROUP BY rp.week_number
";


		$routeData = $this->executequery($sqlMonthTarget);

		foreach ($routeData as $row) {

			$weekNumber   = (int)$row['week_number'];
			$customerCount = (int)$row['customer_count'];

			$occurrence = $weekOccurrences[$weekNumber] ?? 0;

			$Monthtotal += ($customerCount * $occurrence);
		}

		$routeAccountsSql = "SELECT DISTINCT rc.account_id FROM route_counter rc WHERE rc.batch_no IN ($batchNosSql) AND rc.is_active = 1 AND rc.companyid = '$companyid'";

		$todaysales = (float) $this->getvalfield(
			"transaction_entry",
			"COALESCE(SUM(grand_total),0)",
			"account_id IN ($routeAccountsSql) AND type='order' AND is_approved='1'
         AND billdate >= CURDATE()
         AND billdate < CURDATE() + INTERVAL 1 DAY
         AND companyid='$companyid'"
		);

		$sql = "
SELECT
    COALESCE(SUM(te.grand_total),0) AS Monthsales
FROM transaction_entry te
INNER JOIN route_counter rc
    ON rc.account_id = te.account_id
   AND rc.is_active = 1
INNER JOIN route_plan rp
    ON rp.batch_no = rc.batch_no
   AND rp.sales_executive_id = '$loginid'
WHERE te.type = 'order'
  AND te.is_approved = 1
  AND te.billdate >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
  AND te.billdate < DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')";

		$row = $this->executequery($sql);
		$Monthsales = (float)($row[0]['Monthsales'] ?? 0);

		$openingBalance = (float)$this->getvalfield(
			"account",
			"COALESCE(SUM(opening_balance),0)",
			"account_id IN ($routeAccountsSql)"
		);

		$transactionBalance = (float)$this->getvalfield(
			"transaction_entry",
			"COALESCE(
        SUM(
            CASE
                WHEN type='order'
                     AND is_approved='1'
                     AND invoice_no!=''
                THEN invoice_amt

                WHEN type='payment'
                     AND pay_status='1'
                THEN -(grand_total + IFNULL(cash_disc,0))

                ELSE 0
            END
        ),0
    )",
			"account_id IN ($routeAccountsSql)
     AND companyid='$companyid'"
		);

		$routePendingAmount = $openingBalance + $transactionBalance;
		$today_percent = ($currenttotal > 0)
			? ($todayvisit / $currenttotal) * 100
			: 0;

		$month_percent = ($Monthtotal > 0)
			? ($monthvisit / $Monthtotal) * 100
			: 0;

		return [
			'route_plan_id' => $routeplanid,
			'batch_no' => $batchNosSql,
			'today_target' => $currenttotal,
			'today_visit' => $todayvisit,
			'month_target' => $Monthtotal,
			'month_visit' => $monthvisit,
			'today_percent' => round($today_percent, 2),
			'month_percent' => round($month_percent, 2),
			'todaysales' => round($todaysales, 2),
			'Monthsales' => round($Monthsales, 2),
			'pending_amount' => round($routePendingAmount, 2)
		];
	}


	public function processMonthlyKRA(int $emp_id, int $month, int $year, int $companyid)
	{
		$start = date("$year-$month-01");
		$end   = date("Y-m-t", strtotime($start));

		$total_visits = $this->getvalfield(
			"daily_productivity",
			"SUM(visit_count)",
			"emp_id='$emp_id' AND visit_count > 0 AND date BETWEEN '$start' AND '$end' AND companyid='$companyid'"
		) ?: 0;

		$days_worked = $this->getvalfield(
			"daily_productivity",
			"COUNT(*)",
			"emp_id='$emp_id' AND visit_count > 0 AND date BETWEEN '$start' AND '$end' AND companyid='$companyid'"
		) ?: 1;

		$visit_avg = round($total_visits / $days_worked, 2);


		/* ================= PRODUCTIVITY ================= */
		$total_counters = $this->getvalfield(
			"route_counter rc JOIN route_plan rp ON rp.batch_no = rc.batch_no",
			"COUNT(DISTINCT rc.account_id)",
			"rp.sales_executive_id='$emp_id'
     AND rp.companyid='$companyid'
     AND rc.is_active = 1"
		) ?: 0;

		$accounts = $this->executequery("
        SELECT a.account_id, a.class, SUM(t.grand_total) as sales
        FROM transaction_entry t
        JOIN account a ON a.account_id = t.account_id
        WHERE t.type='order'
        AND t.createdby='$emp_id'
        AND t.billdate BETWEEN '$start' AND '$end'
        AND t.is_approved=1
        AND t.companyid='$companyid'
        GROUP BY a.account_id
    ");

		$configRows = $this->executequery(
			"SELECT class, min_sales FROM kra_productivity_config WHERE companyid='$companyid'"
		);
		$classMinSales = [];
		foreach ($configRows as $c) {
			$classMinSales[strtoupper($c['class'])] = $c['min_sales'];
		}

		$active = 0;

		foreach ($accounts as $acc) {
			$class    = strtoupper($acc['class']);
			$sales    = $acc['sales'];
			$min      = $classMinSales[$class] ?? null;
			if ($min !== null && $sales >= $min) {
				$active++;
			}
		}

		$productivity = ($total_counters > 0)
			? round(($active / $total_counters) * 100, 2)
			: 0;


		/* ================= PRODUCT MIX ================= */

		$product_mix = $this->getvalfield("
    (SELECT COUNT(DISTINCT td.product_id) as mix
     FROM transaction_entry t
     JOIN transaction_details td ON td.transaction_id = t.transaction_id
     WHERE t.createdby='$emp_id'
     AND t.type='order'
     AND t.billdate BETWEEN '$start' AND '$end'
     AND t.is_approved=1
     AND t.companyid='$companyid'
     GROUP BY DATE(t.billdate)
    ) x
", "AVG(mix)", "1=1") ?: 0;


		/* ================= BUSINESS ================= */

		$business = $this->getvalfield(
			"transaction_entry",
			"SUM(grand_total)",
			"createdby='$emp_id'
         AND type='order'
         AND billdate BETWEEN '$start' AND '$end'
         AND is_approved=1
         AND companyid='$companyid'"
		) ?: 0;

		$business_lakh = $business / 100000;


		/* ================= BEHAVIOUR ================= */

		$behaviour = $this->getvalfield(
			"kra_behaviour_score",
			"SUM(score)",
			"emp_id='$emp_id'
         AND month='$month'
         AND year='$year'"
		) ?: 0;

		$behaviour = min($behaviour, 4);


		/* ================= POINTS ================= */

		$visit_pts    = $this->getKraPoints('visit', $visit_avg);
		$prod_pts     = $this->getKraPoints('productivity', $productivity);
		$mix_pts      = $this->getKraPoints('product_mix', $product_mix);
		$business_pts = $this->getKraPoints('business', $business_lakh);

		/* ================= SCORE ================= */

		$total =
			($visit_pts * 20) +
			($prod_pts * 20) +
			($mix_pts * 20) +
			($business_pts * 30) +
			($behaviour * 10);

		$achievement = ($total / 220) * 100;


		/* ================= SAVE ================= */

		$exists = $this->getvalfield(
			"monthly_kra",
			"COUNT(*)",
			"emp_id='$emp_id'
         AND month='$month'
         AND year='$year'
         AND companyid='$companyid'"
		);

		$arr = [
			"emp_id" => $emp_id,
			"month" => $month,
			"year" => $year,

			"visit_value" => $visit_avg,
			"productivity_value" => $productivity,
			"product_mix_value" => $product_mix,
			"business_value" => $business_lakh,
			"behaviour_value" => $behaviour,

			"visit_points" => $visit_pts,
			"productivity_points" => $prod_pts,
			"product_mix_points" => $mix_pts,
			"business_points" => $business_pts,
			"behaviour_points" => $behaviour,

			"total_score" => $total,
			"achievement_pct" => $achievement,
			"companyid" => $companyid,
			"createdby" => 1
		];

		if ($exists > 0) {
			$this->update_record(
				"monthly_kra",
				["emp_id" => $emp_id, "month" => $month, "year" => $year],
				$arr
			);
		} else {
			$arr["createdate"] = date("Y-m-d H:i:s");
			$this->insert_record("monthly_kra", $arr);
		}
	}


	public function getKraPoints($key, $value)
	{
		$value = (float)$value;

		return $this->getvalfield(
			"kra_config",
			"points",
			"kra_key='$key'
        AND min_value <= $value
        AND (max_value > $value OR max_value IS NULL)"
		) ?: 0;
	}
	public function processMonthlyIncentive(int $emp_id, int $month, int $year, int $companyid)
	{
		$start = date("$year-$month-01");
		$end   = date("Y-m-t", strtotime($start));

		$visitData = $this->executequery("
        SELECT visit_count as val
        FROM daily_productivity
        WHERE emp_id='$emp_id'
        AND date BETWEEN '$start' AND '$end'
        AND companyid='$companyid'
    ");

		$salesData = $this->executequery("
        SELECT SUM(grand_total)/100000 as val
        FROM transaction_entry
        WHERE createdby='$emp_id'
        AND type='order'
        AND is_approved=1
        AND billdate BETWEEN '$start' AND '$end'
        AND companyid='$companyid'
        GROUP BY DATE(billdate)
    ");

		$mixData = $this->executequery("
        SELECT COUNT(DISTINCT td.product_id) as val
        FROM transaction_entry t
        JOIN transaction_details td ON td.transaction_id = t.transaction_id
        WHERE t.createdby='$emp_id'
        AND t.type='order'
        AND t.is_approved=1
        AND t.billdate BETWEEN '$start' AND '$end'
        AND t.companyid='$companyid'
        GROUP BY DATE(t.billdate)
    ");

		$collectionData = $this->executequery("
    SELECT DATEDIFF(p.first_payment, o.billdate) as val
    FROM transaction_entry o
    JOIN (
        SELECT ref_bill_id, MIN(createdate) as first_payment
        FROM transaction_entry
        WHERE type='payment' and pay_status=1
        AND companyid='$companyid'
        GROUP BY ref_bill_id
    ) p ON p.ref_bill_id = o.transaction_id
    WHERE o.type='order'
    AND o.createdby='$emp_id'
    AND o.is_approved=1
    AND o.billdate BETWEEN '$start' AND '$end'
    AND o.companyid='$companyid'
");

		$visit_amt = $this->calculateIncentiveFlexible('visit', $visitData, $companyid);
		$sales_amt = $this->calculateIncentiveFlexible('sales', $salesData, $companyid);
		$mix_amt   = $this->calculateIncentiveFlexible('product_mix', $mixData, $companyid);
		$coll_amt  = $this->calculateIncentiveFlexible('collection', $collectionData, $companyid);

		$total = $visit_amt + $sales_amt + $mix_amt + $coll_amt;

		if ($total == 0) return;

		$avg = function ($data) {
			return count($data) ? array_sum(array_column($data, 'val')) / count($data) : 0;
		};

		$arr = [
			"sales_executive_id" => $emp_id,
			"month_name" => $month,
			"year" => $year,

			"avg_visits" => $avg($visitData),
			"avg_sales_amount" => array_sum(array_column($salesData, 'val')) * 100000,
			"product_mix_count" => $avg($mixData),
			"avg_collection_days" => $avg($collectionData),

			"visit_incentive" => $visit_amt,
			"sales_incentive" => $sales_amt,
			"product_mix_incentive" => $mix_amt,
			"collection_incentive" => $coll_amt,
			"total_incentive" => $total,
			"companyid" => $companyid
		];

		$exists = $this->getvalfield(
			"monthly_incentive",
			"COUNT(*)",
			"sales_executive_id='$emp_id'
         AND month_name='$month'
         AND year='$year'
         AND companyid='$companyid'"
		);

		if ($exists > 0) {
			$this->update_record(
				"monthly_incentive",
				[
					"sales_executive_id" => $emp_id,
					"month_name" => $month,
					"year" => $year
				],
				$arr
			);
		} else {
			$arr["createdate"] = date("Y-m-d H:i:s");
			$this->insert_record("monthly_incentive", $arr);
		}
	}


	public function calculateIncentiveFlexible($type, $data, $companyid)  // ← added $companyid
	{
		if (empty($data)) return 0;

		$values = array_column($data, 'val');

		$min_val = $this->getvalfield(
			"incentive_slabs",
			"MIN(min_value)",
			"type='$type' AND amount > 0 AND company_id='$companyid'"  // ← also add here
		);

		$allQualified = true;
		foreach ($values as $v) {
			if ($v < $min_val) {
				$allQualified = false;
				break;
			}
		}

		if ($allQualified) {
			$avg = array_sum($values) / count($values);
			return $this->getIncentive($type, $avg, $companyid) * count($values);
		}

		$qualified = [];
		$non = [];

		foreach ($values as $v) {
			if ($v >= $min_val) {
				$qualified[] = $v;
			} else {
				$non[] = $v;
			}
		}

		$avg_q = count($qualified) ? array_sum($qualified) / count($qualified) : 0;
		$avg_n = count($non)       ? array_sum($non)       / count($non)       : 0;

		return ($this->getIncentive($type, $avg_q, $companyid) * count($qualified))
			+ ($this->getIncentive($type, $avg_n, $companyid) * count($non));
	}

	public function getIncentive($type, $value, $companyid)
	{
		return $this->getvalfield(
			"incentive_slabs",
			"amount",
			"type='$type'
         AND company_id='$companyid'
         AND $value >= min_value
         AND ($value < max_value OR max_value IS NULL)
         ORDER BY min_value DESC"
		) ?: 0;
	}


	public function count_method(string $table, array $where): int
	{
		$conditions = [];
		$params = [];

		foreach ($where as $key => $val) {
			$conditions[] = "{$key} = :{$key}";
			$params[":{$key}"] = $val;
		}

		$sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode(" AND ", $conditions);

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return (int) $stmt->fetchColumn();
	}


	public function get_client_ip(): string
	{
		return $_SERVER['HTTP_X_FORWARDED_FOR']
			?? $_SERVER['REMOTE_ADDR']
			?? 'UNKNOWN';
	}


	public function selectMultiple(string $table, array $where = []): array
	{
		$conditions = [];
		foreach ($where as $key => $val) {
			$conditions[] = "{$key} = :{$key}";
		}

		$sql = "SELECT * FROM {$table}";
		if (!empty($conditions)) {
			$sql .= " WHERE " . implode(" AND ", $conditions);
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute($where);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


	public function login_method(string $table, string $username, string $password): array|false
	{
		$sql = "
        SELECT *
        FROM {$table}
        WHERE (mobile = :mobile OR username = :uname)
          AND password = :password
        LIMIT 1
    ";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			'mobile' => $username,
			'uname' => $username,
			'password' => $password
		]);

		return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
	}



	public function login_method_app(string $table, string $mobile, string $password): bool
	{
		$sql = "SELECT * FROM `$table`
            WHERE mobile = :mobile 
            AND password = :password 
            LIMIT 1";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			'mobile' => $mobile,
			'password' => $password
		]);

		$row = $stmt->fetch();

		if ($row) {
			$_SESSION['userid'] = $row['userid'];
			return true;
		}

		return false;
	}


	public function software_expire(): int
	{
		$currentdate = date('Y-m-d');

		$sql = "SELECT COUNT(*) 
            FROM software_expired
            WHERE :today BETWEEN start_date AND expired_date";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(['today' => $currentdate]);

		return (int) $stmt->fetchColumn();
	}
	public function uploadImage(string $uploadPath, array $file): string
	{
		if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
			return "";
		}

		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		$allowed = [
			'jpg',
			'jpeg',
			'png',
			'pdf',
			'doc',
			'docx',
			'xls',
			'xlsx',
			'csv'
		];

		if (!in_array($ext, $allowed)) {
			return "";
		}

		// Create upload directory if it doesn't exist
		if (!is_dir($uploadPath)) {
			mkdir($uploadPath, 0777, true);
		}

		// Generate unique filename
		$filename = 'DOC' . round(microtime(true) * 1000) . '.' . $ext;

		$target = rtrim($uploadPath, '/') . '/' . $filename;

		// Compress only images
		if (in_array($ext, ['jpg', 'jpeg', 'png'])) {

			if ($ext == 'jpg' || $ext == 'jpeg') {
				$image = imagecreatefromjpeg($file['tmp_name']);
			} else {
				$image = imagecreatefrompng($file['tmp_name']);
			}

			if ($image) {

				$width  = imagesx($image);
				$height = imagesy($image);

				// Resize only if image is larger than 1200px
				if ($width > 1200) {
					$new_width  = 1200;
					$new_height = intval(($height / $width) * $new_width);
				} else {
					$new_width  = $width;
					$new_height = $height;
				}

				$tmp = imagecreatetruecolor($new_width, $new_height);

				// Preserve transparency for PNG
				if ($ext == 'png') {
					imagealphablending($tmp, false);
					imagesavealpha($tmp, true);
				}

				imagecopyresampled(
					$tmp,
					$image,
					0,
					0,
					0,
					0,
					$new_width,
					$new_height,
					$width,
					$height
				);

				if ($ext == 'png') {
					imagepng($tmp, $target, 6);
				} else {
					imagejpeg($tmp, $target, 70);
				}

				imagedestroy($image);
				imagedestroy($tmp);
			}
		} else {
			// PDF, Excel, Word, CSV
			move_uploaded_file($file['tmp_name'], $target);
		}

		return $filename;
	}

	public function session_method(string $table, string $username, string $password): ?array
	{
		$sql = "SELECT * FROM $table 
            WHERE (mobile = :username OR username = :username)
            AND password = :password
            LIMIT 1";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			'username' => $username,
			'password' => $password
		]);

		$row = $stmt->fetch();

		return $row ?: null;
	}

	public function session_method_app(string $table, string $username, string $password): ?array
	{
		$sql = "SELECT * FROM $table 
            WHERE (mobile = :mobile)
            AND password = :password
            LIMIT 1";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			'mobile' => $username,
			'password' => $password
		]);

		$row = $stmt->fetch();

		return $row ?: null;
	}
	public function get_opening_ledger(int $account_id, string $from_date, string $todate): float
	{
		$opening_date = $this->getvalfield("account", "opening_date", "account_id='$account_id'");

		// if opening and from date are equal
		if ($from_date == $opening_date) {
			$crit = " and opening_date = '$from_date'";
			// if opening is less than from date
		} elseif ($opening_date < $from_date) {
			$crit = " and opening_date < '$from_date'";
			// if opening is greater than but less than todate
		} elseif ($opening_date > $from_date && $opening_date <= $todate) {
			$crit = " and opening_date between '$from_date' and '$todate'";
		} else {
			$crit = " and opening_date < '$from_date'";
		}

		$opening_amt = (float)$this->getvalfield("account", "opening_balance", "account_id='$account_id' $crit ");

		$opening_paid = (float)$this->getvalfield(
			"transaction_entry",
			"IFNULL(SUM(grand_total),0)",
			"account_id='$account_id'
         AND type='payment'
         AND pay_status=1
         AND pay_type='opening'
         AND billdate < '$from_date'"
		);

		$sql = "SELECT
 IFNULL(SUM(
        CASE
            WHEN type='order'
                 AND is_approved=1
                 AND invoice_no <> ''
            THEN invoice_amt
            ELSE 0
        END
    ),0) AS total_order,

            IFNULL(SUM(
                CASE
                    WHEN type='payment'
                         AND pay_status=1
                         AND pay_type='bill'
                    THEN grand_total
                    ELSE 0
                END
            ),0) AS total_payment,

            IFNULL(SUM(
                CASE
                    WHEN type='payment'
                         AND pay_status=1
                    THEN cash_disc
                    ELSE 0
                END
            ),0) AS total_cash_disc

        FROM transaction_entry
        WHERE account_id='$account_id'
          AND billdate < '$from_date'
    ";

		$row = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);

		$total_order     = (float)$row['total_order'];
		$total_payment   = (float)$row['total_payment'];
		$total_cash_disc = (float)$row['total_cash_disc'];

		$balance = ($opening_amt - $opening_paid)
			+ ($total_order - $total_payment - $total_cash_disc);

		return round($balance, 2);
	}

	public function get_ledger_balance(int $account_id): float
	{
		$opening_amt = (float)$this->getvalfield(
			"account",
			"opening_balance",
			"account_id='$account_id'"
		);

		$opening_paid = (float)$this->getvalfield(
			"transaction_entry",
			"IFNULL(SUM(grand_total),0)",
			"account_id='$account_id'
         AND type='payment' and pay_status=1
         AND pay_type='opening'"
		);

		$sql = "
        SELECT
            IFNULL(SUM(
                CASE
                    WHEN type='order'
                         AND is_approved=1
                         AND invoice_no <> ''
                    THEN invoice_amt

                    ELSE 0
                END
            ),0) AS total_order,

            IFNULL(SUM(
                CASE
                    WHEN type='payment' and pay_status=1
                         AND pay_type='bill'
                    THEN grand_total
                    ELSE 0
                END
            ),0) AS total_payment,

            IFNULL(SUM(
                CASE
                    WHEN type='payment' and pay_status=1
                    THEN cash_disc
                    ELSE 0
                END
            ),0) AS total_cash_disc

        FROM transaction_entry
        WHERE account_id='$account_id'
    ";

		$row = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);

		$total_order      = (float)$row['total_order'];
		$total_payment    = (float)$row['total_payment'];
		$total_cash_disc  = (float)$row['total_cash_disc'];

		$balance = ($opening_amt - $opening_paid)
			+ ($total_order - $total_payment - $total_cash_disc);

		return round($balance, 2);
	}

	public function get_max_overdue_days(int $account_id): int
	{
		// Invoice overdue
		$sql = "SELECT IFNULL(MAX(overdue_days),0) AS max_overdue
            FROM (
                SELECT
                    DATEDIFF(CURDATE(), t.billdate) AS overdue_days,
                    (
                        CASE
                            WHEN t.invoice_no <> '' THEN t.invoice_amt
                            ELSE t.grand_total
                        END
                        -
                        IFNULL((
                            SELECT SUM(p.grand_total + p.cash_disc)
                            FROM transaction_entry p
                            WHERE p.ref_bill_id = t.transaction_id
                              AND p.type='payment'
                              AND p.pay_status=1
                              AND p.pay_type='bill'
                        ),0)
                    ) AS pending_amt
                FROM transaction_entry t
                WHERE t.account_id='$account_id'
                  AND t.type='order'
                  AND t.is_approved=1
                  AND t.invoice_no IS NOT NULL
            ) x
            WHERE pending_amt > 0";

		$invoice_days = (int)$this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['max_overdue'];

		$opening_balance = (float)$this->getvalfield(
			"account",
			"opening_balance",
			"account_id='$account_id'"
		);

		$opening_paid = (float)$this->getvalfield(
			"transaction_entry",
			"IFNULL(SUM(grand_total + cash_disc),0)",
			"account_id='$account_id'
         AND type='payment'
         AND pay_status=1
         AND pay_type='opening'"
		);

		$opening_pending = $opening_balance - $opening_paid;

		$opening_days = 0;

		if ($opening_pending > 0) {
			$opening_date = $this->getvalfield(
				"account",
				"opening_date",
				"account_id='$account_id'"
			);

			if (!empty($opening_date)) {
				$opening_days = (int)((new DateTime())->diff(new DateTime($opening_date))->days);
			}
		}

		return max($invoice_days, $opening_days);
	}

	function recalculateTransaction(int $transaction_id)
	{
		global $obj;

		$transaction = $obj->select_record(
			"transaction_entry",
			["transaction_id" => $transaction_id]
		);

		if (!$transaction) {
			return false;
		}

		$is_gst            = (int)$transaction['is_gst'];
		$overall_gst_rate  = (float)$transaction['gst_percent'];
		$freight           = (float)$transaction['freight_charges'];

		$taxable_total = 0;
		$product_gst_total = 0;
		$net_total = 0;

		$gstRates = [];

		$res = $obj->executequery("SELECT gst_id, gst_percent FROM gst_master");

		foreach ($res as $g) {
			$gstRates[$g['gst_id']] = $g['gst_percent'];
		}

		$details = $obj->executequery("
        SELECT *
        FROM transaction_details
        WHERE transaction_id='$transaction_id'
        ORDER BY tran_detail_id
    ");

		foreach ($details as $row) {

			$tran_detail_id = $row['tran_detail_id'];

			$ordered_qty = (float)$row['qty'];

			$cancel_qty = (float)$obj->getvalfield(
				"cancel_history",
				"IFNULL(SUM(qty),0)",
				"tran_detail_id='$tran_detail_id'"
			);

			$bill_qty = $ordered_qty - $cancel_qty;

			if ($bill_qty < 0) {
				$bill_qty = 0;
			}

			$rate = (float)$row['rate'];
			$discount = (float)$row['discount'];

			$discount_per_unit = ($rate * $discount) / 100;

			$price_after_disc = $rate - $discount_per_unit;

			if ($price_after_disc < 0) {
				$price_after_disc = 0;
			}

			$discount_amt = $discount_per_unit * $bill_qty;

			$sub_total = $price_after_disc * $bill_qty;

			$total_amt = $sub_total;

			$gst_amt = 0;
			$net_amt = $total_amt;

			if ((int)$row['gst_id'] > 0) {
				$gst_percent = isset($gstRates[$row['gst_id']])
					? (float)$gstRates[$row['gst_id']]
					: 0;

				$gst_amt = ($total_amt * $gst_percent) / 100;

				$net_amt = $total_amt + $gst_amt;
			}

			$update = array(

				"price_after_disc" => round($price_after_disc, 2),

				"discount_amt" => round($discount_amt, 2),

				"sub_total" => round($sub_total, 2),

				"total_amt" => round($total_amt, 2),

				"gst_amt" => round($gst_amt, 2),

				"net_amt" => round($net_amt, 2)

			);

			$obj->update_record(
				"transaction_details",
				["tran_detail_id" => $tran_detail_id],
				$update
			);

			$taxable_total += $total_amt;

			$product_gst_total += $gst_amt;

			$net_total += $net_amt;
		}

		$cgst = 0;
		$sgst = 0;
		$overall_gst_amt = 0;

		// Overall GST
		if ($is_gst == 1) {

			$invoice_taxable = $taxable_total + $freight;

			$overall_gst_amt = ($invoice_taxable * $overall_gst_rate) / 100;

			$cgst = $overall_gst_amt / 2;
			$sgst = $overall_gst_amt / 2;

			$grand_total = $invoice_taxable + $overall_gst_amt;

			$net_total = $taxable_total;
		}

		// Product-wise GST
		elseif ($product_gst_total > 0) {

			$grand_total = $taxable_total + $product_gst_total + $freight;

			$cgst = $product_gst_total / 2;
			$sgst = $product_gst_total / 2;

			$net_total = $taxable_total + $product_gst_total;
		}

		// No GST
		else {

			$grand_total = $taxable_total + $freight;

			$net_total = $taxable_total;
		}

		$rounded_total = round($grand_total);

		$round_off = $rounded_total - $grand_total;

		$grand_total = $rounded_total;

		$update = array(

			"taxable_amount" => round($taxable_total, 2),

			"overall_gst_amt" => round($overall_gst_amt, 2),

			"cgst" => round($cgst, 2),

			"sgst" => round($sgst, 2),

			"net_total_amt" => round($net_total, 2),

			"round_off" => round($round_off, 2),

			"grand_total" => round($grand_total, 2)

		);

		$obj->update_record(
			"transaction_entry",
			array("transaction_id" => $transaction_id),
			$update
		);

		return true;
	}

	public function getcode(string $table, string $tablepkey, string $cond = "1=1"): string
	{
		$sql = "SELECT MAX($tablepkey) FROM $table WHERE $cond";
		$stmt = $this->db->query($sql);

		$num = (int) $stmt->fetchColumn();
		$num++;

		return str_pad((string) $num, 5, '0', STR_PAD_LEFT);
	}


	public function getquocode(string $table, string $tablepkey, string $prefix = 'KBE', string $cond = "1=1"): string
	{
		// Current month name
		$month = date('F'); // March

		// Current year-month for filtering (optional better control)
		$year_month = date('Y-m');

		// Get max number for current month
		$sql = "SELECT MAX(CAST(SUBSTRING_INDEX($tablepkey, '-', -1) AS UNSIGNED)) 
            FROM $table 
            WHERE $tablepkey LIKE '$prefix-$month-%'";

		$stmt = $this->db->query($sql);
		$num = (int) $stmt->fetchColumn();

		$num++; // increment

		// Final Code
		return $prefix . '-' . $month . '-' . $num;
	}

	public function executequery(string $sql): array
	{

		$stmt = $this->db->query($sql);
		return $stmt->fetchAll();
	}

	public function executequery_arr($sql)
	{

		$array = array();

		$query = mysqli_query($this->con, $sql);

		while ($row = mysqli_fetch_assoc($query)) {

			$array[] = $row;
		}

		return $array;
	}

	public function delete_record_with_files(
		string $table,
		array $where,
		array $fileFields = [],
		array $folders = []
	): bool {

		// पहले Record Fetch करें
		$record = $this->select_record($table, $where);

		if (!$record) {
			return false;
		}

		// Files Delete करें
		foreach ($fileFields as $index => $field) {

			if (!empty($record[$field])) {

				$path = rtrim($folders[$index], "/") . "/" . $record[$field];

				if (file_exists($path)) {
					unlink($path);
				}
			}
		}

		// Database Record Delete
		return $this->delete_record($table, $where);
	}


	public function getvalMultiple(string $table, string $field, string $where): array
	{
		$sql = "SELECT $field FROM $table WHERE $where";

		$stmt = $this->db->query($sql);
		$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

		return $rows ?: [];
	}


	public function mySimpleCrypt(string $string, string $action = 'e'): string
	{

		$secret_key = 'trinitysolutionsraipur';
		$secret_iv = 'my_simple_secret_iv';
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if ($action == 'e') {

			$output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
		} else if ($action == 'd') {

			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}


	function getIndianCurrency(float $number)
	{

		$decimal = round($number - ($no = floor($number)), 2) * 100;
		$hundred = null;
		$digits_length = strlen($no);
		$i = 0;
		$str = array();
		$words = array(
			0 => '',
			1 => 'one',
			2 => 'two',

			3 => 'three',
			4 => 'four',
			5 => 'five',
			6 => 'six',

			7 => 'seven',
			8 => 'eight',
			9 => 'nine',

			10 => 'ten',
			11 => 'eleven',
			12 => 'twelve',

			13 => 'thirteen',
			14 => 'fourteen',
			15 => 'fifteen',

			16 => 'sixteen',
			17 => 'seventeen',
			18 => 'eighteen',

			19 => 'nineteen',
			20 => 'twenty',
			30 => 'thirty',

			40 => 'forty',
			50 => 'fifty',
			60 => 'sixty',

			70 => 'seventy',
			80 => 'eighty',
			90 => 'ninety'

		);

		$digits = array('', 'hundred', 'thousand', 'lakh', 'crore');

		while ($i < $digits_length) {

			$divider = ($i == 2) ? 10 : 100;

			$number = floor($no % $divider);

			$no = floor($no / $divider);

			$i += $divider == 10 ? 1 : 2;

			if ($number) {

				$plural = (($counter = count($str)) && $number > 9) ? 's' : null;

				$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;

				$str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
			} else
				$str[] = null;
		}

		$Rupees = implode('', array_reverse($str));

		$paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';

		return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
	}



	function compressAndMoveImage($imagePath, $destinationDir, $quality = 75)
	{
		if (!file_exists($imagePath)) {
			return false;
		}

		$info = getimagesize($imagePath);
		if ($info === false) {
			return false;
		}

		$mime = $info['mime'];
		$extension = '';

		switch ($mime) {
			case 'image/jpeg':
				$extension = 'jpg';
				break;
			case 'image/gif':
				$extension = 'gif';
				break;
			case 'image/png':
				$extension = 'png';
				break;
			default:
				return false;
		}

		$newFileName = uniqid() . '.' . $extension;
		$destinationPath = rtrim($destinationDir, '/') . '/' . $newFileName;
		$image = null;
		$success = false;

		if ($mime == 'image/jpeg') {
			$image = imagecreatefromjpeg($imagePath);
			if ($image) {
				$success = imagejpeg($image, $destinationPath, $quality);
			}
		} elseif ($mime == 'image/gif') {
			$image = imagecreatefromgif($imagePath);
			if ($image) {
				$success = imagegif($image, $destinationPath);
			}
		} elseif ($mime == 'image/png') {
			$image = imagecreatefrompng($imagePath);
			if ($image) {
				$success = imagepng($image, $destinationPath, round($quality / 10));
			}
		}

		if ($image) {
			imagedestroy($image);
		}

		return $success ? $newFileName : false;
	}

	public function update_record(string $table, array $where, array $fields, int $print = 0): bool
	{
		$setParts = [];
		$params = [];

		foreach ($fields as $key => $value) {
			$setParts[] = "`$key` = :set_$key";
			$params["set_$key"] = $value;
		}

		$whereParts = [];
		foreach ($where as $key => $value) {
			$whereParts[] = "`$key` = :where_$key";
			$params["where_$key"] = $value;
		}

		$sql = "UPDATE `$table` SET " . implode(", ", $setParts)
			. " WHERE " . implode(" AND ", $whereParts);

		if ($print === 1) {
			echo $sql;
			print_r($params);
			die;
		}

		$stmt = $this->db->prepare($sql);
		return $stmt->execute($params);
	}


	public function select_record(string $table, array $where, int $print = 0): array|null
	{
		$whereParts = [];
		$params = [];

		foreach ($where as $key => $value) {
			$whereParts[] = "`$key` = :$key";
			$params[$key] = $value;
		}

		$sql = "SELECT * FROM `$table` WHERE " . implode(" AND ", $whereParts) . " LIMIT 1";

		if ($print === 1) {
			echo $sql;
			print_r($params);
			die;
		}
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetch() ?: null;
	}


	public function insert_record(string $table, array $fields, int $print = 0): ?int
	{
		$columns = array_keys($fields);
		$cols = '`' . implode('`, `', $columns) . '`';
		$placeholders = ':' . implode(', :', $columns);

		$sql = "INSERT INTO `$table` ($cols) VALUES ($placeholders)";

		if ($print) {
			echo $sql;
			print_r($columns);
			return null;
		}

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute($fields);
			return (int) $this->db->lastInsertId();
		} catch (PDOException $e) {
			error_log("DB Insert Error in $table: " . $e->getMessage());
			throw $e;
		}
	}



	public function insert_record_lastid(string $table, array $fields, int $print = 0): ?int
	{
		if (empty($fields)) {
			return null;
		}

		$columns = array_keys($fields);
		$placeholders = array_map(fn($c) => ":$c", $columns);

		$sql = "INSERT INTO {$table} (" . implode(",", $columns) . ")
            VALUES (" . implode(",", $placeholders) . ")";

		if ($print) {
			echo $sql;
			return null;
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute($fields);

		return (int) $this->db->lastInsertId();
	}


	// public function getvalfield(string $table, string $column, string $condition, int $print = 0)
	// {
	// 	$sql = "SELECT {$column} FROM {$table} WHERE {$condition} LIMIT 1";

	// 	if ($print) {
	// 		echo $sql;
	// 		return null;
	// 	}

	// 	$stmt = $this->db->prepare($sql);
	// 	$stmt->execute();
	// 	$result = $stmt->fetch(PDO::FETCH_ASSOC);

	// 	return $result[$column] ?? null;
	// }

	public function getvalfield(string $table, string $column, string $condition, int $print = 0)
	{
		$sql = "SELECT {$column} FROM {$table} WHERE {$condition} LIMIT 1";

		if ($print) {
			echo $sql;
			return null;
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			return null;
		}

		return reset($result);
	}



	function dateformatindia($date)
	{

		if ($date != "") {
			$ndate = explode("-", $date);
			$year = $ndate[0];
			$day = $ndate[2];
			$month = $ndate[1];

			if ($date == "0000-00-00" || $date == "")

				return "";
			else

				return $day . "-" . $month . "-" . $year;
		} else

			return "";
	}



	function dateformatusa($date)
	{

		if ($date != "") {

			$ndate = explode("-", $date);

			$year = $ndate[2];

			$day = $ndate[0];

			$month = $ndate[1];

			return $year . "-" . $month . "-" . $day;
		} else

			return "";
	}


	public function delete_record(string $table, array $where): bool
	{
		$whereParts = [];
		$params = [];

		foreach ($where as $key => $value) {
			$whereParts[] = "`$key` = :$key";
			$params[$key] = $value;
		}

		$sql = "DELETE FROM `$table` WHERE " . implode(" AND ", $whereParts);

		$stmt = $this->db->prepare($sql);
		return $stmt->execute($params);
	}

	function test_input($data)
	{

		$data = trim($data);

		$data = stripslashes($data);

		$data = htmlspecialchars($data);

		return $data;
	}



	public function checkmenu(string $module_setting, int $loginid): int
	{
		$sql = "SELECT COUNT(*) 
            FROM privilage_setting AS A
            LEFT JOIN m_userprivilege AS B
                ON A.page_id = B.page_id
            WHERE B.menuname = :menuname
              AND A.userid = :userid";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':menuname' => $module_setting,
			':userid'   => $loginid
		]);

		return (int)$stmt->fetchColumn();
	}

	public function check_menuname(string $location, int $loginid): int
	{
		$sql = "SELECT COUNT(*)
            FROM privilage_setting AS A
            LEFT JOIN m_userprivilege AS B
                ON A.page_id = B.page_id
            WHERE A.userid = :userid
              AND B.pagelink = :pagelink";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':userid'   => $loginid,
			':pagelink' => $location
		]);

		return (int)$stmt->fetchColumn();
	}

	public function check_editBtn(string $location, int $loginid): int
	{
		$sql = "SELECT A.pagedit
            FROM privilage_setting AS A
            LEFT JOIN m_userprivilege AS B
                ON A.page_id = B.page_id
            WHERE A.userid = :userid
              AND B.pagelink = :pagelink
            LIMIT 1";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':userid'   => $loginid,
			':pagelink' => $location
		]);

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return isset($row['pagedit']) ? (int)$row['pagedit'] : 0;
	}

	public function check_delBtn(string $location, int $loginid): int
	{
		$sql = "SELECT A.pagedel
            FROM privilage_setting AS A
            LEFT JOIN m_userprivilege AS B
                ON A.page_id = B.page_id
            WHERE A.userid = :userid
              AND B.pagelink = :pagelink
            LIMIT 1";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':userid'   => $loginid,
			':pagelink' => $location
		]);

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return isset($row['pagedel']) ? (int)$row['pagedel'] : 0;
	}
	public function check_pageview(string $location, int $loginid): int
	{
		$sql = "SELECT B.pageview
            FROM privilage_setting AS A
            LEFT JOIN m_userprivilege AS B
                ON A.page_id = B.page_id
            WHERE A.userid = :userid
              AND B.pagelink = :pagelink
            LIMIT 1";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':userid'   => $loginid,
			':pagelink' => $location
		]);

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return isset($row['pageview']) ? (int)$row['pageview'] : 0;
	}
}

$obj = new DataOperation();

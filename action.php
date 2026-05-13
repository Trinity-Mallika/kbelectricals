<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

include("config.php");

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


	public function getRouteDashboardData($loginid, $companyid)
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

		$effectiveWeek = ($total_weeks > 1)
			? (($week - 1) % $total_weeks) + 1
			: 1;

		$sql = "
        SELECT rp.route_planid, rp.batch_no
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
        LIMIT 1
    ";

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':uid' => $loginid,
			':day' => $weekday,
			':week' => $effectiveWeek,
			':companyid' => $companyid
		]);

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$routeplanid = (int) ($row['route_planid'] ?? 0);
		$batch_no = (int) ($row['batch_no'] ?? 0);

		// Today's target
		$currenttotal = (int) $this->getvalfield(
			"route_counter",
			"COUNT(account_id)",
			"batch_no='$batch_no'
         AND is_active=1
         AND companyid='$companyid'"
		);

		// Today's visit (distinct counter only once per day)
		$todayvisit = (int) $this->getvalfield(
			"daily_entries",
			"COUNT(DISTINCT account_id)",
			"DATE(createdate)=CURDATE()
         AND createdby='$loginid'
         AND companyid='$companyid'"
		);

		// Monthly visit (loop visits count every execution)
		$monthvisit = (int) $this->getvalfield(
			"daily_entries",
			"COUNT(entry_id)",
			"MONTH(createdate)=MONTH(CURDATE())
         AND YEAR(createdate)=YEAR(CURDATE())
         AND createdby='$loginid'
         AND companyid='$companyid'"
		);

		// Monthly target
		$Monthtotal = (int) $this->getvalfield(
			"route_counter rc
         JOIN route_plan rp ON rp.batch_no = rc.batch_no",
			"COUNT(DISTINCT rc.account_id)",
			"rp.sales_executive_id='$loginid'
         AND rc.is_active=1
         AND rp.companyid='$companyid'
         AND rc.companyid='$companyid'"
		);

		// Common route account SQL
		$routeAccountsSql = "
        SELECT rc.account_id
        FROM route_plan rp
        JOIN route_counter rc ON rc.batch_no = rp.batch_no
        WHERE rp.route_planid = '$routeplanid'
          AND rc.is_active = 1
          AND rp.companyid = '$companyid'
          AND rc.companyid = '$companyid'
    ";

		// Today's sales
		$todaysales = (float) $this->getvalfield(
			"transaction_entry",
			"COALESCE(SUM(grand_total),0)",
			"account_id IN ($routeAccountsSql)
         AND type='order'
         AND is_approved='1'
         AND billdate >= CURDATE()
         AND billdate < CURDATE() + INTERVAL 1 DAY
         AND companyid='$companyid'"
		);

		// Monthly sales
		$Monthsales = (float) $this->getvalfield(
			"transaction_entry",
			"COALESCE(SUM(grand_total),0)",
			"account_id IN ($routeAccountsSql)
         AND type='order'
         AND is_approved='1'
         AND billdate >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
         AND billdate < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
         AND companyid='$companyid'"
		);

		$routePendingAmount = (float) $this->getvalfield(
			"transaction_entry",
			"COALESCE(
        SUM(
            CASE
                WHEN type='order' THEN grand_total
                WHEN type='payment' THEN -grand_total
                ELSE 0
            END
        ), 0
    )",
			"account_id IN ($routeAccountsSql)
     AND is_approved='1'
     AND companyid='$companyid'"
		);

		// Percentages
		$today_percent = ($currenttotal > 0)
			? ($todayvisit / $currenttotal) * 100
			: 0;

		$month_percent = ($Monthtotal > 0)
			? ($monthvisit / $Monthtotal) * 100
			: 0;

		return [
			'route_plan_id' => $routeplanid,
			'batch_no' => $batch_no,
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

	public function uploadImage(string $imgpath, array $file): string
	{
		if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
			return "";
		}

		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		$allowed = ['jpg', 'jpeg', 'png'];

		if (!in_array($ext, $allowed)) {
			return "";
		}

		$filename = 'DOC' . round(microtime(true) * 1000) . ".jpg";
		$target = rtrim($imgpath, '/') . '/' . $filename;

		if ($ext == 'jpg' || $ext == 'jpeg') {
			$image = imagecreatefromjpeg($file['tmp_name']);
		} elseif ($ext == 'png') {
			$image = imagecreatefrompng($file['tmp_name']);
		} else {
			return "";
		}

		$width = imagesx($image);
		$height = imagesy($image);

		$new_width = 1200;
		$new_height = ($height / $width) * $new_width;

		$tmp = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($tmp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

		imagejpeg($tmp, $target, 60);

		imagedestroy($image);
		imagedestroy($tmp);

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

		return $result[$column] ?? null;
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
}

$obj = new DataOperation();

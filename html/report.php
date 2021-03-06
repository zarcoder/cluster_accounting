<?php
include_once 'includes/main.inc.php';
include_once 'includes/session.inc.php';

if (isset($_POST['create_job_report'])) {

	$month = $_POST['month'];
	$year = $_POST['year'];
	$type = $_POST['report_type'];
	$data = job_functions::get_jobs_bill($db,$month,$year);
	$server_name = settings::get_server_name();
	$filename = $server_name . "-" . $month . "-" . $year . "." . $type; 
}

elseif (isset($_POST['user_job_report'])) {
	$user = new user($db,$ldap,$_POST['user_id']);
	$type = $_POST['report_type'];
	$filename = $user->get_username() . "-" . $_POST['start_date'] . "-" . $_POST['end_date'] . "." . $type;
	$data = $user->get_jobs_report($_POST['start_date'],$_POST['end_date']);
}

elseif (isset($_POST['create_data_report'])) {
	$month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];
        $data = data_functions::get_data_bill($db,$month,$year);
	$server_name = settings::get_server_name();
	$filename = $server_name . "-" . $month . "-" . $year . "." . $type;
}

elseif (isset($_POST['create_user_report'])) {
	$type = $_POST['report_type'];
	$data = user_functions::get_users($db,$ldap);
	$filename = "users." . $type;
}

switch ($type) {
	case 'csv':
		report::create_csv_report($data,$filename);
		break;
	case 'xls':
        	report::create_excel_2003_report($data,$filename);
                break;
	case 'xlsx':
		report::create_excel_2007_report($data,$filename);
		break;
}

?>

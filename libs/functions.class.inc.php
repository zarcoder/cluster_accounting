<?php

class functions {



	public static function get_queues($db,$public = 1) {
		$secs_in_day = 86400;
        	$sql = "SELECT queues.queue_id as queue_id, ";
		$sql .= "queues.queue_name as name, ";
		$sql .= "queues.queue_ldap_group as ldap_group, ";
		$sql .= "queues.queue_description as description, ";
		$sql .= "queues.queue_time_created as time_created, ";
		$sql .= "queue_cost.queue_cost_mem as cost_memory_secs, ";
		$sql .= "queue_cost.queue_cost_mem * " . $secs_in_day . " as cost_memory_day, ";
		$sql .= "queue_cost.queue_cost_cpu as cost_cpu_secs, ";
		$sql .= "queue_cost.queue_cost_cpu * " . $secs_in_day . " as cost_cpu_day, ";
		$sql .= "queue_cost.queue_cost_gpu as cost_gpu_secs, ";
		$sql .= "queue_cost.queue_cost_gpu * " . $secs_in_day . " as cost_gpu_day ";
	        $sql .= "FROM queues ";
        	$sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_queue_id=queues.queue_id ";
	        $sql .= "WHERE queue_enabled='1' ";
		if ($public) {
			$sql .= "AND queue_ldap_group='' ";
		}
		elseif (!$public) {
			$sql .= "AND queue_ldap_group!='' ";
		}
        	$sql .= "GROUP BY queues.queue_id ";
	        $sql .= "ORDER BY queues.queue_name ASC";
        	return $db->query($sql);
	}

	public static function get_all_queues($db) {
		$secs_in_day = 86400;
        	$sql = "SELECT queues.queue_id as queue_id, ";
	        $sql .= "queues.queue_name as name, ";
        	$sql .= "queues.queue_ldap_group as ldap_group, ";
	        $sql .= "queues.queue_description as description, ";
        	$sql .= "queues.queue_time_created as time_created, ";
	        $sql .= "queue_cost.queue_cost_mem as cost_memory_secs, ";
        	$sql .= "queue_cost.queue_cost_mem * " . $secs_in_day . " as cost_memory_day, ";
	        $sql .= "queue_cost.queue_cost_cpu as cost_cpu_secs, ";
        	$sql .= "queue_cost.queue_cost_cpu * " . $secs_in_day . " as cost_cpu_day, ";
	        $sql .= "queue_cost.queue_cost_gpu as cost_gpu_secs, ";
        	$sql .= "queue_cost.queue_cost_gpu * " . $secs_in_day . " as cost_gpu_day ";
	        $sql .= "FROM queues ";
        	$sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_queue_id=queues.queue_id ";
	        $sql .= "WHERE queue_enabled='1' ";
        	$sql .= "GROUP BY queues.queue_id ";
	        $sql .= "ORDER BY queues.queue_name ASC";
        	return $db->query($sql);

	}

	public static function get_projects($db,$custom = false, $start=0,$count=0) {
		$sql = "SELECT projects.*,cfops.*, users.user_name as owner ";
		$sql .= "FROM projects ";
		$sql .= "LEFT JOIN users ON users.user_id=projects.project_owner ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_project_id=projects.project_id ";
		$sql .= "WHERE project_enabled='1' ";
		if ($custom) {
			$sql .= "AND project_default='0' ";
		}
		$sql .= "GROUP BY projects.project_id ";
		$sql .= "ORDER BY projects.project_name ASC ";
		if ($count != 0) {
			$sql .= "LIMIT " . $start . "," . $count;
		}
		return $db->query($sql);

	}

	public static function get_num_projects($db,$custom = false) {
		$sql = "SELECT count(1) as count FROM projects ";
		$sql .= "WHERE project_enabled=1 ";
		if ($custom) {
			$sql .= "AND project_default='0'";
		}
		$result = $db->query($sql);
		return $result[0]['count'];
	}


	public static function get_pretty_date($date) {
		return substr($date,0,4) . "/" . substr($date,4,2) . "/" . substr($date,6,2);

	}


	public static function output_message($messages) {
		$output = "";
		foreach ($messages as $message) {
			if ($message['RESULT']) {
				$output .= "<div class='alert alert-success'>" . $message['MESSAGE'] . "</div>";
			}
			else {
				$output .= "<div class='alert alert-error'>" . $message['MESSAGE'] . "</div>";
			}
		}
		return $output;

	}

	public static function get_cfop($db,$cfop_id) {
		$sql = "SELECT * FROM cfops ";
		$sql .= "WHERE cfop_id='" . $cfop_id . "' ";
		$sql .= "LIMIT 1";
		return $db->query($sql);

	}

	public static function get_torque_job_dir() {
		return __TORQUE_JOBS_LOG__;

	}

	public static function log_message($message) {
                $current_time = date('Y-m-d H:i:s');
                $full_msg = $current_time . ": " . $message . "\n";
                if (self::log_enabled()) {
                        file_put_contents(self::get_log_file(),$full_msg,FILE_APPEND | LOCK_EX);
                }
                echo $full_msg;

        }

        public static function get_log_file() {
                if (!file_exists(__LOG_FILE__)) {
                        touch(__LOG_FILE__);
                }
                return __LOG_FILE__;

        }

        public static function log_enabled() {
                return __ENABLE_LOG__;
        }

}

?>

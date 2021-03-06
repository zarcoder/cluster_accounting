<?php

require_once 'includes/header.inc.php';
$user_id = $login_user->get_user_id();
if (isset($_GET['user_id']) && (is_numeric($_GET['user_id']))) {
        $user_id = $_GET['user_id'];
}

if (!$login_user->permission($user_id)) {
        echo "<div class='alert alert-error'>Invalid Permissions</div>";
        exit;
}

if (isset($_GET['job'])) {
	
	//Done to shorten job number from jobnumber.torque_host_name to just jobnumber, ie 234535.biocluster.igb.illinois.edu
	$job_number_period = strpos($_GET['job'],".");
	$job_number = $_GET['job'];
	if ($job_number_period) {
		$job_number = substr($_GET['job'],0,$job_number_period);
	}
	
	$job = new job($db,$job_number);
	
	if (!$job->job_exists($job_number)) {
		echo "<h3>Job " . $job_number  . " does not exist.  Completed jobs will be entered in the accounting software every hour.</h3>";
		exit;

	}

}
else { 
	echo "<div class='alert alert-error'>This job does not exist. Completed jobs will be entered in the accounting software every hour.</div>";
	exit;

}


$exec_host_html = "<table class='table table-bordered table-condensed table-striped'>";
$exec_host_html .= "<tr><th>Execution Hosts</th></tr>";
$exec_hosts = $job->get_exec_hosts();
if (count($exec_hosts)) {
	foreach ($exec_hosts as $host) {
		$exec_host_html .= "<tr><td>" . $host . "</td></tr>";	

	}
}
else {
	$exec_host_html .= "<tr><td>No Data</td></tr>";
}
$exec_host_html .= "</table>";
?>
<h3>
	Job #
	<?php echo $job->get_full_job_number(); ?>
	Details
</h3>
<div class='row span12'>
<div class='span8'>
<table class='table table-bordered table-condensed table-striped'>
	<tr>
		<td>Job Number:</td>
		<td><?php echo $job->get_full_job_number(); ?></td>
	</tr>
	<tr>
		<td>Job User:</td>
		<td><?php echo $job->get_username(); ?></td>
	</tr>
	<tr>
		<td>Job Name:</td>
		<td><?php echo $job->get_job_name(); ?></td>
	</tr>
	<tr>
		<td>Submitted Project:</td>
		<td><?php echo $job->get_submitted_project(); ?></td>
	</tr>
	<tr>
		<td>Billed Project:</td>
		<td><?php echo $job->get_project()->get_name(); ?></td>
	</tr>
	<tr>
		<td>Queue:</td>
		<td><?php echo $job->get_queue_name(); ?></td>
	</tr>
	<tr>
		<td>Exit Status:</td>
		<td><?php echo $job->get_exit_status(); ?></td>
	</tr>
	<tr>
		<td>Submission Time:</td>
		<td><?php echo $job->get_submission_time(); ?></td>
	</tr>
	<tr>
		<td>Start Time:</td>
		<td><?php echo $job->get_start_time(); ?></td>
	</tr>
	<tr>
		<td>End Time:</td>
		<td><?php echo $job->get_end_time(); ?></td>
	</tr>
        <tr>
                <td>Queued Elapsed Time (H:M:S):</td>
                <td><?php echo $job->get_queued_time_hours(); ?></td>
        </tr>

	<tr>
		<td>Wallclock Time (H:M:S): </td>
		<td><?php echo $job->get_wallclock_time_hours(); ?></td>
	</tr>
	<tr>
		<td>CPU Time (H:M:S):</td>
		<td><?php echo $job->get_cpu_time_hours(); ?></td>
	</tr>

	<?php if ($job->get_cpu_time() * settings::get_reserve_processor_factor() > ($job->get_elapsed_time() * $job->get_slots())) {
		echo "<tr class='success'>";
	}
	else {
		echo "<tr>";
			
	}
	?>
	<td>Processors:</td>
	<td><?php echo $job->get_slots(); ?></td>
	</tr>
	<?php if ($job->get_used_mem() * settings::get_reserve_memory_factor() > $job->get_reserved_mem()) {
		echo "<tr class='error'>";
	}
	else {
		echo "<tr>";
	}

	?>
	<td>Memory Reserved:</td>
	<td><?php echo $job->get_reserved_mem_gb(); ?>GB</td>
	</tr>
	<tr>
		<td>Memory Used:</td>
		<td><?php echo $job->get_used_mem_gb(); ?>GB</td>
	</tr>
	<tr>
		<td>Virtual Memory Used:</td>
		<td><?php echo $job->get_maxvmem_gb(); ?>GB</td>
	</tr>
	<tr>
		<td>Cost:</td>
		<?php if ($job->get_total_cost() < 0.01) { 
			echo "<td>$ < 0.01</td>";
		}
		else { echo "<td>$" . $job->get_formated_total_cost() . "</td>";
		}
		?>
	</tr>
	<tr>
		<td>Amount Billed:</td>
		<?php if (($job->get_billed_cost() < 0.01) && ($job->get_billed_cost() > 0)) {
			echo "<td>$ < 0.01</td>";
		}
		else { echo "<td>$" . $job->get_formated_billed_cost() . "</td>";
		}
		?>
	</tr>
	<tr>
		<td>CFOP:</td>
		<td><?php echo $job->get_cfop(); ?></td>
	</tr>
	<tr>
		<td>Activity Code:</td>
		<td><?php echo $job->get_activity_code(); ?></td>
	</tr>

</table>
</div>
<div class='span4'>
<?php echo $exec_host_html; ?>

</div>
</div>
<div class='row span12'>
<?php
if ($job->get_used_mem() * settings::get_reserve_memory_factor() > $job->get_reserved_mem()) {
	echo "<div class='alert alert-error span8'>Please reserve the appropriate amount of memory.</div>";
}
if ($job->get_cpu_time() * settings::get_reserve_processor_factor() > ($job->get_elapsed_time() * $job->get_slots())) {
	echo "<div class='alert alert-error span8'>Please reserve the appropriate amount of processors.</div>";
}
if ($job->get_submitted_project() !== $job->get_project()->get_name()) {
	echo "<div class='alert alert-error span8'>Please use the correct project when submitting jobs.  This job was charged to your default project.</div>";
}
if (!strpos($job->get_qsub_script(),'module load') && $job->get_qsub_script_exists()) {
	echo "<div class='alert alert-error span8'>Please use the module command in your qsub script.</div>";
}
?>
</div>
<div class='row span12'>
<a href='job_script.php?job=<?php echo $job->get_full_job_number(); ?>' class='btn btn-primary'>View qsub Script</a>&nbsp
<?php
if ($login_user->is_admin()) {
	echo "<a href='edit_job.php?job=" . $job->get_full_job_number() . "' class='btn btn-primary'>Edit Job</a>";

}

?>
</div>
</div>

<?php include_once 'includes/footer.inc.php'; ?>

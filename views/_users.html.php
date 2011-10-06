<?php $usersTasks = $sprint->get_users_tasks(); ?>

<table id="users">
<!--	<tr>
		<th>Member</th>
		<th>Tasks development status</th>
	</tr>-->
	<?php foreach ($usersTasks as $user => $taskStates): ?>
		<?php $statesObject = $sprint->get_users_states_object($taskStates); ?>
		<?php echo render("_user.html.php", null, array('user' => $user, 'statesObject' => $statesObject)); ?>
	<?php endforeach; ?>
</table>

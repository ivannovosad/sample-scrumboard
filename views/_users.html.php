<?php $usersTasks = $sprint->get_users_tasks(); ?>

<table id="users">
	<?php foreach ($usersTasks as $user => $taskStates): ?>
		<?php if ($user): ?>
			<?php $statesObject = $sprint->get_users_states_object($taskStates); ?>
			<?php echo render("_user.html.php", null, array('user' => $user, 'statesObject' => $statesObject)); ?>
		<?php endif; ?>
	<?php endforeach; ?>
</table>

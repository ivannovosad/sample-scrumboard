<div id="sidebar">
  <div class="sprint_status">
	<div class="on_target_text <?= $sprint->get_working_days_left() >= 0 ? 'over' : 'under' ?>">
		<?php if ($sprint->get_working_days_left() < 0) : ?>
			finished<br />
		<?php else: ?>
			<?= round($sprint->get_working_days_left()); ?> days left<br />
		<?php endif; ?>
	</div>
	  
	<div class="total_hours">
		<?= $sprint->get_points_left();?> points left,
		<?= $sprint->get_total_points(); ?> total points<br />
		<? /*= $sprint->get_working_days_left(); */ ?><!-- days left,-->
		Planned burn: <?= $sprint->get_planned_daily_burn(); ?> points/day 
	</div>
  </div>
	
  <div class="graph total_graph">
	<div class="box-wrap">
	  <div class="target" title="Current time" style="left: <?= $sprint->get_current_target_percent(); ?>%;"></div>
	  <div class="actual" title="Finished: <?= $sprint->get_finished_points(); ?> points"
		   style="width: <?= $sprint->get_current_percent(); ?>%;"></div>
           
	  <div class="actualdev" title="Amount of tasks in (at least) Dev done: <?= $sprint->get_dev_done_tasks_count(); ?>"
		   style="width: <?= $sprint->get_dev_done_tasks_percent(); ?>%;"></div>
	</div>
  </div>
	
	<div id="users">
		<?= render('_users.html.php', NULL, array('sprint' => $sprint)); ?>
	</div>
    
	<div id="bugs">
		<?= render('_bugs.html.php', NULL, array('sprint' => $sprint)); ?>
	</div>

	<div id="technology">
		<?= render('_technology.html.php', NULL, array('sprint' => $sprint)); ?>
	</div>
</div>

<ul class="stories">
  <?php foreach ($sprint->stories as $story) : ?>
	<?= render('_dashboard_story.html.php', NULL, array('story' => $story)); ?>
  <?php endforeach; ?>
</ul>


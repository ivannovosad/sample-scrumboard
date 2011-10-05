<div id="sidebar">
  <div class="sprint_status">
	<div class="on_target_text <?= $sprint->get_on_target_delta() >= 0 ? 'over' : 'under' ?>">
		
		<?= $sprint->get_working_days_left(); ?> days left<br />
		
		<!-- <?= $sprint->get_on_target_delta() >= 0 ? '+'.$sprint->get_on_target_delta() : $sprint->get_on_target_delta() ?> hrs -->
	</div>
	  
	<div class="total_hours">
		<?= $sprint->get_time_left();?> points left, <?= $sprint->get_estimate();?> total points<br />
		<!--<?= $sprint->get_working_days_left(); ?> days left,--> <?= $sprint->get_planned_daily_burn(); ?> points/day burn
	</div>
  </div>
	
	

  <div class="graph total_graph">
	<div class="box-wrap">
	  <div class="target" title="Target: <?= $sprint->get_on_target_value(); ?> hours" style="left: <?= $sprint->get_current_target_percent(); ?>%;"></div>
	  <div class="actual" title="Finished: <?= $sprint->get_finished(); ?> hours" style="width: <?= $sprint->get_current_percent(); ?>%;"></div>
	</div>
  </div>
</div>

<ul class="stories">
  <?php foreach ($sprint->stories as $story) : ?>
	<?= render('_dashboard_story.html.php', NULL, array('story' => $story)); ?>
  <?php endforeach; ?>
</ul>
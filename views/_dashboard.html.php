<?php echo render('_sidebar.html.php', NULL, array('sprint' => $sprint, 'backlog_sprint' => $backlog_sprint)); ?>

<ul class="stories">
  <?php foreach ($sprint->stories as $story) : ?>
	<?= render('_dashboard_story.html.php', NULL, array('story' => $story)); ?>
  <?php endforeach; ?>
</ul>

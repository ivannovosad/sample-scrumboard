<?php if ($backlog_sprint['item_id'] != $sprint->item_id): // don't render the dashboard for backlog ?>
	<?php echo render('_sidebar.html.php', NULL, array('sprint' => $sprint)); ?>
<?php endif; ?>

<ul class="stories">
  <?php foreach ($sprint->stories as $story) : ?>
	<?= render('_dashboard_story.html.php', NULL, array('story' => $story)); ?>
  <?php endforeach; ?>
</ul>

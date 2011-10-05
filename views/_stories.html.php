<div class="header">
  <?php foreach ($sprint->states as $state) : ?>
	<?= '<h1>'.$state.'</h1>'; ?>
  <?php endforeach; ?>
</div>
<div class="items">
  <?php foreach ($sprint->stories as $story) : ?>
	<?= render('_story.html.php', NULL, array('story' => $story)); ?>
  <?php endforeach; ?>
</div>
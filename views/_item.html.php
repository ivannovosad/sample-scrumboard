<li class="story-item" data-id="<?= $item->item_id ?>" data-url="<?= $_SESSION['space']['url']; ?>item/<?= $item->item_id ?>">
  <h3>
    <?php /*if ($item->time_left > 0) : ?>
	  <div class="timeleft tooltip" title="Original estimate: <?= round($item->estimate, 2) ?> points">
		<?= round($item->time_left, 2) ?>
	  </div>
	<?php endif; */?>
    <a target="_BLANK" href="<?= $item->link ?>"><?= $item->title ?></a>
  </h3>
  <div class="story-item-details" <?php if (!$item->responsible) { print 'style="display:none;"'; } ?>>
    <?php if ($item->responsible) : ?>
      <div class="responsible">
        <?//= substr($item->responsible['name'], 0, strpos($item->responsible['name'], ' ')); ?>
        <?= $item->responsible['name']; ?>
      </div>
    <?php endif; ?>
  </div>
</li>

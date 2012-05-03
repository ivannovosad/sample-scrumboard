<div class="story-group" id="story-<?= $story->item_id ?>" data-id="<?= $story->item_id ?>">
  <div class="story-header">
    <h2><div class="toggle"></div><?= $story->title ?></h2>
    <?php if ($story->product_owner) : ?>
      <ul class="user-list">
        <li>
			<?php if (isset($story->product_owner['avatar'])): ?>
			<img src="<?= avatar_url($story->product_owner['avatar']); ?>" width="16" height="16">
			<?php endif; ?>
		  
          <?= $story->product_owner['name']; ?> (PO)
        </li>
        <?php $responsible = $story->get_responsible(); ?>
        <?php if ($responsible): ?>
          <?php foreach($responsible as $user): ?>
            <?php if ($user['user_id'] != $story->product_owner['user_id']) : ?>
              <li>
				<?php if (isset($user['avatar'])): ?>
					<img src="<?= avatar_url($user['avatar']); ?>" width="16" height="16">
				<?php endif; ?>
                <?= $user['name']; ?>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    <?php endif; ?>
  </div>
  <?php $i = 0;foreach ($story->get_items_by_state() as $state => $collection) : ?>
    <div class="state state-<?= $i; ?> <?= str_replace(' ', '-', strtolower($state)); ?>">
     
        
      <ul class="story-item-state" data-state="<?= $state; ?>" data-state-id="<?= $i; ?>">
        <?php foreach ($collection as $item) : ?>
          <?= render('_item.html.php', NULL, array('item' => $item)); ?>
        <?php endforeach; ?>
      </ul>
        
        
    </div>
    <?php $i++; ?>
  <?php endforeach; ?>
    <div class="add-task">
        <a class="btn-add-task" data-id="<?php echo $story->item_id; ?>" href="#basic-modal-content_<?php echo $story->item_id; ?>">Add task</a>
    </div>
    <?= render('_modal.html.php', NULL, array('story_item_id' => $story->item_id)); ?>
    
</div>

<li data-id="<?= $story->item_id ?>">

  <div class="body">
    <div class="status-area">
      <ul class="status">
      <?php
        foreach ($story->get_items_by_state() as $state => $list) {
          print '<li style="height: '.round(count($list)/count($story->items)*100).'%;" class="'. str_replace(' ', '-', strtolower($state)) .'" title="'.$state.': '.count($list).' tasks"></li>';
        }
      ?>
      </ul>
      <div class="status-text">
		  <div class="number">
			<?= count($story->get_unfinished_story_items()); ?> 
		  </div>
		  <div class="label">tasks left</div>
		  <div class="points <?= str_replace(' ', '-', strtolower($state));?>"><?= $story->get_points(); ?></div>
		  
		  
	  </div>
    </div>
    <span class="title"><?= $story->title ?> [prio: <?= $story->priority; ?>]</span>

    <div class="story-technologies">
    <?php foreach ($story->technologies as $tech): ?>
        <div class="story-tech-color" style="background-color: #<?php echo $tech['color']; ?>;"><?php echo $tech['text']; ?></div>
    <?php endforeach; ?>
    </div>
    
    <div class="metadata">
      <?php
        $links = array();
        $status_text = $story->get_status_text();
        if ($status_text) {
          $links[] = '<span class="'.$status_text['short'].'">'.$status_text['long'].'</span>';
        }
        
        /* commented out since we dispaly bugs in scrumboard
         * as developement tasks with flag: Is Bug = YES
		$bugsStatesObject = $story->get_bugs_states_object();
		$bugsUrl = SCRUM_SPACE_URL."/app/view/".BUG_APP_ID.
			"?filter_field_id=".BUG_STORY_ID."&filter_field_value={$story->item_id}";
         */
		
		if ($story->dev_started) {
			$links[] = '<span>Dev started: '.dev_started($story->dev_started).'</span>';
		} else {
			$links[] = '<span>Dev not started yet</span>';
		}

        /*
		$links[] = '<a class="reported" href="'.$bugsUrl.'">'.
			$bugsStatesObject->BUG_STATE_REPORTED .' reported bugs</a>';
		
		$links[] = '<a class="fixed" href="'.$bugsUrl.'">'.
			$bugsStatesObject->BUG_STATE_FIXED .' fixed bugs</a>';
		
		$links[] = '<a class="checked" href="'.$bugsUrl.'">'.
			$bugsStatesObject->BUG_STATE_CHECKED .' checked bugs</a>';
        */

        $links[] = '<a href="'.$story->link.'">view in podio</a>';
		
		$items = array();
		$itemsCount = count($story->items);
		$itemsQADoneCount = 0;
		foreach ($story->items as $item) {
			if ($item->state === STATE_QA_DONE) {
				$itemsQADoneCount++;
			}
			$items[] = $item->item_id;
		}

		// only product owner sees the PO done button when all the tasks are in 'QA done' state
		if (current_user_id() === $story->product_owner['user_id']
			&& $itemsCount === $itemsQADoneCount) {

			$links[] = '<button data-id="'.$story->item_id.'" data-value="'.implode(',', $items).'">PO</button>';
		}
      ?>
      <?= implode(' | ', $links); ?>
    </div>
  </div>
</li>

<?php /* echo print_r($story); */ ?>
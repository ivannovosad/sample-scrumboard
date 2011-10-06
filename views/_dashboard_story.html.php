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
    <span class="title"><?= $story->title ?></span>
    <div class="metadata">
      <?php
        $links = array();
        $status_text = $story->get_status_text();
        if ($status_text) {
          $links[] = '<span class="'.$status_text['short'].'">'.$status_text['long'].'</span>';
        }
		$bugsStatesObject = $story->get_bugs_states_object();
        // $links[] = $story->get_points() .' points estimated';
        // $links[] = count($story->bugs) .' total bugs';
		//if ($bugsStatesObject->BUG_STATE_REPORTED > 0) {
			$links[] = '<span class="reported">'.
				$bugsStatesObject->BUG_STATE_REPORTED .' reported bugs</span>';
		//}
		//if ($bugsStatesObject->BUG_STATE_FIXED > 0) {
			$links[] = '<span class="fixed">'.
				$bugsStatesObject->BUG_STATE_FIXED .' fixed bugs</span>';
		//}
		//if ($bugsStatesObject->BUG_STATE_CHECKED > 0) {
			$links[] = '<span class="checked">'.
				$bugsStatesObject->BUG_STATE_CHECKED .' checked bugs</span>';
		//}
        $links[] = '<a href="'.$story->link.'">view in podio</a>';
        // if ($story->product_owner) {
        //   $links[] = $story->product_owner['name'];
        // }
      ?>
      <?= implode(' | ', $links); ?>
    </div>
  </div>
</li>


<?php /*print_r($story); */ ?>
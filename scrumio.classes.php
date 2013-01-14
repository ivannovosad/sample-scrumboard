<?php

class ScrumioItem {

	public $item_id;
	public $title;
	public $estimate;
	public $time_left;
	public $responsible;
	public $state;
	public $commentsCount = 0;
	public $story_id;
	public $is_bug;

	public function __construct($item) {
		global $api;
		// Set Item properties
		$this->item_id = $item['item_id'];
		$this->title = $item['title'];
		$this->link = $item['link'];
		$this->commentsCount = $item['comment_count'];

		foreach ($item['fields'] as $field) {
			if ($field['field_id'] == ITEM_STORY_ID) {
				$this->story_id = $field['values'][0]['value']['item_id'];
			}
			if ($field['field_id'] == ITEM_STATE_ID) {
				$this->state = $field['values'][0]['value'];
			}
			if ($field['field_id'] == ITEM_ISBUG_ID) {
                $value = $field['values'][0]['value']['id'];
				$this->is_bug = ($value === 2) ? true : false;
			}
//			if ($field['field_id'] == ITEM_ESTIMATE_ID) {
//				$this->estimate = 0;
//				if ($field['values'][0]['value'] > 0) {
//					$this->estimate = $field['values'][0]['value']/3600;
//				}
//			}
//			if ($field['field_id'] == ITEM_TIMELEFT_ID) {
//				$this->time_left = 0;
//				if ($field['values'][0]['value'] > 0) {
//					$this->time_left = $field['values'][0]['value']/3600;
//				}
//			}
			if ($field['field_id'] == ITEM_RESPONSIBLE_ID) {
				$this->responsible = array();
				if ($field['values'][0]['value'] > 0) {
					if ($field['values'][0]['value']/*['avatar']*/) {
						$this->responsible = $field['values'][0]['value'];
					}
				}
			}
		}
	}
}

class ScrumioBug {

	public $bug_id;
	public $title;
	public $responsible;
	public $state;
	public $story_ids;

	public function __construct($item) {
		global $api;
		// Set Item properties
		$this->bug_id = $item['item_id'];
		$this->title = $item['title'];
		$this->link = $item['link'];

		foreach ($item['fields'] as $field) {
			
            if ($field['field_id'] == BUG_STORY_ID) {
                foreach ($field['values'] as $story) {
                    //print_r($story);
                    $this->story_ids[] = $story['value']['item_id'];
                }
				//$this->story_id = $field['values'][0]['value']['item_id'];
			}
			if ($field['field_id'] == BUG_STATE_ID) {
				$this->state = $field['values'][0]['value'];
			}
			if ($field['field_id'] == BUG_RESPONSIBLE_ID) {
				$this->responsible = array();
				if ($field['values'][0]['value'] > 0) {
					if ($field['values'][0]['value']/*['avatar']*/) {
						$this->responsible = $field['values'][0]['value'];
					}
				}
			}
		}
	}
}

class ScrumioStory {

	public $item_id;
	public $title;
  public $technologies = array();
	public $product_owner;
	public $states;
	public $total_days;
	public $remaining_days;
	public $items;
	public $points = 0;
	public $priority = 0;
	public $dev_started;
	public $bugs;
  public $data_qa = false;

  
	public function __construct($item, $items, $bugs, $estimate, $time_left, $states, $total_days, $remaining_days) {
		global $api;
		// Set Story properties
		$this->item_id = $item['item_id'];
		$this->title = $item['title'];
		$this->link = $item['link'];
		foreach ($item['fields'] as $field) {
			if ($field['field_id'] == STORY_OWNER) {
				$this->product_owner = $field['values'][0]['value'];
			}
			if ($field['field_id'] == STORY_POINTS_ID) {
				$this->points = $field['values'][0]['value'];
			}
			if ($field['field_id'] == STORY_PRIORITY_ID) {
				$this->priority = (int)$field['values'][0]['value'];
			}
			if ($field['field_id'] == STORY_DEV_STARTED_ID) {
				$this->dev_started = $field['values'][0]['start'];
			}
      if ($field['field_id'] == STORY_DATA_QA_ID) {
        if ($field['values'][0]['value']['text'] === 'Yes') {
          $this->data_qa = true;
        }
      }
      if ($field['field_id'] == STORY_TECHNOLOGY_ID) {
        foreach ($field['values'] as $value) {
          if ($value['value']['status'] === 'active') {
            $this->technologies[] = array('text' => $value['value']['text'], 'color' => $value['value']['color']);
          }
        }
      }
		}
    
		// Get all items for this story
		$this->items = $items;
		$this->bugs = $bugs;
		
		$this->estimate = $estimate;
		$this->time_left = $time_left;

		$this->states = $states;
		$this->total_days = $total_days;
		$this->remaining_days = $remaining_days;
	}
	
	public function get_bugs_states_object() {
		$countReported = $countFixed = $countChecked = 0;
		foreach ($this->bugs as $bug) {
			switch ($bug->state) {
				case BUG_STATE_REPORTED:
					$countReported++;
					break;
				case BUG_STATE_FIXED:
					$countFixed++;
					break;
				case BUG_STATE_CHECKED:
					$countChecked++;
					break;
			}
		}
		
		$statesObject = new stdClass();
		$statesObject->BUG_STATE_REPORTED = $countReported;
		$statesObject->BUG_STATE_FIXED = $countFixed;
		$statesObject->BUG_STATE_CHECKED = $countChecked;
		return $statesObject;
	}

	public function get_points() {
		return (int) $this->points;
	}
  
	public function get_responsible() {
		$list = array();
		foreach ($this->items as $item) {
			if ($item->responsible) {
				$list[$item->responsible['user_id']] = $item->responsible;
			}
		}
		return $list;
	}
  
  public function get_items_by_state() {
    $list = array();
    foreach ($this->states as $state) {
      $list[$state] = array();
    }
    
    foreach ($this->items as $item) {
      $state = $item->state ? $item->state : STATE_NOT_STARTED;
      $list[$state][] = $item;
    }
    
    return $list;
  }
  
  public function get_status_text() {
    $states = $this->get_items_by_state();
    $total = count($this->items);
    $return = array();
    
    if (count($states['Dev done']) > 0 && $total == (count($states['Dev done'])+count($states['QA done'])+count($states['PO done']))) {
      $return = array('short' => 'testing', 'long' => 'ready for testing!');
    }
    elseif (count($states['QA done']) > 0 && $total == (count($states['QA done'])+count($states['PO done']))) {
      $return = array('short' => 'po', 'long' => 'ready for PO signoff!');
    }
    elseif (count($states['PO done']) > 0 && $total == count($states['PO done'])) {
      $return = array('short' => 'done', 'long' => 'all finished!');
    }
    
    return $return;
  }
  
  public function get_time_left() {
    return round($this->time_left, 2);
  }
  
  public function get_estimate() {
    return round($this->total_days, 2);
  }
  
  public function get_on_target_value() {
    $estimate = $this->get_estimate();
    $hours_per_day = $estimate/$this->total_days;
    $target_value = round($estimate-($this->remaining_days*$hours_per_day));
    return $target_value > $estimate ? $estimate : $target_value;
  }
  
  public function get_current_percent() {
    $target = $this->get_on_target_value();
    $total = $this->get_estimate();
    $current = $total-$this->get_time_left();
    $target_percent = $target/$total*100;
    return $current/$total*100;
  }
  
  public function get_current_target_percent() {
    $target = $this->get_on_target_value();
    $total = $this->get_estimate();
    $current = $total-$this->get_time_left();
    return $target/$total*100;
  }
	
	public function get_finished_story_items() {
		$list = array();
		foreach ($this->states as $state) {
		  $list[$state] = array();
		}

		foreach ($this->items as $item) {
		  $state = $item->state ? $item->state : STATE_PO_DONE;
		  $list[$state][] = $item;
		}
		return $list;
	}
	
	public function get_unfinished_story_items() {
		$items = array();
		foreach ($this->items as $item) {
			if ($item->state !== STATE_PO_DONE) {
				$items[] = $item;
			}
		  
		}
		return $items;
	}
}

class ScrumioSprint {
  
  public $item_id;
  public $title;
  public $start_date;
  public $end_date;
  public $states;
  public $total_days;
  public $remaining_days;
  public $stories;
  
  public function __construct($sprint) {
    global $api;
    // Locate available states
    $items_app = $api->app->get(ITEM_APP_ID);
    $this->states = array();
    if(is_array($items_app['fields'])) {
      foreach ($items_app['fields'] as $field) {
        if ($field['field_id'] == ITEM_STATE_ID) {
          $this->states = $field['config']['settings']['allowed_values'];
          break;
        }
      }
    }
    // Find active sprint
    // $filters = array(array('key' => SPRINT_STATE_ID, 'values' => array('Active')));
    // $sprints = $api->item->getItems(SPRINT_APP_ID, 1, 0, 'title', 0, $filters);
    // $sprint = $sprints['items'][0];
    $sprint_id = $sprint['item_id'];
    
    // Set sprint properties
    $this->item_id = $sprint['item_id'];
    $this->title = $sprint['title'];
    foreach ($sprint['fields'] as $field) {
      if ($field['type'] == 'date') {
        $this->start_date = date_create($field['values'][0]['start'], timezone_open('UTC'));
        $this->end_date = date_create($field['values'][0]['end'], timezone_open('UTC'));
      }
    }
	
    // Get all stories in this sprint
    $filters = array(array('key' => STORY_SPRINT_ID, 'values' => array($sprint_id)));
    // $stories = $api->item->getItems(STORY_APP_ID, 200, 0, 'title', 0, $filters);
	// stories are ordered by highest priority (lower number is higher priority)
	$stories = $api->item->getItems(STORY_APP_ID, 200, 0, STORY_PRIORITY_ID, 0, $filters);
   
    // Grab all story items for all stories in one go
    $stories_ids = array();
    $stories_items = array();
    $stories_bugs = array();
    $stories_estimates = array();
    $stories_time_left = array();
    foreach ($stories['items'] as $story) {
      $stories_ids[] = $story['item_id'];
      $stories_items[$story['item_id']] = array();
      $stories_bugs[$story['item_id']] = array();
      $stories_estimates[$story['item_id']] = 0;
      $stories_time_left[$story['item_id']] = 0;
    }
    $filters = array(array('key' => ITEM_STORY_ID, 'values' => $stories_ids));
    $raw = $api->item->getItems(ITEM_APP_ID, 200, 0, 'title', 0, $filters);
    foreach ($raw['items'] as $item) {
      $item = new ScrumioItem($item);
      $stories_items[$item->story_id][] = $item;
//      $stories_estimates[$item->story_id] = $stories_estimates[$item->story_id] + $item->estimate;
//      $stories_time_left[$item->story_id] = $stories_time_left[$item->story_id] + $item->time_left;
      if (isset($stories_estimates[$item->story_id])) {
        $stories_estimates[$item->story_id] += $item->estimate;
      } else {
          $stories_estimates[$item->story_id] = $item->estimate;
      }
      if (isset($stories_time_left[$item->story_id])) {
        $stories_time_left[$item->story_id] += $item->time_left;
      } else {
          $stories_time_left[$item->story_id] = $item->time_left;
      }
    }
	
	$filters = array(array('key' => BUG_STORY_ID, 'values' => $stories_ids));
    $raw = $api->item->getItems(BUG_APP_ID, 200, 0, 'title', 0, $filters);
	$bugs = array();
    foreach ($raw['items'] as $item) {
		$bug = new ScrumioBug($item);
        
        foreach ($bug->story_ids as $storyID) {
            $stories_bugs[$storyID][] = $bug;
        }
		//$stories_bugs[$bug->story_id][] = $bug;
    }

    foreach ($stories['items'] as $story) {
	  $bugs = $stories_bugs[$story['item_id']];
      $items = $stories_items[$story['item_id']];
      $estimate = $stories_estimates[$story['item_id']] ? $stories_estimates[$story['item_id']] : '0';
      $time_left = $stories_time_left[$story['item_id']] ? $stories_time_left[$story['item_id']] : '0';
      
      //if (count($items) > 0) {
        $this->stories[] = new ScrumioStory($story, $items, $bugs, $estimate, $time_left, $this->states, $this->get_working_days(), $this->get_working_days_left());
      //}
    }
    
  }
  
	/**
	 *
	 * returns only finished stories for current sprint
	 * @return array 
	 */
	public function get_finished_stories() {
		$finishedStories = array();
	  
		// iterate over all sprints stories
		foreach ($this->stories as $story) {

			// if a story has story items, iterate over all of them
			// and find out if all of them are finished
			// only if so, the story itself is finished
			if ($story->items) {
				
				$itemsCount = count($story->items);
				$finishedItemsCount = 0;

				foreach ($story->items as $item) {
					if ($item->state === STATE_PO_DONE) {
						$finishedItemsCount++;
					}
				}
				
				// all of the items are finished
				if ($itemsCount > 0 && $itemsCount === $finishedItemsCount) {
					$finishedStories[] = $story;
				}
			}
		}
		return $finishedStories;
	}
	
	/**
	 * return all sprint's stories' points 
	 * @return int 
	 */
	public function get_total_points() {
		$points = 0;
		foreach ($this->stories as $story) {
			$points += $story->points;
		}
		return (int) $points;
	}

  /**
   * return all sprint's stories' points 
   * @return int 
   */
  public function get_drupal_points() {
    return $this->get_points_for_technology(TECHNOLOGY_DRUPAL);
  }

  public function get_design_points() {
    return $this->get_points_for_technology(TECHNOLOGY_DESIGN);
  }

  public function get_ror_points() {
    return $this->get_points_for_technology(TECHNOLOGY_ROR);
  }

  public function get_infrastructure_points() {
    return $this->get_points_for_technology(TECHNOLOGY_INFRASTRUCTURE);
  }

  public function get_points_for_technology($technologyName) {
    $points = 0;
    foreach ($this->stories as $story) {
      $technologiesCount = count($story->technologies);
      if ($technologiesCount > 0) { 
        foreach ($story->technologies as $technology) {
          if ($technology['text'] === $technologyName) {
            $points += ($story->points / $technologiesCount);
          }
        }
      }
    }
    return (float) $points;
  }
	
	/**
	 * returns the amount of points of unfinished stories
	 * those with 
	 * @return int
	 */
	public function get_points_left() {
		$totalPoints = $this->get_total_points();
		$finishedStories = $this->get_finished_stories();
		
		$finishedStoriedPoints = 0;
		if (count($finishedStories) > 0) {
			foreach ($finishedStories as $story) {
				$finishedStoriedPoints += $story->points;
			}
			return (int) ($totalPoints - $finishedStoriedPoints);
		}
		return $totalPoints;
	}
	
	public function get_finished_points() {
		$finishedStories = $this->get_finished_stories();
		
		$finishedStoriedPoints = 0;
		if (count($finishedStories) > 0) {
			foreach ($finishedStories as $story) {
				$finishedStoriedPoints += $story->points;
			}
			return (int) ($finishedStoriedPoints);
		}
		return 0;
	}
	
	public function get_current_percent() {
		$totalPoints = $this->get_total_points();
		$pointsLeft = $this->get_points_left();
		
		$percentage = ($totalPoints - $pointsLeft) / $totalPoints * 100;
		return round($percentage, 2);
	}
    
    
    public function get_dev_done_tasks_count() {
        
        // get tasks that are at least in "Dev done"
		return count($this->get_tasks(
            array(STATE_DEV_DONE, STATE_QA_DONE, STATE_PO_DONE)
        ));
    }
    
	public function get_dev_done_tasks_percent() {
		$allTasks = $this->get_tasks(
            array(STATE_NOT_STARTED, STATE_DEV_STARTED, STATE_DEV_DONE, STATE_QA_DONE, STATE_PO_DONE)
        );
        $allTasksCount = count($allTasks);
        
        $devDoneTasksCount = $this->get_dev_done_tasks_count();
        
		$percentage = $devDoneTasksCount / $allTasksCount * 100;
		return round($percentage, 2);
	}
    
    /**
     *
     * @param array $states
     * @example $states = array(STATE_DEV_DONE, STATE_QA_DONE, STATE_PO_DONE)
     * @return array 
     */
    public function get_tasks($states, $bugsOnly = false) {
        
        $tasks = array();
        foreach ($this->stories as $story) {
			foreach ($story->items as $item) {
				if (in_array($item->state, $states)) {
                    if ($bugsOnly) {
                        if ($item->is_bug) {
                            $tasks[] = $item;
                        }
                    } else {
                        $tasks[] = $item;
                    }
                }
			}
		}
        return $tasks;
    }
	
	public function get_users_tasks() {
		$usersTasks = array();
		foreach ($this->stories as $story) {
			foreach ($story->items as $item) {
				$user = $item->responsible['name'];
				$usersTasks[$user][] = $item->state;
			}
		}
		return $usersTasks;
	}
	
	/**
	 *
	 * get object that contains counts of users PO Done and Dev done tasks
	 * @param array $userTaskStates
	 * @return object 
	 */
	public function get_users_states_object($userTaskStates) {
		$countAll = $countDevDone = $countPODone = 0;
		foreach ($userTaskStates as $taskState) {
			if ($taskState === STATE_PO_DONE) {
				$countPODone++;
			} elseif ($taskState === STATE_DEV_DONE || $taskState === STATE_QA_DONE || $taskState === STATE_PO_DONE) {
				$countDevDone++;
			}
			$countAll++;
		}
		
		$statesObject = new stdClass();
		$statesObject->PO = $countPODone;
		$statesObject->DEV = $countDevDone;
		$statesObject->ALL = $countAll;
		return $statesObject;
	}
 
  
  public function get_working_days() {
    return getWorkingDays(date_format($this->start_date, 'Y-m-d'), date_format($this->end_date, 'Y-m-d'));
  }
  
  public function get_working_days_left() {
    $start_date = date_create('now', timezone_open('UTC'));
	
    // We substract 1 here to be able to 'chase the target' rather than 'working ahead'
    //return getWorkingDays(date_format($start_date, 'Y-m-d'), date_format($this->end_date, 'Y-m-d'))-1;
    // changed by
    return getWorkingDays(date_format($start_date, 'Y-m-d'), date_format($this->end_date, 'Y-m-d'));
  }
  
  public function get_time_left() {
    static $list;
    if (!isset($list[$this->item_id])) {
      $list[$this->item_id] = 0;
      foreach ($this->stories as $story) {
        $list[$this->item_id] = $list[$this->item_id]+$story->get_time_left();
      }
    }
    return $list[$this->item_id] ? round($list[$this->item_id], 2) : '0';
  }
  
  public function get_estimate() {
    static $list;
    if (!isset($list[$this->item_id])) {
      $list[$this->item_id] = 0;
      foreach ($this->stories as $story) {
        $list[$this->item_id] = $list[$this->item_id]+$story->get_estimate();
      }
    }
    return $list[$this->item_id] ? round($list[$this->item_id], 2) : '0';
  }
  
  public function get_on_target_value() {
    static $list;
    if (!isset($list[$this->item_id])) {
      $estimate = $this->get_estimate();
      $total_days = $this->get_working_days();
      $remaining_days = $this->get_working_days_left();
      $hours_per_day = $estimate/$total_days;
      $target_value = round($estimate-($remaining_days*$hours_per_day));
      $list[$this->item_id] = $target_value > $estimate ? $estimate : $target_value;
      
    }
    return $list[$this->item_id];
  }
  
	public function get_planned_daily_burn() {
		$estimate = $this->get_total_points();
		$total_days = $this->get_working_days();
		$hours_per_day = $estimate/$total_days;
		$dailyBurn = round($hours_per_day, 2);

		return $dailyBurn;
	}

	public function get_current_target_percent() {
		$target = $this->get_on_target_value();
		$total = $this->get_estimate();
		$current = $total-$this->get_time_left();
		return $target/$total*100;
	}

  public function get_finished() {
    return $this->get_estimate()-$this->get_time_left();
  }
}

//The function returns the no. of business days between two dates and it skips the holidays
function getWorkingDays($startDate,$endDate,$holidays = array()){
  //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
  //We add one to inlude both dates in the interval.
  $days = (strtotime($endDate) - strtotime($startDate)) / 86400; //+ 1;
  
  // echo "<h1>DAYS: ".$days."</h1>";
  $no_full_weeks = floor($days / 7);
  $no_remaining_days = fmod($days, 7);

  //It will return 1 if it's Monday,.. ,7 for Sunday
  $the_first_day_of_week = gmdate("N",strtotime($startDate));
  $the_last_day_of_week = gmdate("N",strtotime($endDate));

  //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
  //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
  if ($the_first_day_of_week <= $the_last_day_of_week){
    if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
    if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
  }
  else{
    if ($the_first_day_of_week <= 6) {
      //In the case when the interval falls in two weeks, there will be a weekend for sure
      $no_remaining_days = $no_remaining_days - 2;
    }
  }

  //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
 $workingDays = $no_full_weeks * 5;
  if ($no_remaining_days > 0 )
  {
    $workingDays += $no_remaining_days;
  }

  //We subtract the holidays
  foreach($holidays as $holiday){
    $time_stamp=strtotime($holiday);
    //If the holiday doesn't fall in weekend
    if (strtotime($startDate) <= $time_stamp && $time_stamp <= strtotime($endDate) && gmdate("N",$time_stamp) != 6 && gmdate("N",$time_stamp) != 7)
      $workingDays--;
  }

  return $workingDays;
}

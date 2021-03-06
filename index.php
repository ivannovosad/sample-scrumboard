<?php
require_once 'vendor/limonade.php';
require_once 'scrumio.classes.php';
require_once 'init.php';
require_once 'helpers.php';

function configure() {
  option('OAUTH_REDIRECT_URI', 'http://'.$_SERVER['HTTP_HOST'].url_for('authorize'));
  option('OAUTH_URL', htmlentities(OAUTH_ENDPOINT.'?response_type=code&client_id='.CLIENT_ID.'&redirect_uri='.rawurlencode(option('OAUTH_REDIRECT_URI'))));
}

dispatch('/', 'scrumboard');
dispatch('/show/:id', 'scrumboard');
dispatch('/reload/:id', 'scrumboard');
  function scrumboard() {
    global $api;
    // If we have an access token, show the scrumboard
    if (isset($_SESSION['access_token']) && $_SESSION['access_token'] && isset($_SESSION['story_app']) && $_SESSION['story_app']) {
      
      $filters = array(array(
          'key' => 'name',
          'values' => array( 'Backlog' )
      ));
      $backlog_sprint = $api->item->getItems(SPRINT_APP_ID, 1, 0, SPRINT_DURATION_ID, 0, $filters);
      $backlog_sprint = $backlog_sprint['items'][0];

      // Grab sprints and find current sprint
      $sprints = $api->item->getItems(SPRINT_APP_ID, 5, 0, SPRINT_DURATION_ID, 1);
      foreach ($sprints['items'] as $item) {
        if (params('id') == $item['item_id']) {
          $current_sprint = $item;
          break;
        }
        else {
          foreach ($item['fields'] as $field) {
            if ($field['field_id'] == SPRINT_STATE_ID) {
              if ($field['values'][0]['value'] == 'Active') {
                $current_sprint = $item;
              }
            }
          }
        }
      }

      if (params('id') == $backlog_sprint['item_id']) {
        $current_sprint = $backlog_sprint;
      }
      
  	  ///// current_sprint
  		$sprint = new ScrumioSprint($current_sprint);
  	  
  		// only return contents of partials when periodical refreshin
  		if ( strpos(request_uri(), '/reload') !== false ) {
  			
  			$dashboard = html('_dashboard.html.php', NULL, array('sprint' => $sprint, 'sprints' => $sprints['items'], 'backlog_sprint' => $backlog_sprint));
  			$stories = html('_stories.html.php', NULL, array('sprint' => $sprint, 'sprints' => $sprints['items'], 'backlog_sprint' => $backlog_sprint));
  			
  			return json_encode(array('dashboard' => $dashboard, 'stories' => $stories));
  		} else {
  			return html('index.html.php', NULL, array('sprint' => $sprint, 'sprints' => $sprints['items'], 'backlog_sprint' => $backlog_sprint));
  		}

    } else {
      // No access token, show the "login" screen
      return html('login.html.php', NULL, array('oauth_url' => option('OAUTH_URL')));
    }
  }
  
dispatch('/authorize', 'authorize');
  function authorize() {
    global $oauth;
    
    $story_app = NULL;
    
    // Successful authorization. Store the access token in the session
    if (!isset($_GET['error'])) {
      $oauth->getAccessToken('authorization_code', array('code' => $_GET['code'], 'redirect_uri' => option('OAUTH_REDIRECT_URI')));
      $api = new PodioAPI();
      $_SESSION['access_token'] = $oauth->access_token;
      $_SESSION['refresh_token'] = $oauth->refresh_token;
      $story_app = $api->app->get(STORY_APP_ID);
    }
    
    if ($story_app) {
      $_SESSION['story_app'] = $story_app;
      $_SESSION['space'] = $api->space->get($_SESSION['story_app']['space_id']);
      redirect_to('');
    }
    else {
      // Something went wrong. Display appropriate error message.
      unset($_SESSION['access_token']);
      unset($_SESSION['refresh_token']);
      $error_description = !empty($_GET['error_description']) ? htmlentities($_GET['error_description']) : 'You do not have access to the ScrumIO apps. Try logging in as a different user.';
      return html('login.html.php', NULL, array('oauth_url' => option('OAUTH_URL'), 'error_description' => $error_description));
    }
  }
  
dispatch_post('/item', 'create_item');
    function create_item() {
        global $api;
        
        $values = array(
            array(
                'field_id' => ITEM_TITLE_ID,
                'values' => array('value' => $_POST['item_name'])
            ),
            array(
                'field_id' => ITEM_STATE_ID,
                'values' => array('value' => STATE_NOT_STARTED)
            ),
            array(
                'field_id' => ITEM_STORY_ID,
                'values' => intval($_POST['story_id'])
            )
         );
        
        $result = $api->item->create(ITEM_APP_ID, $values);
        return txt('ok');
    }

dispatch_put('/item/:item_id', 'update_time_left'); 
  function update_time_left() {
    global $api;
    $item_id = params('item_id');
    $state = $_POST['state'];
    
    
    $itemDataObj = $api->item->get($item_id);
    // print_r($itemDataObj['fields']);

    if (isset($_POST['assign'])) {
        // assign task to me
        $userArray = $api->user->getOwnProfile();
        $profileID = $userArray['profile_id'];
        
        $data = array(array('value' => $profileID));
        $api->item->updateFieldValue($item_id, ITEM_RESPONSIBLE_ID, $data);
    }
    
    $data = array(array('value' => $state));
    $api->item->updateFieldValue($item_id, ITEM_STATE_ID, $data);
    
//    // Set time_left to '0' when moving to one of the 'done' states
//    if (in_array($state, array(/*STATE_DEV_DONE, STATE_QA_DONE, */STATE_PO_DONE))) {
//      $api->item->updateFieldValue($item_id, ITEM_TIMELEFT_ID, array(array('value' => 0)), 1);
//    }
//    // Reset time left when moving to Not Started
//    elseif ($state == STATE_NOT_STARTED) {
//      $item = $api->item->getBasic($item_id);
//      $item = new ScrumioItem($item);
//      $api->item->updateFieldValue($item_id, ITEM_TIMELEFT_ID, array(array('value' => $item->estimate*60*60)), 1);
//    }
    return txt('ok');
  }
  
dispatch_put('/story/:story_id', 'update_story'); 
	function update_story() {
		global $api;
		$story_id = params('story_id');
        
        if (isset($_POST['priority'])) {
            $priority = intval($_POST['priority']);
            update_story_priority($story_id, $priority);
        }
		
		$dev_started_param = @$_POST['dev_started'];
        
		if ($dev_started_param === 'true') {
			$dev_started = array('start' => date("Y-m-d H:i:s"));
			$data = array($dev_started);
		} else {
			// send an empty array to reset the field ('NULL')
			$data = array();
		}
		
		$api->item->updateFieldValue($story_id, STORY_DEV_STARTED_ID, $data);
        
		return txt('ok');
	}
    
    function update_story_priority($story_id, $priority) {
        global $api;
        
        $data = array($priority);
        $api->item->updateFieldValue($story_id, STORY_PRIORITY_ID, $data);
        
		return txt('ok');
    }

dispatch('/logout', 'logout');
  function logout() {
    unset($_SESSION['access_token']);
    unset($_SESSION['refresh_token']);
    unset($_SESSION['space']);
    unset($_SESSION['story_app']);
    redirect_to('');
  }

run();

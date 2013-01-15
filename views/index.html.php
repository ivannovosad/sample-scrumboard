<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
  <head>
    <title>Scrum Board Diagnosia</title>
    <!-- <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"> -->
    <link href='https://fonts.googleapis.com/css?family=PT+Sans:regular,italic,bold' rel='stylesheet' type='text/css'>       
    <link rel="stylesheet" href="public/base.css" type="text/css" media="all" charset="utf-8">
    <link rel="stylesheet" href="public/dashboard.css" type="text/css" media="all" charset="utf-8">
    <link rel="stylesheet" href="public/board.css" type="text/css" media="all" charset="utf-8">
    <link rel="stylesheet" href="public/tipsy/stylesheets/tipsy.css" type="text/css" media="all" charset="utf-8">
    <link rel="stylesheet" href="public/jquery.fancybox-1.3.4.css" type="text/css" media="all" charset="utf-8">
    <link rel="shortcut icon" href="public/i/favicon.png"> 
  </head>
  <body>
    <div id="navbar">
      <a id="logout" href="<?= url_for('logout');?>">Log out</a>
      <a href="#" id="switch-view">Switch view</a> | 
      Sprints: 
      <ul class="sprints">
        <?php foreach ($sprints as $item) : ?>
    			<?php
    				$sprintStart = $item['fields'][1]['values'][0]['start'];
    				$sprintStart = date("d.m.Y", strtotime($sprintStart));
    			?>
          <li class="<?= $sprint->item_id == $item['item_id'] ? 'selected' : '' ?>">
    			  <a href="<?= url_for('/show/'.$item['item_id']);?>"><?= $sprintStart; /*$item['title'];*/ ?></a>
    		  </li>
        <?php endforeach; ?>

        <li class="<?= $backlog_sprint['item_id'] == $sprint->item_id ? 'selected' : '' ?>">
          <a href="<?= url_for('/show/'.$backlog_sprint['item_id']);?>"><?= $backlog_sprint['title']; ?></a>
        </li>
      </ul>
    </div>
    <div id="main">

      <div id="dashboard">
			<?php echo render('_dashboard.html.php', NULL, array('sprint' => $sprint, 'backlog_sprint' => $backlog_sprint)); ?>
      </div>

      <div id="stories" class="story-view hidden" data-count="<?= count($sprint->states); ?>">
			<?= render('_stories.html.php', NULL, array('sprint' => $sprint)); ?>
      </div>
    </div>
    <div id="overlay"></div>
    <script type="text/javascript" charset="utf-8">
      var update_url_base = "<?= url_for('/item'); ?>";
      var update_story_url_base = "<?= url_for('/story'); ?>";
      var state_po_done = "<?= STATE_PO_DONE; ?>";
      var state_not_started = "<?= STATE_NOT_STARTED; ?>";
      var state_dev_started = "<?= STATE_DEV_STARTED; ?>";
	  var sprint_id ="<?php echo $sprint->item_id; ?>";
    </script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script> 
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.js"></script>
    <script src="public/tipsy/javascripts/jquery.tipsy.js" type="text/javascript" charset="utf-8"></script>
    <script src="public/lib/jquery.ui.touch-punch.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="public/lib/jquery.periodicalupdater.js" type="text/javascript" charset="utf-8"></script>
    <script src="public/lib/Function.prototype.js" type="text/javascript" charset="utf-8"></script>
    <script src="public/lib/Podio.js" type="text/javascript" charset="utf-8"></script>
    <script src="public/lib/Podio.Event.js" type="text/javascript" charset="utf-8"></script>
    <script src="public/lib/Podio.Event.UI.js" type="text/javascript" charset="utf-8"></script>
    
    <script type="text/javascript" src="public/lib/jquery.mousewheel-3.0.4.pack.js"></script>
	<script type="text/javascript" src="public/lib/jquery.fancybox-1.3.4.pack.js"></script>
    
    <script src="public/scrumboard.js" type="text/javascript" charset="utf-8"></script>
  </body>
</html>

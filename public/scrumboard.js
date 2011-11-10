(function (window, $, undefined) {

  function onInit() {
    $('ul.status li').tipsy({gravity: 'e'});
    $('.graph .target, .graph .actual').tipsy({gravity: 's'});
    $('.tooltip').tipsy({gravity: 'n'});
	
	var url = "?/reload";
	if (sprint_id) {
		url += "/"+sprint_id;
	}
	$.PeriodicalUpdater(url, {
        method: 'get',          // method; get or post
        data: null,               // array of values to be passed to the page - e.g. {name: "John", greeting: "hello"}
        minTimeout: 120000,      // starting value for the timeout in milliseconds
        maxTimeout: 3000000,      // maximum length of time between requests
        multiplier: 2,          // the amount to expand the timeout by if the response hasn't changed (up to maxTimeout)
        type: 'json',           // response type - text, xml, json, etc.  See $.ajax config options
        maxCalls: 0,            // maximum number of calls. 0 = no limit.
        autoStop: 0             // automatically stop requests after this many returns of the same data. 0 = disabled.
    }, function(remoteData, success, xhr, handle) {
		$('#dashboard').html(remoteData.dashboard);
		$('#stories').html(remoteData.stories);
		initSingleStoryView();
		
    });
  }
  

	function onDashBoardStoryClick(elmTarget, e) {

		if (e.target.nodeName === 'A') {
			window.open(e.target.href, "_blank");
		} else if (e.target.nodeName === 'BUTTON') {
            
			var itemIDs = $(e.target).data('value').toString();
			if (itemIDs.indexOf(",") >= 0) {
				var ids = itemIDs.split(",");
				for (id in ids) {
					setDevelopmentTaskPODone(e, ids[id]);
				}
			} else {
				setDevelopmentTaskPODone(e, itemIDs);
			}
		} else {
			// Single story click: switch to board view and scroll to story
			onDashBoardToggleClick();
			var storyId = elmTarget.data('id');
			$('html,body').scrollTop($('#story-' + storyId).offset().top - 75);
		}
	}
	
	function setDevelopmentTaskPODone(e, taskID) {
		$.ajaxSetup({async:false});
		
		$(e.target.parentNode).append(
		'<div class="spinner">Setting all development tasks to PO DONE.<br />Please wait...</div>');
		
		$.post(update_url_base+'/'+taskID, {'state':state_po_done, '_method':'PUT'}, function(data){
			if ($(e.target.parentNode).find('.spinner')) {
				$(e.target.parentNode).find('.spinner').remove();
			}
			reloadView();
		});
	}
  
	function onDashBoardToggleClick(elmTarget, e) {
		$('#dashboard, #stories').toggle();
		
		$('#dashboard, #stories').ajaxStart(function() {
			$("#navbar").append('<div id="refresh">Refreshing...</div>');
		})
		.ajaxStop(function() {
			$("#refresh").remove();
		});
		reloadView();
		initSingleStoryView();
		$('html, body').scrollTop(0);
	}
  
	function reloadView() {
		var url = "?/reload";
		if (sprint_id) {
			url += "/"+sprint_id;
		}
		$.get(
			url,
			function(remoteData, status, xhr)  {
				$('#dashboard').html(remoteData.dashboard);
				$('#stories').html(remoteData.stories);
				initSingleStoryView();
			},
			"json"
		);
	}
  
  function initSingleStoryView() {
    function resize_stories() {
      // Recalculate width according to browser width
      var total_width = $(window).width()-10;
      $('.story-group').width(total_width);

      total_width = total_width-175; // width of the story-header
      var count = $('#stories').data('count');
      var wrapper_width = Math.floor(total_width/count);

	// fix of the overflowing columns
	if (wrapper_width > 238) {
		wrapper_width = 238;
	}

      $('.story, .state,.header h1').width(wrapper_width);
      
    }
    
    function set_story_height(current_id) {
      var states = $(current_id).find('.state');
      var max_height = 0;
      states.each(function(){
        var current_height = 0;
        $(this).find('li').each(function(){
          if (!$(this).attr('style')) {
            current_height += $(this).outerHeight(true);
          }
        });
        
        if (current_height > max_height) {
          max_height = current_height;
        }
      });
      states.find('ul').height(max_height);
    }
    
    resize_stories();
    
    $(window).resize(function(){
      resize_stories();
    });
	
	
	function get_all_story_tasks(storyElementID) {
		var tasks = new Array();
		
		// all the items
		$(storyElementID + " li.story-item").each(function (index, item) {
			if (!$(item).hasClass('ui-draggable-dragging')) {
				tasks[index] = $(item);
			}
		});
		return tasks;
	}
	
	function are_all_tasks_not_started(tasks) {
		
		var notStartedCount = 0;
		var statuses = new Array();
		for (i = 0; i < tasks.length; i++) {
			var state = $(tasks[i]).parent().data("state");
			if (state == state_not_started) {
				notStartedCount++;
			}
		}
		if (tasks.length == notStartedCount) {
			return true;
		} else {
			return false;
		}
	}
	

    $('.story-group').each(function(){
		
		var allStoryTasks, allTasksNotStarted;
		var current_id = '#'+$(this).attr('id');
	  
      $(current_id+' .story-item-state li').draggable({
        cancel: ".spinner",
        revert: "invalid", // when not dropped, the item will revert back to its initial position
        containment: current_id, // stick to demo-frame if present
        helper: "clone",
        cursor: "move",
        start: function(event, ui){
            set_story_height(current_id);
			allStoryTasks = get_all_story_tasks(current_id);
			allTasksNotStarted = are_all_tasks_not_started(allStoryTasks);
			$(ui.helper).width($(ui.helper).parent().width());
        }
      });

      $(current_id+' .story-item-state').droppable({
        accept: current_id+' .story-item-state > li',
        activeClass: 'ui-state-highlight',
        tolerance: 'pointer', 
        drop: function(event, ui) {
          var old_state = $(ui.draggable).parents('ul').data('state');
          var state = $(this).data('state');
          if (state != old_state) {
            var item_id = $(ui.draggable).data('id');
            $(this).append(ui.draggable);
            // $(this).css('background', '#eee');
			
			var storyID = $(ui.draggable).parents("div.story-group").data('id');
			
			// if none of the story tasks are started and the current one is dropped into Dev started,
			// update Story 'Dev started'
			if (allTasksNotStarted && state == state_dev_started) {
				$.post(update_story_url_base+'/'+storyID, {'dev_started':true, '_method':'PUT'}, function(data){
				});
			}
			
			if (state == state_not_started) {
				if (are_all_tasks_not_started(allStoryTasks)) {
					// reset story's 'Dev started'
					$.post(update_story_url_base+'/'+storyID, {'dev_started':false, '_method':'PUT'}, function(data){
					});
				}
			}

            // Make Ajax request to change state on Podio
            $(ui.draggable).append('<div class="spinner"></div>');
            $.post(update_url_base+'/'+item_id, {'state':state, '_method':'PUT'}, function(data){
              $(ui.draggable).find('.spinner').remove();
            });
          }
        },
        over: function(event, ui) {
          $(ui.helper)
            .removeClass('dragging-0')
            .removeClass('dragging-1')
            .removeClass('dragging-2')
            .removeClass('dragging-3')
            .removeClass('dragging-4')
            .addClass('dragging-'+$(this).attr('data-state-id'));
			
			allStoryTasks = get_all_story_tasks(current_id);
			allTasksNotStarted = are_all_tasks_not_started(allStoryTasks);
        }
      });
    });
    
    var collapsedData = getCollapsedData().split(',');
    if (typeof collapsedData === 'object') {
      $.each(collapsedData, function(index, value){
        if (typeof value === 'string') {
          $('#story-'+value).addClass('collapsed').find('.user-list,.state').hide();
        }
      });
    }
  }
  
  function onScrumBoardToggleClick(elmTarget, e) {
    var elmParent = elmTarget.parents('.story-group');
    elmParent.find('.user-list, .state').toggle();
    elmParent.toggleClass('collapsed');
    if (elmParent.hasClass('collapsed')) {
      addCollapsed(elmParent.attr('data-id'));
    }
    else {
      removeCollapsed(elmParent.attr('data-id'));
    }
  }
  
  function getCollapsedData() {
    var data = false;
    if (typeof localStorage !== 'undefined' ) {
      data = localStorage.getItem("collapsedList");
    }
    return data ? data : '';
  }
  function addCollapsed(id) {
    if (typeof(localStorage) !== 'undefined' ) {
      var data = getCollapsedData();
      var return_value = '';
      if (typeof data === 'string') {
        data = data.split(',');
        if ($.inArray(id, data) == -1) {
          data.push(id);
        }
        return_value = data.join(',');
      }
      else {
        return_value = id;
      }
      localStorage.setItem("collapsedList", return_value);
    }
  }
  function removeCollapsed(id) {
    if (typeof(localStorage) !== 'undefined' ) {
      var data = getCollapsedData();
      var return_value = '';
      if (typeof data === 'string') {
        data = data.split(',');
        var idx = data.indexOf(id);
        if (idx != -1) {
          data.splice(idx, 1);
        }
        return_value = data.join(',');
      }
      localStorage.setItem("collapsedList", return_value);
    }
  }

  Podio.Event.bind(Podio.Event.Types.init, onInit);
  Podio.Event.UI.bind('click', '#dashboard ul.stories > li', onDashBoardStoryClick);
  Podio.Event.UI.bind('click', '#switch-view', onDashBoardToggleClick);
  Podio.Event.UI.bind('click', '.story-group h2', onScrumBoardToggleClick);

})(window, jQuery);

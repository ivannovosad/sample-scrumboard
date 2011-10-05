(function (window, $, undefined) {

  function onInit() {
    $('ul.status li').tipsy({gravity: 'e'});
    $('.graph .target, .graph .actual').tipsy({gravity: 's'});
    $('.tooltip').tipsy({gravity: 'n'});
	
	var url = "/?/reload";
	if (sprint_id) {
		url += "/"+sprint_id;
	}
	$.PeriodicalUpdater(url, {
        method: 'get',          // method; get or post
        data: null,               // array of values to be passed to the page - e.g. {name: "John", greeting: "hello"}
        minTimeout: 30000,      // starting value for the timeout in milliseconds
        maxTimeout: 60000,      // maximum length of time between requests
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
    // Single story click: switch to board view and scroll to story
    onDashBoardToggleClick();
    var storyId = elmTarget.data('id');
    $('html,body').scrollTop($('#story-' + storyId).offset().top - 75);
  }
  function onDashBoardToggleClick(elmTarget, e) {
    $('#dashboard, #stories').toggle();
   initSingleStoryView();
   $('html, body').scrollTop(0);
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

    resize_stories();
    
    $(window).resize(function(){
      resize_stories();
    });

    $('.story-group').each(function(){
      var current_id = '#'+$(this).attr('id');
      $(current_id+' .story-item-state li').draggable({
        cancel: ".spinner",
        revert: "invalid", // when not dropped, the item will revert back to its initial position
        containment: current_id, // stick to demo-frame if present
        helper: "clone",
        cursor: "move",
        start: function(event, ui){
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

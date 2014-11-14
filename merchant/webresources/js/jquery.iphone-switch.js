/************************************************
*  jQuery iphoneSwitch plugin                   *
*                                               *
*  Author: Arunraj                       *
*  Date:   01/07/2013                             *
************************************************/

jQuery.fn.iphoneSwitch = function() {
    // create the switch
    return this.each(function() {
        var container;
        var image;
        var hiddenObj;
        // make the container
        container = jQuery('<div class="drag_bg "></div>');
        // make the switch image based on starting state
        image = jQuery('<span class="on_img"></span>');
        // insert into placeholder
        if(jQuery(this).val() == '1')
          {  
		  	jQuery(image).animate({
                left: 0
            });
             jQuery(this).parent('div').prev('span.on_txt').addClass('sel');
          }
        else
          {  jQuery(image).animate({
                left: 38
            });
            jQuery(image).addClass('off_img');
          }


        jQuery(container).html(jQuery(image)).insertAfter(jQuery(this));
        hiddenObj   =   jQuery(this);
        jQuery(container).click(function(e){
            var divLeft = e.pageX - $(this).offset().left;
			if(divLeft <= 25)
                {
					jQuery(image).animate({
                        left: 0
                    }, "fast", function() {
                        jQuery(hiddenObj).val(1);
                        jQuery(this).removeClass('off_img');
                        jQuery(hiddenObj).parent('div').prev('span.on_txt').addClass('sel');
						var checked = $('input[name="filter_dashboard[]"]:checked').length;
						var filter_dashboard_date = $('#filter_dashboard_date').val();
						var date_flag = $('#date_flag').val(); 
						if(checked || filter_dashboard_date || date_flag)
							loadGraph(1,1);
						else 
							loadGraph(0,1);
                    });
                }
                else if(divLeft > 25)
                {
					jQuery(image).animate({
                        left: 38
                    }, "fast", function() {
                        jQuery(hiddenObj).val(0);
                        jQuery(this).addClass('off_img');
                        jQuery(hiddenObj).parent('div').prev('span.on_txt').removeClass('sel');
						var checked = $('input[name="filter_dashboard[]"]:checked').length;
						var filter_dashboard_date = $('#filter_dashboard_date').val();
						var date_flag = $('#date_flag').val(); 
						if(checked || filter_dashboard_date || date_flag)
							loadGraph(1,0);
						else 
							loadGraph(0,0);
                    });
                }
        });
        jQuery(image).draggable({
            stop: function( event, ui ) {
                var pLeft = ui.position.left;
				if(pLeft <= 16)
                {
                    jQuery(this).animate({
                        left: 0
                    }, "fast", function() {
                        jQuery(hiddenObj).val(1);
                        jQuery(this).removeClass('off_img');
                        jQuery(hiddenObj).parent('div').prev('span.on_txt').addClass('sel');
						var checked = $('input[name="filter_dashboard[]"]:checked').length;
						var filter_dashboard_date = $('#filter_dashboard_date').val();
						var date_flag = $('#date_flag').val(); 
						if(checked || filter_dashboard_date || date_flag)
							loadGraph(1,1);
						else 
							loadGraph(0,1);
                    });
                }
                else if(pLeft > 16)
                {
                    jQuery(this).animate({
                        left: 38
                    }, "fast", function() {
                        jQuery(hiddenObj).val(0);
                        jQuery(this).addClass('off_img');
                        jQuery(hiddenObj).parent('div').prev('span.on_txt').removeClass('sel');
						var checked = $('input[name="filter_dashboard[]"]:checked').length;
						var filter_dashboard_date = $('#filter_dashboard_date').val();
						var date_flag = $('#date_flag').val(); 
						if(checked || filter_dashboard_date || date_flag)
							loadGraph(1,0);
						else 
							loadGraph(0,0);
						
                    });
                }
            },
            containment: "parent"
        });
		/*jQuery(image).bind('touchmove',function(e){
		      e.preventDefault();
		      //CODE GOES HERE
			  alert('----------------');
		});*/
        jQuery(this).removeClass('OnOffNew');
    });
};

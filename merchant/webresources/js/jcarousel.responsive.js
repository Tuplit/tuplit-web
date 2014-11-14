(function($) {
    $(function() {
        var jcarousel  = $('.jcarousel');
		var slideCount = 1;//items to be moved
		/* jQuery(window).resize(function() {
			alert('resize');
			carousel.reload();
		});*/
		jQuery(window).resize(function() {
				//alert('resize');
				var width = jcarousel.innerWidth();
				if (width >= 790) {
					slideCount	=	6;
                } else if (width >= 350) {
					slideCount	= 	3;
                } else if (width >= 320) {
					slideCount	=	1;
                } else{
					slideCount	= 	1;
				}
				//alert("=====>"+slideCount);
				$('.jcarousel-control-prev')
					.jcarouselControl({
						target: '-='+slideCount
					});
				$('.jcarousel-control-next')
					.jcarouselControl({
						target: '+='+slideCount
					});
				
		});
		jcarousel.on('jcarousel:reload jcarousel:create', function () {
               // alert('load');
				var width = jcarousel.innerWidth();
                if (width >= 790) {
                    width = (width-30) / 6;
					slideCount	=	6;
                } else if (width >= 350) {
                    width = width / 3;
					slideCount	= 	3;
                } else if (width >= 320) {
                    width = width / 2;
					slideCount	=	1;
                } /*else{
					slideCount	= 	1;
				}*/
				//  alert(slideCount);
				
                jcarousel.jcarousel('items').css('width', width + 'px');
				
            })
            .jcarousel({
                wrap: 'circular',
				animation : 1000
            });

        $('.jcarousel-control-prev')
					.jcarouselControl({
						target: '-='+slideCount
					});
				
				$('.jcarousel-control-next')
					.jcarouselControl({
						target: '+='+slideCount
					});

        $('.jcarousel-pagination')
            .on('jcarouselpagination:active', 'a', function() {
                $(this).addClass('active');
            })
            .on('jcarouselpagination:inactive', 'a', function() {
                $(this).removeClass('active');
            })
            .on('click', function(e) {
                e.preventDefault();
            })
            .jcarouselPagination({
                perPage: 1,
                item: function(page) {
                    return '<a href="#' + page + '">' + page + '</a>';
                }
            });
    });
})(jQuery);

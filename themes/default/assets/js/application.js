/*!
 * Created by ONESIDE.PL Jonatan Polak (jonatanpolak@gmail.com)
 * modified by Jan Grzegorowski mygekosale.pl (kontakt@mygekosale.pl)
 */

function checkDelivery() {
  $('.make-order').click(function(e) {
    if( !($('.order-method input[name="optionsRadios"]:checked').length > 0) ) {
      e.preventDefault();
      GError('Nie wybrano sposobu dostawy', 'Prosimy o wybór sposobu dostawy w celu złożenia zamówienia');
    }
  });
}

function qtySpinner(){
  if ($('.spinnerhide').length != 0){
    $('.spinnerhide').each(function(){
      var packagesize = parseInt($(this).attr('data-packagesize'));
      var places = ((packagesize % 1) > 0) ? 2 : 0;
      $(this).spinner({min: packagesize, width: 20, places: places, step: packagesize}).width(50);
    });
  }
  $('.product-quantity').unbind('keyup').keyup(function() {
    change($(this), $(this).data('productid'), null, $(this).val());
  });
  $('.product-quantity-att').unbind('keyup').keyup(function() {
    change($(this), $(this).data('productid'), $(this).data('attr'), $(this).val());
  });
  $('.product-quantity').next('.ui-spinner').unbind('click').bind('click', function() {
    change($(this), $(this).prev('input').data('productid'), null, $(this).prev('input').val());
  });
  $('.product-quantity-att').next('.ui-spinner').unbind('click').bind('click', function() {
    change($(this), $(this).prev('input').data('productid'), $(this).prev('input').data('attr'), $(this).prev('input').val());
  });

  var change = function(element, id, attr, val) {
    var $this = element;
    var timerId = $this.data("timerId");
    if (timerId) {
      window.clearTimeout(timerId);
    }
    $this.data("timerId", window.setTimeout(function(){xajax_changeQuantity(id, attr, val)}, 500));
  }
}

jQuery(function($) {
   
    var OnesideEngine = {
        plugins : {
            nav : function () {
                $('ul.nav li.dropdown').hover(function() {
                    $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn();
                }, function() {
                    $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut();
                });
            },
            
            login : function () {
                $('#loginTop').hover(function() {
                    $(this).find('.dropdown-toggle').addClass('active');
                    $('#loginTopContent').stop(true, true).delay(200).show();
                }, function () {
                    $(this).find('.dropdown-toggle').removeClass('active');
                    $('#loginTopContent').stop(true, true).delay(200).hide();
                });
            },
            
            basket : function () {
                $('#topBasket').hover(function() {
                    $('#topBasketContent').stop(true, true).delay(100).fadeIn();
                }, function () {
                    $('#topBasketContent').stop(true, true).delay(100).fadeOut();
                });
                qtySpinner();
            },
            
            productGallery : function () {
                if ($('#productInfo .image-slider').length != 0) {
                    $('ul', $('#productInfo .image-slider')).jcarousel({
                        scroll:1,
                        buttonNextHTML: null,
                        buttonPrevHTML: null,
                        initCallback: function(carousel){
                        	$('.slider-moveRight', $('#productInfo')).bind('click', function() {
                                carousel.next();
                                return false;
                            });

                            $('.slider-moveLeft', $('#productInfo')).bind('click', function() {
                                carousel.prev();
                                return false;
                            });
                        }
                    });

                    $('li a', $('#productInfo .image-slider ul')).bind('click', function(e) {
                        e.preventDefault();
                        var href = $(this).attr('href');
                        var large = $('#productInfo .image-large img');
                        $('#productInfo .image-large a').attr('href', href);
                        $('#productInfo .image-slider ul li.active').removeClass('active');
                        $(this).parent().addClass('active');
                        large.attr('src', href);
                        return false;
                    });
                }
            },
            
            star : function () {
                if ($('.star').length != 0)
                    $('.star').each(function(){
                    	$(this).raty({
                        	readOnly : $(this).hasClass('readonly') ? true : false,
                        	target: $(this).attr('data-target'),
                        	targetKeep: true,
                        	targetType : 'number',
                        	score: function() {
                        	    return $(this).attr('data-rating');
                        	},
                        	path: GCore.ASSETS_PATH + 'img/'
                        });
                    });
            },
            categoryTabs: function(){
            	$('#productTab li:first-child a').click();
            },
            load : function () {
                OnesideEngine.plugins.nav();
                OnesideEngine.plugins.login();
                OnesideEngine.plugins.basket();
                OnesideEngine.plugins.productGallery();
                OnesideEngine.plugins.star();
                OnesideEngine.plugins.categoryTabs();
            }
        }
    }
    
    OnesideEngine.plugins.load();
    
    checkDelivery();
});

/*
* ALERT
*/

var GAlert = function(sTitle, sMessage, oParams) {
	if (sMessage == undefined) {
		sMessage = '';
	}
	
	var iAlertId = GAlert.Register();
	var sModal = $('<div/>').addClass('modal hide fade').attr('id','alert-'+iAlertId);
	sModal.append($('<div/>').addClass('modal-header').html('<button type="button" class="close" data-dismiss="modal">×</button><h3>' + sTitle + '</h3>'));
	sModal.append($('<div/>').addClass('modal-body').html('<p>' + sMessage + '</p>'));
	sModal.modal('show');
	return iAlertId;
};

var GWarning = function(sTitle, sMessage, oParams) {
	if (oParams == undefined) {
		oParams = {};
	}
	oParams.iType = GAlert.TYPE_WARNING;
	return GAlert(sTitle, sMessage, oParams);
};

var GError = function(sTitle, sMessage, oParams) {
	if (oParams == undefined) {
		oParams = {};
	}
	oParams.iType = GAlert.TYPE_ERROR;
	return GAlert(sTitle, sMessage, oParams);
};

var GMessage = function(sTitle, sMessage, oParams) {
	if (oParams == undefined) {
		oParams = {};
	}
	oParams.iType = GAlert.TYPE_MESSAGE;
	return GAlert(sTitle, sMessage, oParams);
};

GAlert.Destroy = function(iAlertId) {
	if (GAlert.sp_dHandler != undefined) {
		GAlert.sp_dHandler.Destroy(iAlertId);
	}
};

GAlert.DestroyThis = function(eEvent) {
	GAlert.Destroy($(this));
};
	
GAlert.DestroyAll = function() {
	if (GAlert.sp_dHandler != undefined) {
		GAlert.sp_dHandler.DestroyAll();
	}
};

GAlert.Register = function() {
	return GAlert.s_iCounter++;
};

GAlert.sp_dHandler;
GAlert.s_iCounter = 0;

GAlert.TYPE_WARNING = 0;
GAlert.TYPE_ERROR = 1;
GAlert.TYPE_MESSAGE = 2;
GAlert.TYPE_PROMPT = 3;


/*
* CORE
*/

var oDefaults = {
	iCookieLifetime: 30,
	sDesignPath: '',
	sController: 'main',
	sCartRedirect: ''
};

GCore = function(oParams) {
	GCore.p_oParams = oParams;
	GCore.DESIGN_PATH = GCore.p_oParams.sDesignPath;
	GCore.ASSETS_PATH = GCore.p_oParams.sAssetsPath;
	GCore.CONTROLLER = GCore.p_oParams.sController;
	GCore.COOKIE_LIFETIME = GCore.p_oParams.iCookieLifetime;
	GCore.CART_REDIRECT = GCore.p_oParams.sCartRedirect;
};

GCore.NULL = 'null';
GCore.s_afOnLoad = [];
GCore.GetArgumentsArray = function(oArguments) {
	var amArguments = [];
	for (var i = 0; i < oArguments.length; i++) {
		amArguments[i] = oArguments[i];
	}
	return amArguments;
};

GCore.Duplicate = function(oA, bDeep) {
	var oB = $.extend((bDeep == true), {}, oA);
	return oB;
};

GCore.OnLoad = function(fTarget) {
	GCore.s_afOnLoad.push(fTarget);
};

GCore.Init = function() {
	for (var i = 0; i < GCore.s_afOnLoad.length; i++) {
		GCore.s_afOnLoad[i]();
	}
};

GCore.ExtendClass = function(fBase, fChild, oDefaults) {
	var fExtended = function() {
		var aBaseArguments = [];
		for (var i = 0; i < arguments.length; i++) {
			aBaseArguments.push(arguments[i]);
		}
		var result = fBase.apply(this, aBaseArguments);
		if (result === false) {
			return result;
		}
		fChild.apply(this, arguments);
		this.m_oOptions = $.extend(true, GCore.Duplicate(oDefaults, true), arguments[0]);
		return this;
	};
	for(var i in fBase.prototype) {
		fExtended.prototype[i] = fBase.prototype[i];
	}
	return fExtended;
};

GCore.ObjectLength = function(oObject) {
	var iLength = 0;
	for (var i in oObject) {
		if (isNaN(i)) {
			continue;
		}
		iLength++;
	}
	return iLength;
};

GCore.StartWaiting = function() {
	$('body').css({
		cursor: 'wait'
	});
};

GCore.StopWaiting = function() {
	$('body').css({
		cursor: 'auto'
	});
};

var GEventHandler = function(fHandler) {
	var fSafeHandler = function(eEvent) {
		try {
			if (eEvent.data) {
				for (var i in eEvent.data) {
					this[i] = eEvent.data[i];
				}
			}
			return fHandler.apply(this, arguments);
		}
		catch (xException) {
			GException.Handle(xException);
			return false;
		}
	};
	return fSafeHandler;
};

/*
 * GCookie 
 */

GCookie = function(name, value, options) {
    if (typeof value != 'undefined') {
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString();
        }
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { 
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

/*
* CALLBACK
*/

var GCallback = function(fHandler, oParams) {
	if (oParams == undefined) {
		oParams = {};
	}
	var i = GCallback.s_iReferenceCounter++;
	GCallback.s_aoReferences[i] = {
		fHandler: fHandler,
		oParams: oParams
	};
	GCallback['Trigger_' + i] = function() {
		GCallback.Invoke(i, GCore.GetArgumentsArray(arguments));
	};
	return 'GCallback.Trigger_' + i + '';
};

GCallback.s_iReferenceCounter = 0;
GCallback.s_aoReferences = {};

GCallback.Invoke = function(iReference, amArguments) {
	if (amArguments[0] == undefined) {
		amArguments[0] = {};
	}
	var oReference = GCallback.s_aoReferences[iReference];
	if (oReference != undefined) {
		oReference.fHandler.call(this, $.extend(oReference.oParams, amArguments[0]));
	}
	delete GCallback.s_aoReferences[iReference];
};


/*
* EXCEPTION
*/

var GException = function(sMessage) {
	this.m_sMessage = sMessage;
	this.toString = function() {
		return this.m_sMessage;
	};
};

GException.Handle = function(xException) {
	new GAlert(GException.Language['exception_has_occured'], xException);
	throw xException; // for debugging
};

GException.Language = {
	exception_has_occured: 'Wystąpił błąd!'
};


/*
* PLUGIN
*/

var GPlugin = function(sPluginName, oDefaults, fPlugin) {
	
	(function($) {
	
		var oExtension = {};
		oExtension[sPluginName] = function(oOptions) {
			if ($(this).hasClass(sPluginName)) {
				return;
			}
			oOptions = $.extend(GCore.Duplicate(oDefaults), oOptions);
			return this.each(function() {
				this.m_oOptions = oOptions;
				this.m_iId = GPlugin.s_iCounter++;
				GPlugin.s_oInstances[this.m_iId] = this;
				this.m_oParams = {};
				this._GetClass = function(sClassName) {
					var sClass = this.m_oOptions.oClasses['s' + sClassName + 'Class'];
					if (sClass == undefined) {
						return '';
					}
					else {
						return sClass;
					}
				};
				this._GetImage = function(sImageName) {
					var sImage = this.m_oOptions.oImages['s' + sImageName];
					if (sImage == undefined) {
						return '';
					}
					else {
						return GCore.DESIGN_PATH + sImage;
					}
				};
				try {
					if($(this).attr('class') != undefined){
						var asParams = $(this).attr('class').match(/G\:\w+\=\S+/g);
						if (asParams != undefined) {
							for (var i = 0; i < asParams.length; i++) {
								var asParamData = asParams[i].match(/G:(\w+)\=(\S+)/);
								this.m_oParams[asParamData[1]] = asParamData[2];
							}
						}
					}
					$(this).addClass(sPluginName);
					fPlugin.apply(this, [this.m_oOptions]);
				}
				catch(xException) {
					throw xException;
					GException.Handle(xException);
				}
			});
		};
		$.fn.extend(oExtension);
		fPlugin.GetInstance = GPlugin.GetInstance;
	
	})(jQuery);
	
};

GPlugin.s_iCounter = 0;
GPlugin.s_oInstances = {};

GPlugin.GetInstance = function(iId) {
	if (GPlugin.s_oInstances[iId] != undefined) {
		return GPlugin.s_oInstances[iId];
	}
	throw new GException('Requested instance (' + iId + ') not found.');
	return false;
};


/*
* ACCORDION
*/

var oDefaults = {
	oClasses: {
	}
};

var GAccordion = function() {
	
	var gThis = this;
	
	this._Constructor = function() {
		$(gThis).children('li').not(':first').addClass('collapsed');
		$(gThis).find('h4').click(function(eEvent) {
			if ($(this).closest('li').hasClass('collapsed')) {
				$(gThis).children('li').addClass('collapsed');
				$(this).closest('li').removeClass('collapsed');
				$(gThis).closest('.GLayoutBoxes').trigger('positionsChanged');
				return false;
			}
			return true;
		});
		$(gThis).closest('.GLayoutBoxes').trigger('positionsChanged');
	};
	
	gThis._Constructor();
	
};

new GPlugin('GAccordion', oDefaults, GAccordion);

/*
* LOADING
* Adds a loading indicator for the desired DOM element.
*/

var oDefaults = {
	oClasses: {
	},
	sBackground: '#fff',
	fOpacity: .75,
	iZIndex: 1001
};

var GLoading = function() {
	
	var gThis = this;
	
	gThis.m_jOverlay;
	gThis.m_jIcon;
	
	gThis._Constructor = function() {
		gThis.m_jOverlay = $('<div class="GLoading"/>').css({
			display: 'block',
			position: 'absolute',
			left: $(gThis).offset().left,
			top: $(gThis).offset().top,
			width: $(gThis).width(),
			height: $(gThis).height(),
			zIndex: gThis.m_oOptions.iZIndex,
			opacity: 0,
			background: gThis.m_oOptions.sBackground
		});
		gThis.m_jIcon = $('<div class="GLoading_icon"/>').css({
			display: 'block',
			position: 'absolute',
			left: $(gThis).offset().left,
			top: $(gThis).offset().top,
			width: $(gThis).width(),
			height: $(gThis).height(),
			zIndex: gThis.m_oOptions.iZIndex,
			opacity: 0
		});
		$('body').append(gThis.m_jOverlay).append(gThis.m_jIcon);
		gThis.m_jOverlay.animate({
			duration: 500,
			opacity: gThis.m_oOptions.fOpacity
		});
		gThis.m_jIcon.animate({
			duration: 500,
			opacity: 1
		});
		$(gThis).resize(GEventHandler(function(eEvent) {
			gThis.m_jOverlay.css({
				width: $(gThis).width(),
				height: $(gThis).height()
			});
			gThis.m_jIcon.css({
				width: $(gThis).width(),
				height: $(gThis).height()
			});
		}));
	};
	
	gThis.StopLoading = function() {
		gThis.m_jOverlay.stop(true, true).animate({
			duration: 500,
			opacity: 0
		}, function() {
			$(this).remove();
		});
		gThis.m_jIcon.stop(true, true).animate({
			duration: 500,
			opacity: 0
		}, function() {
			$(this).remove();
		});
		$(gThis).removeClass('GLoading');
	};
	
	gThis._Constructor();
	
};

GLoading.Stop = function(jNode) {
	return jNode.get(0).StopLoading();
};

GLoading.RemoveAll = function() {
	$('.GLoading, GLoading_icon').remove();
};

new GPlugin('GLoading', oDefaults, GLoading);

/*
* PRODUCT ATTRIBUTES
*/

var oDefaults = {
	
};

var GProductAttributes = function(oOptions) {
	
	var gThis = this;
	
	gThis._Constructor = function() {
		
		$('.attributes').change(function(){
			gThis.UpdateAttributes(oOptions);
		});
	};
	
	gThis.UpdateAttributes = function(oOptions) {
		gThis.aoAttributes = [];
		$(".attributes").find('option:selected').each(function() {
			gThis.aoAttributes.push(this.value);
		});
		gThis.aoAttributes.sort(function(a,b){return a-b});
		gThis.sCheckedVariant = gThis.aoAttributes.join(',');
		if(oOptions.aoVariants[gThis.sCheckedVariant] == undefined){
			$('.available').hide();
			$('.noavailable').show();
		}else{
			if(oOptions.bTrackStock != undefined){
				if(oOptions.bTrackStock == 1 && oOptions.aoVariants[gThis.sCheckedVariant].stock == 0){
					$('.available').hide();
					$('.noavailable').show();
				}else{
					$('.available').show();
					$('.noavailable').hide();
				}
			}
			$('#attributevariants').val(oOptions.aoVariants[gThis.sCheckedVariant].setid);
			$('#availablestock').val(oOptions.aoVariants[gThis.sCheckedVariant].stock);
			$('#stockavailablity').text(oOptions.aoVariants[gThis.sCheckedVariant].stock);
			$('#variantprice').val(oOptions.aoVariants[gThis.sCheckedVariant].sellprice);
			$('#changeprice').text(oOptions.aoVariants[gThis.sCheckedVariant].sellprice);
			$('#changeprice-netto').text(oOptions.aoVariants[gThis.sCheckedVariant].sellpricenetto);
			$('#changeprice-old').text(oOptions.aoVariants[gThis.sCheckedVariant].sellpriceold);
			$('#changeprice-netto-old').text(oOptions.aoVariants[gThis.sCheckedVariant].sellpricenettoold);
			if(oOptions.aoVariants[gThis.sCheckedVariant].photos.normal != undefined && oOptions.aoVariants[gThis.sCheckedVariant].photos.normal != ''){
				$('.image-large a').attr('href', oOptions.aoVariants[gThis.sCheckedVariant].photos.large);
				$('.image-large img').attr('src', oOptions.aoVariants[gThis.sCheckedVariant].photos.normal);
			}
			if(oOptions.aoVariants[gThis.sCheckedVariant].availablity != undefined && oOptions.aoVariants[gThis.sCheckedVariant].availablity != ''){
				$('#availablity').text(oOptions.aoVariants[gThis.sCheckedVariant].availablity);
			}
      else {
				$('#availablity').text($('#availablity').attr('data-default-availablity'));
      }
		}
		
	};
	
	gThis._Constructor();
	gThis.UpdateAttributes(oOptions);
	
};

new GPlugin('GProductAttributes', oDefaults, GProductAttributes);


/*
* SEARCH
* Live Search 
*/

var oDefaults = {
	oClasses: {
	},
	iDuration: 200,
	sPlaceholder: 'live-search-results',
  minTextLength: 3
};

var GSearch = function() {
	
	var gThis = this;
	gThis._Constructor = function() {
		gThis.m_jInput = $(this);
		gThis.m_oOptions.sDefaultText = gThis.m_jInput.attr('placeholder');
		gThis.sLastValue = gThis.m_jInput.val();
		gThis.m_jInput.attr('autocomplete','off');
		gThis.m_jLiveSearch = $('<div>').attr('id', gThis.m_oOptions.sPlaceholder).hide().slideUp(0);
    $('#livesearch').append(gThis.m_jLiveSearch);
		$(document.body).click(function(event){
			var clicked = $(event.target);
			if(!(clicked.is('#'+gThis.m_oOptions.sPlaceholder) || clicked.parents('#' + gThis.m_oOptions.sPlaceholder).length || clicked.is('input'))){
        gThis.HideLiveSearch();
			}
		});
		gThis.OnFocus();
		gThis.OnBlur();
		gThis.OnClick();
		
		gThis.m_jInput.typeWatch({
			callback: function(){
				gThis.OnTypingFinished();
			},
			minTextLength : 0
		});
	};
	
	gThis.RepositionLiveSearch = function() {
		var liveSearchPaddingBorderHoriz = parseInt(gThis.m_jLiveSearch.css('paddingLeft'), 10) + parseInt(gThis.m_jLiveSearch.css('paddingRight'), 10) + parseInt(gThis.m_jLiveSearch.css('borderLeftWidth'), 10) + parseInt(gThis.m_jLiveSearch.css('borderRightWidth'), 10);
		var tmpOffset = gThis.m_jInput.offset();
		var inputDim = {
			right: tmpOffset.left,
			top: tmpOffset.top,
			width: gThis.m_jInput.outerWidth(),
			height: gThis.m_jInput.outerHeight()
		};
		
		inputDim.topPos = inputDim.top + inputDim.height;
		inputDim.totalWidth = inputDim.width - liveSearchPaddingBorderHoriz;

		gThis.m_jLiveSearch.css({
			position:	'absolute',
			right:	'0px',
		});
	};
	
	gThis.ShowLiveSearch = function() {
		if (gThis.value.length >= gThis.m_oOptions.minTextLength){
      gThis.RepositionLiveSearch();	
      $(window).unbind('resize', gThis.RepositionLiveSearch).bind('resize', gThis.RepositionLiveSearch);
      gThis.m_jLiveSearch.slideDown(gThis.m_oOptions.iDuration);
    }
    else {
      gThis.HideLiveSearch();
    }
	};
	
	gThis.HideLiveSearch = function() {
		gThis.m_jLiveSearch.slideUp(gThis.m_oOptions.iDuration);
	};
	
	gThis.OnFocus = function() {
		gThis.m_jInput.focus(function() {
			if(gThis.m_jInput.val() == gThis.m_oOptions.sDefaultText) $(this).val("");
		});
		if (gThis.m_jLiveSearch.html() == ''){
			gThis.sLastValue = '';
			gThis.m_jInput.keyup();
		}else{
			setTimeout(gThis.ShowLiveSearch(),1);
		}
	};
	
	gThis.OnClick = function() {
		gThis.m_jInput.click(function(){
			setTimeout(gThis.ShowLiveSearch(),1);
		});
	};
	
	gThis.OnBlur = function() {
		gThis.m_jInput.blur(function() {
			if(gThis.m_jInput.val() == '') $(this).val(gThis.m_oOptions.sDefaultText);
		});
		gThis.ShowLiveSearch();
	};
	
	gThis.OnTypingFinished = function() {
    if(gThis.m_jInput.val() != gThis.m_oOptions.sDefaultText) {
      if(gThis.sLastValue == gThis.m_jInput.val()) {
        gThis.ShowLiveSearch();
      }
      else if(gThis.value.length >= gThis.m_oOptions.minTextLength) {
        gThis.LoadResults();
      }
      else {
        gThis.HideLiveSearch();
      }
    }
	}; 
	
	gThis.LoadResults = function() {
		gThis.sLastValue = gThis.m_jInput.val();
    gThis.m_jLiveSearch.html(xajax_doSearchQuery({
      'form': gThis.m_oOptions.form.serialize(),
      'container': gThis.m_oOptions.sPlaceholder,
    }));
    gThis.ShowLiveSearch();
	};
	
	gThis._Constructor();
	
};

new GPlugin('GSearch', oDefaults, GSearch);

/*
* SELECT
* Beautiful select-field replacement.
*/

var oDefaults = {
	oClasses: {
		sFauxClass: 'faux'
	}
};

var GSelect = function() {
	
	var gThis = this;
	
	this._Constructor = function() {
		if (this.bSelectInitialized) {
			return;
		}
		this.bSelectInitialized = true;
		$(this).parent().find('select').css('opacity', 0);
		$(this).parent().append('<span class="' + gThis._GetClass('Faux') + '"><span>' + $(this).find('option:selected').text() + '</span></span>');
		$(this).change(function() {
			$(this).parent().find('.' + gThis._GetClass('Faux') + ' span').text($(this).find('option:selected').text());
		});
	};
	
	gThis._Constructor();
	
};

new GPlugin('GSelect', oDefaults, GSelect);

/*
* LAYOUT COLUMN
*/

var GLayoutColumn = function(oOptions) {
	
	var gThis = this;
	
	gThis.m_oOptions = $.extend(true, GCore.Duplicate(GLayoutColumn.DEFAULTS, true), oOptions);
	
};

GLayoutColumn.WIDTH_AUTO = 0;

GLayoutColumn.DEFAULTS = {
	iWidth: 0,
	asBoxes: []
};


/*
* LAYOUT BOX
*/

var GLayoutBox = function(oOptions) {
	
	var gThis = this;
	
	gThis.m_oOptions = $.extend(true, GCore.Duplicate(GLayoutBox.DEFAULTS, true), oOptions);
	
	gThis.p_oPosition = {c:0, r:0};
	gThis.m_bCollapsed = false;
	gThis.m_bClosed = false;
	gThis.m_iSpan = 1;
	gThis.m_sType;
	gThis.m_oObject = GCore.NULL;
	gThis.m_aiWidthClasses = [];
	
	gThis._Constructor = function() {
		gThis.m_oOptions.jNode.data('oBox', gThis);
		gThis._ExtractOptions();
		gThis._InitializeEvents();
	};
	
	gThis.ChangeParams = function(oBoxModification) {
		if (!$.isEmptyObject(oBoxModification)) {
			gThis.m_oOptions.oParamsFromHash = $.extend(gThis.m_oOptions.oParamsFromHash, oBoxModification);
			var oModification = {};
			oModification[gThis.m_oOptions['sName']] = gThis.m_oOptions.oParamsFromHash;
			gThis.m_oOptions.oLayoutBoxes.ChangeParams(oModification);
			gThis.m_oOptions.jNode.triggerHandler('boxHashParamsChange');
		}
		return gThis.m_oParamsFromHash;
	};
	
	gThis.OnLinkClick = GEventHandler(function(eEvent) {
		gThis.ParseLink($(this).attr('href'));
		return false;
	});
	
	gThis.ParseLink = function(sLink) {
		gThis.m_oOptions.oLayoutBoxes.ChangeParams(gThis.m_oOptions.oLayoutBoxes._GetParamsFromHref(sLink));
		return false;
	};
	
	gThis._ExtractOptions = function() {
		var sClass = gThis.m_oOptions.jNode.attr('class');
		var oMatch;
		var rRE = /layout-box-option-([^- ]+)-([^- ]+)/g;
		while ((oMatch = rRE.exec(sClass)) !== null) {
			switch (oMatch[1]) {
				case 'header':
					gThis.m_oOptions.bShowHeader = (oMatch[2] == 'true');
					break;
				case 'stretch':
					gThis.m_oOptions.iSpan = parseInt(oMatch[2]);
					break;
			}
		}
		var rRE2 = /layout-box-type-([^ ]+)/;
		oMatch = rRE2.exec(sClass);
		if (oMatch != null) {
			gThis.m_sType = oMatch[1];
		}
	};
	
	gThis._InitializeEvents = function() {
		gThis.m_oOptions.jNode.bind('distributed', gThis.OnDistributed);
		gThis.m_oOptions.jNode.find('a[href*=",p="]').click(gThis.OnLinkClick);
		gThis.m_oOptions.jNode.bind('resize', gThis._CheckWidthClasses);
	};
	
	gThis.SetWidthClasses = function(aiClasses) {
		gThis.m_aiWidthClasses = aiClasses;
	};
	
	gThis._CheckWidthClasses = GEventHandler(function(eEvent) {
		gThis.m_oOptions.jNode.attr('class', gThis.m_oOptions.jNode.attr('class').replace(/layout-box-width-class-\d+/g, ''));
		if (!gThis.m_aiWidthClasses.length) {
			return;
		}
		var iWidth = gThis.m_oOptions.jNode.width();
		for (var i = 0; i < gThis.m_aiWidthClasses.length; i++) {
			if (gThis.m_aiWidthClasses[i] > iWidth) {
				break;
			}
		}
		gThis.m_oOptions.jNode.addClass('layout-box-width-class-' + i);
	});
	
	gThis._InitializeBox = function() {
		if (gThis.m_sType != undefined) {
			var sFunctionName = 'GLayoutBoxType' + gThis.m_sType.replace(/\w+/g, function(a) {
				return a.charAt(0).toUpperCase() + a.substr(1).toLowerCase();
			}).replace(/-/, '');
			if (window[sFunctionName]) {
				gThis.m_oObject = new window[sFunctionName]({
					jNode: gThis.m_oOptions.jNode,
					oLayoutBox: gThis
				});
			}
		}
	};
	
	gThis._InitializeIcons = function() {
		var jBox = gThis.m_oOptions.jNode;
		jBox.find('.layout-box-icons .layout-box-icon, .layout-box-resize').remove();
		if (gThis.m_oOptions.bCollapsible) {
			if (gThis.m_bCollapsed) {
				gThis.m_oOptions.jNode.addClass('layout-box-collapsed');
				jBox.find('.layout-box-icons').prepend($('<span class="layout-box-uncollapse layout-box-icon"/>').click(gThis.Uncollapse));
				jBox.find('.layout-box-header h1,.layout-box-header h3').click(gThis.Uncollapse).css('cursor', 'move');
			}
			else {
				gThis.m_oOptions.jNode.removeClass('layout-box-collapsed');
				jBox.find('.layout-box-icons').prepend($('<span class="layout-box-collapse layout-box-icon"/>').click(gThis.Collapse));
				jBox.find('.layout-box-header h1, .layout-box-header h3').click(gThis.Collapse).css('cursor', 'move');
			}
		}
	};
	
	gThis.m_iInitX = 0;
	gThis.m_iCurrentSpan = 1;
	gThis.m_iFormerWidth = 0;
	gThis.m_jBoxHelper;
	
	gThis.OnStartResize = GEventHandler(function(eEvent) {
		gThis.m_oOptions.jNode.parent().css('z-index', 1000);
		$(gThis.m_oOptions.oLayoutBoxes).find('.ui-sortable').sortable('disable');
		$('body').mousemove(gThis.OnResize).mouseup(gThis.OnStopResize);
		gThis.m_iInitX = eEvent.pageX;
		gThis.m_jBoxHelper = $('<div class="layout-box-place"/>');
		gThis.m_oOptions.jNode.after(gThis.m_jBoxHelper);
		var jBox = gThis.m_oOptions.jNode;
		gThis.m_iFormerWidth = jBox.width();
		gThis.m_jBoxHelper.css({
			position: 'absolute',
			left: jBox.offset().left - jBox.parent().offset().left,
			top: jBox.offset().top - jBox.parent().offset().top,
			width: jBox.width(),
			height: jBox.height(),
			zIndex: 1000
		});
		return false;
	});
	
	gThis.OnResize = GEventHandler(function(eEvent) {
		var iDifference = eEvent.pageX - gThis.m_iInitX;
		var iOldSpan = gThis.m_iCurrentSpan;
		if (iDifference > gThis.m_oOptions.oLayoutBoxes.m_oOptions.iSpace) {
			gThis.m_iCurrentSpan = gThis.m_iSpan + 1;
		}
		else if (iDifference < -gThis.m_oOptions.oLayoutBoxes.m_oOptions.iSpace) {
			gThis.m_iCurrentSpan = gThis.m_iSpan - 1;
		}
		if (iOldSpan != gThis.m_iCurrentSpan) {
			gThis.m_oOptions.jNode.data('iSpan', gThis.m_iCurrentSpan);
			$(gThis.m_oOptions.oLayoutBoxes).triggerHandler('positionsChanged');
		}
		gThis.m_jBoxHelper.width(gThis.m_oOptions.jNode.width());
		gThis.m_jBoxHelper.height(gThis.m_oOptions.jNode.height());
		if (document.selection) {
			document.selection.createRange().execCommand('Unselect');
		}
	});
	
	gThis.OnStopResize = GEventHandler(function(eEvent) {
		$('body').unbind('mousemove', gThis.OnResize);
		$('body').unbind('mouseup', gThis.OnStopResize);
		gThis.ChangeSpan(gThis.m_iCurrentSpan);
		gThis.m_jBoxHelper.remove();
		$(gThis.m_oOptions.oLayoutBoxes).find('.ui-sortable').sortable('enable');
		if (document.selection) {
			document.selection.createRange().execCommand('Unselect');
		}
	});
	
	gThis.OnDistributed = GEventHandler(function(eEvent) {
		gThis.m_oOptions.jNode.triggerHandler('resize');
	});
	
	gThis.Close = function() {
		alert('gThis.Close');
		gThis.m_bClosed = true;
		gThis.m_oOptions.jNode.fadeOut(200, function() {
			$(gThis.m_oOptions.oLayoutBoxes).triggerHandler('distributionChanged');
		});
	};
	
	gThis.Show = function() {
		alert('gThis.Show');
		gThis.m_bClosed = false;
		gThis.m_oOptions.jNode.css('display', 'block');
		gThis.m_oOptions.jNode.appendTo($(gThis.m_oOptions.oLayoutBoxes).find('.layout-column:first'));
		$(gThis.m_oOptions.oLayoutBoxes).triggerHandler('distributionChanged');
	};
	
	gThis.Collapse = function() {
		if (gThis.m_oOptions.jNode.data('bDontCollapse')) {
			return;
		}
		gThis.m_oOptions.jNode.addClass('layout-box-collapsed');
		gThis.m_bCollapsed = true;
		gThis._InitializeIcons();
		gThis.m_oOptions.jNode.find('.layout-box-content').slideUp(gThis.m_oOptions.iAnimationTime, function() {
			$(gThis.m_oOptions.oLayoutBoxes).triggerHandler('distributionChanged');
			$(window).triggerHandler('resize');
			if (!gThis.m_oOptions.bShowHeader) {
				gThis.m_oOptions.jNode.removeClass('layout-box-option-header-false');
			}
		});
		gThis.m_oOptions.jNode.triggerHandler('collapse');
		gThis.m_oOptions.jNode.triggerHandler('resize');
	};
	
	gThis.InitCollapse = function() {
		if (gThis.m_oOptions.jNode.data('bDontCollapse')) {
			return;
		}
		gThis.m_oOptions.jNode.addClass('layout-box-collapsed');
		gThis.m_bCollapsed = true;
		gThis._InitializeIcons();
		gThis.m_oOptions.jNode.find('.layout-box-content').slideUp(gThis.m_oOptions.iAnimationTime, function() {
			$(window).triggerHandler('resize');
			if (!gThis.m_oOptions.bShowHeader) {
				gThis.m_oOptions.jNode.removeClass('layout-box-option-header-false');
			}
		});
		gThis.m_oOptions.jNode.triggerHandler('collapse');
		gThis.m_oOptions.jNode.triggerHandler('resize');
	};
	
	gThis.Uncollapse = function() {
		if (gThis.m_oOptions.jNode.data('bDontCollapse')) {
			return;
		}
		gThis.m_oOptions.jNode.removeClass('layout-box-collapsed');
		gThis.m_bCollapsed = false;
		if (!gThis.m_oOptions.bShowHeader) {
			gThis.m_oOptions.jNode.addClass('layout-box-option-header-false');
		}
		gThis._InitializeIcons();
		gThis.m_oOptions.jNode.find('.layout-box-content').slideDown(gThis.m_oOptions.iAnimationTime, function() {
			$(gThis.m_oOptions.oLayoutBoxes).triggerHandler('distributionChanged');
			$(window).triggerHandler('resize');
		});
		gThis.m_oOptions.jNode.triggerHandler('uncollapse');
		gThis.m_oOptions.jNode.triggerHandler('resize');
	};
	
	gThis.InitUncollapse = function() {
		if (gThis.m_oOptions.jNode.data('bDontCollapse')) {
			return;
		}
		gThis.m_oOptions.jNode.removeClass('layout-box-collapsed');
		gThis.m_bCollapsed = false;
		if (!gThis.m_oOptions.bShowHeader) {
			gThis.m_oOptions.jNode.addClass('layout-box-option-header-false');
		}
		gThis._InitializeIcons();
		gThis.m_oOptions.jNode.find('.layout-box-content').slideDown(gThis.m_oOptions.iAnimationTime, function() {
			$(window).triggerHandler('resize');
		});
		gThis.m_oOptions.jNode.triggerHandler('uncollapse');
		gThis.m_oOptions.jNode.triggerHandler('resize');
	};
	
	gThis.Expand = function() {
		gThis.ChangeSpan(gThis.m_iSpan + 1);
	};
	
	gThis.Retract = function() {
		gThis.ChangeSpan(gThis.m_iSpan - 1);
	};
	
	gThis.ChangeSpan = function(iSpan) {
		gThis.m_iSpan = Math.max(1, iSpan);
		gThis.m_oOptions.jNode.data('iSpan', gThis.m_iSpan);
		gThis._InitializeIcons();
		gThis.m_oOptions.jNode.triggerHandler('resize');
	};
	
	gThis._Constructor();
	
};

GLayoutBox.DEFAULTS = {
	sName: '',
	sCaption: '',
	bShowHeader: true,
	iSpan: 1,
	jNode: GCore.NULL,
	iAnimationTime: 50,
	oLayoutBoxes: GCore.NULL,
	oParamsFromHref: {},
	oParamsFromHash: {}
};


/*
* LAYOUT BOXES
*/

var oDefaults = {
	aoColumns: [],
	sLayoutHash: '',
	iSpace: 20,
	iMinWidth: 800
};

var GLayoutBoxes = function() {
	
	var gThis = this;
	
	gThis.m_iColumns = 0;
	gThis.m_iBoxes = 0;
	gThis.m_oBoxes = {};
	gThis.m_aasBoxesDistribution = {};
	gThis.m_bDontSave = false;
	gThis.m_iCurrentlySortedOriginalSpan = 1;
	gThis.m_iCurrentlySortedSpan = 1;
	gThis.m_iOffsetTop = 0;
	gThis.m_bInvalidPlace = false;
	gThis.m_jClosedBoxes;
	gThis.m_jBoxesOptions;
	gThis.m_oBoxesOptions;
	gThis.m_oParamsFromHref;
	gThis.m_oParamsFromHash;
	
	gThis._Constructor = function() {
		gThis.m_oParamsFromHref = gThis._GetParamsFromHref(location.href);
		gThis.m_oParamsFromHash = $.extend({}, gThis.m_oParamsFromHref, gThis._GetParamsFromHash(location.hash));
		gThis.m_iColumns = gThis.m_oOptions.aoColumns.length;
		gThis._CreateBoxes();
		gThis._CreateColumns();
		gThis._InitializeEvents();
		gThis._DistributeParams();
		gThis._InitializeBoxes();
		gThis.ResetDistribution();
	};
	
	gThis._GetParamsFromHref = function(sSource, sBoxId) {
		var mMatch = sSource.match(/,p=([^,#?]+)/);
		if (mMatch == null) {
			return {};
		}
		if (sBoxId == undefined) {
			return gThis._ExtractParams(mMatch[1]);
		}
		return gThis._ExtractParams(mMatch[1])[sBoxId];
	};
	
	gThis._GetParamsFromHash = function(sSource, sBoxId) {
		if ((sSource == undefined) || !sSource.length) {
			return {};
		}
		var mMatch = (',' + sSource).match(/,#?p=([^,#?]+)/);
		if (mMatch == null) {
			return {};
		}
		if (sBoxId == undefined) {
			return gThis._ExtractParams(mMatch[1]);
		}
		return gThis._ExtractParams(mMatch[1])[sBoxId];
	};
	
	gThis._ExtractParams = function(sSource) {
		return $.parseJSON(Base64.decode(sSource));
	};
	
	gThis.ChangeParams = function(oModification) {
		if (!$.isEmptyObject(oModification)) {
			gThis.m_oParamsFromHash = $.extend(gThis.m_oParamsFromHash, oModification);
			gThis._DistributeParams();
			$(gThis).triggerHandler('hashParamsChange');
		}
		return gThis.m_oParamsFromHash;
	};
	
	gThis._DistributeParams = function() {
		for (var i in gThis.m_oParamsFromHash) {
			if (gThis.m_oBoxes[i] != undefined) {
				gThis.m_oBoxes[i].m_oOptions.oParamsFromHash = gThis.m_oParamsFromHash[i];
				gThis.m_oBoxes[i].m_oOptions.jNode.triggerHandler('boxParamsChange');
			}
		}
	};
	
	gThis.ResetDistribution = function() {
		gThis.m_bDontSave = true;
		gThis.m_aasBoxesDistribution = {};
		var bLoadDefault = true;
		var aaoDistribution = {};
		if (GCookie(GLayoutBoxes.COOKIE_NAME_HASHCODE + GCore.CONTROLLER) != undefined) {
			if (gThis.m_oOptions.sLayoutHash != GCookie(GLayoutBoxes.COOKIE_NAME_HASHCODE + GCore.CONTROLLER)) {
				GCookie(GLayoutBoxes.COOKIE_NAME_DISTRIBUTION + GCore.CONTROLLER, null);
			}
		}
		GCookie(GLayoutBoxes.COOKIE_NAME_HASHCODE + GCore.CONTROLLER, gThis.m_oOptions.sLayoutHash, {expires: GCore.COOKIE_LIFETIME});
		if (GCookie(GLayoutBoxes.COOKIE_NAME_DISTRIBUTION + GCore.CONTROLLER) != undefined) {
			bLoadDefault = false;
			try {
				gThis.m_aasBoxesDistribution = $.parseJSON(GCookie(GLayoutBoxes.COOKIE_NAME_DISTRIBUTION + GCore.CONTROLLER));
			}
			catch (xException) {
				bLoadDefault = true;
			}
		}
		if (bLoadDefault) {
			for (var i = 0; i < gThis.m_iColumns; i++) {
				var oColumn = gThis.m_oOptions.aoColumns[i];
				gThis.m_aasBoxesDistribution[i] = GCore.Duplicate(gThis.m_oOptions.aoColumns[i].m_oOptions.asBoxes);
			}
		}

		gThis.DistributeBoxes();
		gThis.m_bDontSave = false;
	};
	
	gThis._CreateBoxes = function() {
		var jBoxes = $(gThis).find('.layout-box');
		gThis.m_iBoxes = jBoxes.length;
		for (var i = 0; i < gThis.m_iBoxes; i++) {
			var sName = jBoxes.eq(i).attr('id').substr("layout-box-".length);
			gThis.m_oBoxes[sName] = new GLayoutBox({
				sName: sName,
				sCaption: jBoxes.eq(i).find('.layout-box-header h3').text(),
				jNode: jBoxes.eq(i),
				oLayoutBoxes: gThis,
				oParamsFromHref: gThis.m_oParamsFromHref[sName],
				oParamsFromHash: gThis.m_oParamsFromHash[sName]
			});
		}
	};
	
	gThis._InitializeBoxes = function() {
		for (var i in gThis.m_oBoxes) {
			gThis.m_oBoxes[i]._InitializeBox();
		}
	};
	
	gThis._CreateColumns = function() {
		for (var i = 0; i < gThis.m_iColumns; i++) {
			var oColumn = gThis.m_oOptions.aoColumns[i];
			var jColumn = $(gThis).children('.layout-column:eq(' + i + ')');
			$(gThis).append(jColumn);
			jColumn.css('z-index', 1 + gThis.m_iColumns - i);
		}
	};
	
	gThis.DistributeBoxes = function() {
		var jColumns = $(gThis).children('.layout-column');
		for (var i = 0; i < gThis.m_iColumns; i++) {
			if (gThis.m_aasBoxesDistribution[i] == undefined) {
				continue;
			}
			var jColumn = jColumns.eq(i);
			var iBoxes = GCore.ObjectLength(gThis.m_aasBoxesDistribution[i]);
			
			for (var j = 0; j < iBoxes; j++) {
				var oBoxOptions = gThis.m_aasBoxesDistribution[i][j];
				var oBox = gThis.m_oBoxes[oBoxOptions.sName];
				if(oBox != undefined){
					if (oBoxOptions.iSpan != undefined) {
						oBox.ChangeSpan(oBoxOptions.iSpan);
					}
					else {
						oBox.ChangeSpan(1);
					}
					var iTime = oBox.m_oOptions.iAnimationTime;
					oBox.m_oOptions.iAnimationTime = 0;
					if (oBoxOptions.bCollapsed) {
						oBox.InitCollapse();
					}
					else {
						oBox.InitUncollapse();
					}
					oBox.m_bClosed = oBoxOptions.bClosed;
					if (!oBox.m_bClosed) {
						oBox.m_oOptions.jNode.css('display', 'block');
						
					}
					else {
						oBox.m_oOptions.jNode.css('display', 'none');
					}
					oBox.m_oOptions.iAnimationTime = iTime;
					jColumn.append($('#layout-box-' + oBox.m_oOptions.sName));
				}
			}
		}
		gThis.RefreshPositions();
	};
	
	gThis._InitializeEvents = function() {
		$(window).resize(gThis.OnResize).resize();
		$(gThis).bind('distributionChanged', gThis.OnDistributionChanged);
		$(gThis).bind('positionsChanged', gThis.OnPositionsChanged);
	};
	
	gThis.OnDistributionChanged = GEventHandler(function(eEvent) {
		setTimeout(function() {
			gThis.RefreshPositions();
			gThis.SaveDistribution();
		}, 50);
	});
	
	gThis.OnPositionsChanged = GEventHandler(function(eEvent) {
		gThis.RefreshPositions();
	});
	
	gThis.OnSort = function(eEvent, oUI) {
		gThis.RefreshPositions();
		return true;
	};
	
	gThis.RefreshPositions = function() {
		gThis.m_bInvalidPlace = false;
		$(gThis).find('.layout-box:not(.ui-sortable-helper), .layout-box-place').css('margin-top', 0);
		$(gThis).find('.layout-box:not(.ui-sortable-helper), .layout-box-place').css('width', 'auto');
		var jColumns = $(gThis).children('.layout-column');
		for (var i = 0; i < gThis.m_iColumns; i++) {
			var iColumnWidth = jColumns.eq(i).width();
			var jBoxes = jColumns.eq(i).children('.layout-box:not(.ui-sortable-helper), .layout-box-place');
			var iBoxes = jBoxes.length;
			for (var j = 0; j < iBoxes; j++) {
				var jBox = jBoxes.eq(j);
				if (jBox.is('.layout-box')) {
					var iSpan = jBox.data('iSpan');
				}
				else {
					if (jBox.is('.layout-box-place')) {
						jBox.removeClass('layout-box-place-invalid');
					}
					var iSpan = gThis.m_iCurrentlySortedOriginalSpan;
					gThis.m_iCurrentlySortedSpan = iSpan;
				}
				if (iSpan == undefined) {
					iSpan = 1;
				}
				var iXBorder = iColumnWidth - jBox.width();
				var iYBorder = jBox.outerHeight() - jBox.height();
				var iWidth = iColumnWidth;
				for (var k = i + 1; k < i + iSpan; k++) {
					if (k >= gThis.m_iColumns) {
						if (jBox.is('.layout-box-place')) {
							var sName = $(gThis).find('.ui-sortable-helper').attr('id');
							if (sName != undefined) {
								var oBox = gThis.m_oBoxes[sName.substr('layout-box-'.length)];
								if (!oBox.m_oOptions.bExpandable) {
									gThis.m_bInvalidPlace = true;
									jBox.addClass('layout-box-place-invalid');
								}
								else {
									gThis.m_iCurrentlySortedSpan = k - i;
									iSpan = k - i;
								}
							}
							else {
								gThis.m_iCurrentlySortedSpan = k - i;
								iSpan = k - i;
							}
						}
						break;
					}
					iWidth += jColumns.eq(k).width() + gThis.m_oOptions.iSpace;
				}
				jBox.css('width', iWidth);
				var iY1Min = jBox.offset().top - gThis.m_iOffsetTop;
				var iY1Max = iY1Min + jBox.height() + iYBorder + gThis.m_oOptions.iSpace;
				for (var k = i + 1; k < i + iSpan; k++) {
					var jRelatedBoxes = jColumns.eq(k).children('.layout-box:not(.ui-sortable-helper), .layout-box-place');
					var iRelatedBoxes = jRelatedBoxes.length;
					iY3Max = 0;
					for (var l = 0; l < iRelatedBoxes; l++) {
						var jRelatedBox = jRelatedBoxes.eq(l);
						var iY2Min = jRelatedBox.offset().top - gThis.m_iOffsetTop;
						var iY2Max = iY2Min + jRelatedBox.height() + iYBorder + gThis.m_oOptions.iSpace;
						if ((iY2Max > iY1Min) && (iY2Min < iY1Max)) {
							jRelatedBox.css('margin-top', iY1Max - iY3Max);
						}
						iY3Max = iY2Max - gThis.m_oOptions.iSpace;
					}
				}
			}
		}
		for (var i = 0; i < gThis.m_iColumns; i++) {
			if (gThis.m_aasBoxesDistribution[i] == undefined) {
				continue;
			}
			var iBoxes = GCore.ObjectLength(gThis.m_aasBoxesDistribution[i]);
			for (var j = 0; j < iBoxes; j++) {
				var oBoxOptions = gThis.m_aasBoxesDistribution[i][j];
				var oBox = gThis.m_oBoxes[oBoxOptions.sName];
				if(oBox != undefined){
					oBox.m_oOptions.jNode.triggerHandler('distributed');
				}
			}
		}
	};
	
	gThis.RefreshDistribution = function() {
		var jColumns = $(gThis).children('.layout-column');
		for (var i = 0; i < gThis.m_iColumns; i++) {
			gThis.m_aasBoxesDistribution[i] = [];
			var jBoxes = jColumns.eq(i).children('.layout-box');
			var iBoxes = jBoxes.length;
			for (var j = 0; j < iBoxes; j++) {
				var jBox = jBoxes.eq(j);
				var sName = jBox.attr('id').substr("layout-box-".length);
				gThis.m_oBoxes[sName].p_oPosition.c = i;
				gThis.m_oBoxes[sName].p_oPosition.r = j;
				if (isNaN(jBox.data('iSpan'))) {
					jBox.data('iSpan', 1);
				}
				gThis.m_oBoxes[sName].m_iSpan = jBox.data('iSpan');
				gThis.m_aasBoxesDistribution[i].push({
					sName: sName,
					iSpan: gThis.m_oBoxes[sName].m_iSpan,
					bCollapsed: gThis.m_oBoxes[sName].m_bCollapsed,
					bClosed: gThis.m_oBoxes[sName].m_bClosed
				});
			}
		}
	};
	
	gThis.SaveDistribution = function() {
		gThis.RefreshDistribution();
		if (gThis.m_bDontSave) {
			return;
		}
		GCookie(GLayoutBoxes.COOKIE_NAME_DISTRIBUTION + GCore.CONTROLLER, JSON.stringify(gThis.m_aasBoxesDistribution), {expires: GCore.COOKIE_LIFETIME});
	};
	
	gThis.OnResize = GEventHandler(function(eEvent) {
		gThis.m_iOffsetTop = $(gThis).offset().top;
		gThis._UpdateColumnWidths();
	});
	
	gThis._UpdateColumnWidths = function() {
		var jColumns = $(gThis).children('.layout-column');
		var iWidthRemaining = $(gThis).css('width', 'auto').width();
		if (gThis.m_oOptions.iMinWidth > iWidthRemaining) {
			iWidthRemaining = gThis.m_oOptions.iMinWidth;
		}
		$(gThis).width(iWidthRemaining);
		iWidthRemaining += gThis.m_oOptions.iSpace;
		var iAutoWidthColumns = 0;
		for (var i = 0; i < gThis.m_iColumns; i++) {
			var oColumn = gThis.m_oOptions.aoColumns[i];
			if (oColumn.m_oOptions.iWidth == GLayoutColumn.WIDTH_AUTO) {
				iAutoWidthColumns++;
				continue;
			}
			iWidthRemaining -= gThis.m_oOptions.iSpace + oColumn.m_oOptions.iWidth;
		}
		var iAutoWidth = Math.floor((iWidthRemaining - (iAutoWidthColumns) * gThis.m_oOptions.iSpace) / iAutoWidthColumns);
		for (var i = 0; i < gThis.m_iColumns; i++) {
			if (gThis.m_oOptions.aoColumns[i].m_oOptions.iWidth == GLayoutColumn.WIDTH_AUTO) {
				jColumns.eq(i).width(iAutoWidth);
			}
			else {
				jColumns.eq(i).width(gThis.m_oOptions.aoColumns[i].m_oOptions.iWidth);
			}
			if (i > 0) {
        // do not change inline CSS generated by PHP
        if(gThis.m_oOptions.aoColumns[i].m_oOptions.iWidth == $(gThis).width()){
          jColumns.eq(i).css('margin-left', 0);
        }else{
          jColumns.eq(i).css('margin-left', gThis.m_oOptions.iSpace);
        }
			}
			else {
				jColumns.eq(i).css('margin-left', 0);
			}
		}
	};
	
	gThis._Constructor();
	
};

GLayoutBoxes.COOKIE_NAME_DISTRIBUTION = 'layout-distribution-';
GLayoutBoxes.COOKIE_NAME_HASHCODE = 'layout-hashcode-';

new GPlugin('GLayoutBoxes', oDefaults, GLayoutBoxes);

GLayoutBoxes.Language = {
	display_options: 'Opcje',
	available_boxes: 'Dostępne boksy',
	restore_layout: 'Przywróć domyślny układ'
};

/*
* PRODUCT LIST BOX
*/

var GLayoutBoxTypeProductList = function(oOptions) {
	
	var gThis = this;
	gThis.m_oOptions = $.extend(true, GCore.Duplicate(GLayoutBoxTypeProductList.DEFAULTS, true), oOptions);
	
	gThis.m_jProductList;
	gThis.m_iPage = 1;
	gThis.m_iPagesTotal = 1;
	gThis.m_jPagination;
	
	gThis._Constructor = function() {
		gThis.m_oOptions.oLayoutBox.SetWidthClasses([100, 300]);
	};
	gThis._Constructor();
	
};

GLayoutBoxTypeProductList.DEFAULTS = {
	jNode: GCore.NULL,
	oLayoutBox: GCore.NULL
};

/*
* SHOWCASE BOX
*/

var GLayoutBoxTypeShowcase = function(oOptions) {
	
	var gThis = this;
	gThis.m_oOptions = $.extend(true, GCore.Duplicate(GLayoutBoxTypeShowcase.DEFAULTS, true), oOptions);
	
	gThis.m_sCategory = '';
	gThis.m_jCarousel;
	gThis.m_iCurrentItem = 0;
	gThis.m_bFirstLoad = true;
	
	gThis._Constructor = function() {
		gThis.m_oOptions.oLayoutBox.SetWidthClasses([450]);
		gThis.m_jCarousel = gThis.m_oOptions.jNode.find('.carousel');
		gThis.m_jCategories = gThis.m_oOptions.jNode.find('.bottom-tabs').find('a');
		gThis.m_jCategories.click(function(){
			gThis.ChangeCategory($(this).attr('data-id'));
		});
		gThis.m_jTabs = gThis.m_oOptions.jNode.find('.bottom-tabs');
		var jTabs = gThis.m_oOptions.jNode.find('.bottom-tabs').find('a');
		var iTabs = jTabs.length;
		var iSelected = 0;
		var iTotalWidth = 0;
		for (var i = 0; i < iTabs; i++) {
			iTotalWidth += jTabs.eq(i).closest('li').outerWidth();
		}
		gThis.m_jTabs.css('width', iTotalWidth);
	};
	
	gThis.ChangeCategory = function(sCategory) {
		gThis.m_sCategory = (sCategory == 'all') ? '' : sCategory;
		window['xajax_GetProductsForSchowcase_' + gThis.m_oOptions.oLayoutBox.m_oOptions.sName]({
			category: gThis.m_sCategory
		}, GCallback(function(eEvent) {
			gThis.m_jCarousel.empty();
			gThis.m_jCarousel.append(eEvent.products);
		}));
	};
	
	gThis._Constructor();
	
};

GLayoutBoxTypeShowcase.DEFAULTS = {
	jNode: GCore.NULL,
	oLayoutBox: GCore.NULL
};

GException.Language = {
	exception_has_occured: 'Wystąpił błąd!'
};

/*
* OVERLAY
* Adds a customizable overlay that covers everything except the element that it's invoked for.
*/

var oDefaults = {
	oClasses: {
	},
	iZIndex: 1000,
	fClick: GCore.NULL,
	fOpacity: 0.0
};

var GOverlay = function() {
	
	var gThis = this;
	
	gThis.m_jOverlay;
	
	this._Constructor = function() {
		gThis.m_jOverlay = $('<div class="GOverlay"/>').css({
			display: 'block',
			position: 'absolute',
			left: 0,
			top: 0,
			width: $(document).width(),
			height: $(document).height(),
			zIndex: gThis.m_oOptions.iZIndex,
			opacity: gThis.m_oOptions.fOpacity,
			background: '#000'
		});
		$('body').append(gThis.m_jOverlay);
		$(gThis).css({
			zIndex: gThis.m_oOptions.iZIndex + 1
		});
		gThis.m_jOverlay.click(GEventHandler(function(eEvent) {
			var bResult = false;
			if (gThis.m_oOptions.fClick instanceof Function) {
				bResult = gThis.m_oOptions.fClick.apply(this, [eEvent]);
			}
			if (!bResult) {
				gThis.m_jOverlay.remove();
			}
			return false;
		}));
	};
	
	gThis._Constructor();
	
};

GOverlay.RemoveAll = function() {
	$('.GOverlay').remove();
};

new GPlugin('GOverlay', oDefaults, GOverlay);

var GShadow = function() {
	
	var gThis = this;
	
	this._Constructor = function() {
		$(gThis).append('<span class="' + gThis.m_oOptions.oClasses.sNE + '"/>');
		$(gThis).append('<span class="' + gThis.m_oOptions.oClasses.sSE + '"/>');
		$(gThis).append('<span class="' + gThis.m_oOptions.oClasses.sSW + '"/>');
		$(gThis).append('<span class="' + gThis.m_oOptions.oClasses.sS + '"/>');
		$(gThis).append('<span class="' + gThis.m_oOptions.oClasses.sE + '"/>');
	};
	
	gThis._Constructor();
	
};

new GPlugin('GShadow', oDefaults, GShadow);

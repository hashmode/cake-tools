/****************************************************
 * 
 * 					VARS
 * 
 ***************************************************/


/**
 * used when showing confirmation message
 * @mixed - boolean|object
*/
var $CT_CONFIRM_NODE = false;

/**
 * ajax loading image html source
 * @string
*/
var CT_LOADING_IMG = '';
if (typeof CT_WEB_ROOT !== 'undefined') {
	CT_LOADING_IMG = '<img src="' + CT_WEB_ROOT + CT_ROUTER_NAME + '/img/loading.gif" />';
}

/**
 * keeps the status of pending ajax request
 * @boolean
*/
var CT_ACTIVE_AJAX = false;

/**
 * texts for i18n - defined in view
 * @object
 * 
 * var CT_TEXT
*/

/**
 * VARS END
*/


/****************************************************
 * 
 * 					FUNCTIONS
 * 
 ***************************************************/

/**
 * mathAdd method
 * function for adding numbers
 * 
 * @param {string|number} a
 * @param {string|number} b
 * @return {number}
*/
function mathAdd(a, b) {
	return -(-a - b);
	// OR return toInt(a) + toInt(b); ? 
}

/**
 * toInt method
 * convert string type to int type
 * 
 * @param {string} str
 * @return {number}
 * @link http://stackoverflow.com/a/22440945/932473
*/
function toInt(str) {
	return str*1;
}

/**
 * isNumber method
 * check if the var a valid number
 * 
 * @param {string} n
 * @return {boolean}
 * @link http://stackoverflow.com/a/9716488/932473
*/
function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}

/**
 * extractNumber method
 * extract numbers from string
 * 
 * @param {string} str
 * @return {number}
*/
function extractNumber(str) {
	return toInt(str.match(/[0-9]+/));
}

/**
 * str method
 * fades the element out and in
 * 
 * @param {object} $elem
 * @return void
*/
function flash($elem) {
	$elem.fadeIn(100).fadeOut(300).fadeIn(300);
}

/**
 * getFirstKey method
 * get object's first key
 * 
 * @param {object} obj
 * @return mixed
 * @link http://stackoverflow.com/a/2769741/932473
*/
function getFirstKey(obj) {
    for (var a in obj) return a;
}

/**
 * getFirstValue method
 * get object's first value
 * 
 * @param {object} obj
 * @return mixed
 */
function getFirstValue(obj) {
	for (var a in obj) return obj[a];
}

/**
 * round method
 * math round by precision - simlar to "round" in php
 * 
 * @param {number} number
 * @param {number} precision
 * @return {number}
*/
function round(number, precision) {
	if (typeof precision === 'undefined') {
		return Math.round(number);
	}
	number = toInt(number);
	var result = Math.round(number*Math.pow(10, precision), precision) / (Math.pow(10, precision));
	return result.toFixed(precision);
}

/**
 * caller method
 * call function by name
 * 
 * @param {string} f - the function name
 * @param _ {mixed} argument
 * @return void
*/
function caller(f, argument) {
	// if at least one argument is provided
	if (typeof argument !== 'undefined') {
		
		// exclude the first argument - funciton name
		var newArguments = [];
		for (var i in arguments) {
			if (i != 0) {
				newArguments.push(arguments[i]);
			}
		}

		window[f].apply(this, newArguments);
	} else {
		window[f]();
	}
}

/**
 * parseJson method
 * parses the json string
 * 
 * @param {string} str
 * @return mixed - json object or boolean false if invalid string  
*/
function parseJson(str) {
	var isJson = true;
	try {
		var json = $.parseJSON(str)
	} catch (err) {
		isJson = false;
	}

	return isJson ? json : false;
}

/**
 * htmlDecode method
 * replaces html with equivalent single character
 * 
 * @param {string} str
 * @retur {string}	
*/
function htmlDecode(str) {
	str = str.replace('&lt;', "<");
	str = str.replace('&gt;', ">");
	str = str.replace('&amp;', "&");
	return str;
}	

/**
 * disable method
 * disables the element(s) - adding disabled attribute and disabled class
 * 
 * @param _ {object}
 * @return void
*/
function disable() {
	for (var i = 0; i < arguments.length; i++) {
		arguments[i].prop('disabled', 'disabled');
		arguments[i].addClass('disabled');
	}	
}

/**
 * enable method
 * enables the element(s) - deleting the disabled attribute and disabled class
 * 
 * @param _ {object}
 * @return void
*/
function enable() {
	for (var i = 0; i < arguments.length; i++) {
		arguments[i].removeAttr('disabled');
		arguments[i].removeClass('disabled');
	}	
}

/**
 * readOnly
 * makes element(s) readonly - adding readonly attribute
 * 
 * @param _ {object}
 * @return void
 */
function readOnly() {
	for (var i = 0; i < arguments.length; i++) {
		arguments[i].prop('readonly', 'readonly');
	}	
}

/**
 * unReadOnly
 * deletes element(s) readonly attribute
 * 
 * @param _ {object}
 * @return void
 */
function unReadOnly() {
	for (var i = 0; i < arguments.length; i++) {
		arguments[i].removeAttr('readonly');
	}	
}

/**
 * time method
 * get current time in seconds
 * 
 * @return {number}
*/
function time() {
	return Math.round(new Date().getTime() / 1000);
}

/**
 * militime method
 * get current time in miliseconds
 * 
 * @return {number}
*/
function militime() {
	return new Date().getTime();
}

/**
 * rand method
 * generates random number between given range
 * 
 * @param {number} min
 * @param {number} max
 * @return {number}
 * @link https://gist.github.com/fevangelou/62c5960081410ebaaea0
*/
function rand(min, max){
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * generateRandomNumber method
 * generates random number
 * 
 * @return {number}
*/
function generateRandomNumber() {
	var num = Math.random();
	var str = num.toString();
	return toInt(str.replace("0.", ""));
}

/**
 * generateRandomStr method
 * generates a random string
 * 
 * @param {number} length
 * @param {boolean} alphanumeric - (default true) to use only number and letters
 * @return {string}
*/
function generateRandomStr(length, alphanumeric) {
	var maxLength = 50;
	if (typeof alphanumeric !== 'undefined' && !alphanumeric) {
		maxLength = 80;
	} else {
		alphanumeric = true;
	}

	if (length > maxLength) {
		return '';
	}

	var string = "";
	var i = 0;
	var possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var signs = "!#$%&()*+,-./:;<=>?@[]^_`{|}~";

	if (!alphanumeric) {
		possible += signs;
	}

	var possibleLength = possible.length - 1;

	while (i < length){
		var rnd = rand(0, possibleLength);
		var char = possible.substring(rnd, rnd + 1);
		if (!strstr(string, char)) {
			string += char;
			i++;
		}
	}

	return string;
}


/**
 * scroll2 method
 * scrolls to the given element in a given time
 * 
 * @param {object} $element
 * @param {number} time - time in seconds that scrolling should take
 * @param {number} correct - number in px to adjust the scrolled final position from $element
 * @param {string} callback - callback function to call after scrolling is done
 * @return void  
*/
function scroll2($element, time, correct, callback) {
	var position = false;
	
	try {
		position = $element.offset().top;
	} catch (err) {
		;
	}
	if (!position) {
		return;
	}

	var scrollPosition = $element.offset().top;
	if (typeof correct !== 'undefined' && correct) {
		scrollPosition = mathAdd(scrollPosition, correct);
	}
	
	if (typeof time == 'undefined' || !time) {
		time = 3000;
	}

	$('html, body').animate({
		scrollTop : scrollPosition
	}, time, function() {
		if (typeof callback !== 'undefined') {
			caller(callback, $element);
		}
	});
}


/**
 * isVisible method
 * checks if the element is visible on the page (without scrolling)
 * 
 * @param {object} $elem
 * @return {boolean}
 * @link http://stackoverflow.com/a/488073
*/
function isVisible($elem) {
	var docViewTop = $(window).scrollTop();
	var docViewBottom = docViewTop + $(window).height();

	var elemTop = $elem.offset().top;
	var elemBottom = elemTop + $elem.height();

	return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}


/**
 * activateAjaxForm mthod
 * activate ajax form
 * 
 * @param {object} $thisNode
 * @param {string} callback
 * @return void
*/
function activateAjaxForm($thisNode, callback) {
	if (!$thisNode.find('form').length) {
		return false;
	}
	$thisNode.find('form').ajaxForm({
		target: $thisNode,
		error: function() {
			alertBox("Server Error");
		},
		success: function(content){
			if (typeof callback !== 'undefined') {
				activateAjaxForm($thisNode, callback);
				caller(callback);
			} else {
				activateAjaxForm($thisNode);
			}
		}
    });

	loadDefaults();
}


/**
 * arraysEqual method
 * compares if 2 arrays are the same
 * 
 * @param {array} a
 * @param {array} b
 * @return {boolean}
 * @link http://stackoverflow.com/a/16436975/932473
*/
function arraysEqual(a, b) {
	if (a === b) {
		return true;
	}
	
	if (a == null || b == null) {
		return false;
	}
	
	if (a.length != b.length) {
		return false;
	}

	/**
	 * if the order of the elements inside the array does not matter
	*/
	for (var i = 0; i < a.length; ++i) {
		if (a[i] !== b[i]) {
			return false;
		}
	}

	return true;
}


/**
 * strstr method
 * 
 * Finds first occurrence of a string within another
 * version: 1103.1210
 * 	  discuss at: http://phpjs.org/functions/strstr    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * +   bugfixed by: Onno Marsman
 * +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 *     example 1: strstr(‘Kevin van Zonneveld’, ‘van’);
 *     returns 1: ‘van Zonneveld’    // *     example 2: strstr(‘Kevin van Zonneveld’, ‘van’, true);
 *     returns 2: ‘Kevin ‘
 *     example 3: strstr(‘name@example.com’, ‘@’);
 *     returns 3: ‘@example.com’
 *     example 4: strstr(‘name@example.com’, ‘@’, true);    // *     returns 4: ‘name’
 *     
 * @param {string} haystack
 * @param {string} needle
 * @param {boolean} bool
 * @return {boolean}
 * @link http://stackoverflow.com/a/7015207/932473
*/
function strstr(haystack, needle, bool) {
    var pos = 0;

    haystack += "";
    pos = haystack.indexOf(needle); if (pos == -1) {
        return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}

/**
 * loadDefaults method
 * loads default settings - might be used after ajax page/element load
 * 
 * @return void
*/
function loadDefaults() {
	/**
	 * adjust toggle elements
	*/
	$('input[type="checkbox"].toggle-box').each(function() {
		if (!$(this).is(":checked")) {
			$("."+$(this).attr('target')).addClass('hidn');
		} else {
			if ($(this).attr('rtarget')) {
				$("."+$(this).attr('rtarget')).addClass('hidn');
			}
		}
	});
	
	/**
	 * adjust toggle elements - reverse
	*/
	$('input[type="checkbox"].toggle-box-reverse').each(function() {
		if ($(this).is(":checked")) {
			$("."+$(this).attr('target')).addClass('hidn');
		} else {
			if ($(this).attr('rtarget')) {
				$("."+$(this).attr('rtarget')).addClass('hidn');
			}
		}
	});
	
	/**
	 * adjust select toggle elements for reverse
	*/
	$('select.toggle-box').each(function() {
		// show element when the select is checked
		var val = $(this).val();
		var _val = $(this).attr('_val');
		var showList = $(this).attr('show');
		var hideList = $(this).attr('hide');

		if (showList) {
			var showEls = showList.split(',');
			for (var v = 0; v < showEls.length; v++) {
				var thisEl = $.trim(showEls[v]);
				if (val == _val) {
					$(thisEl).show();
				} else {
					$(thisEl).hide();
				}
			}
		}
			
		if (hideList) {
			var hideEls = hideList.split(',');
			for (var v = 0; v < hideEls.length; v++) {
				var thisEl = $.trim(hideEls[v]);
				if (val == _val) {
					$(thisEl).hide();
				} else {
					$(thisEl).show();
				}
			}
		}
	});
	
	/**
	 * adjust toggle readonly elements 
	*/
	$(".toggle-readonly").each(function() {
		if ($(this).is(":checked")) {
			readOnly($("."+$(this).attr('toggleElement')));
		}
	});

	/**
	 * adjust toggle readonly elements 
	 */
	$(".toggle-readonly-reverse").each(function() {
		if (!$(this).is(":checked")) {
			readOnly($("."+$(this).attr('toggleElement')));
		}
	});

	/**
	 * adjust switch elements
	*/
	$(".switch-radio input:radio").each(function() {
		if ($(this).is(":checked")) {
			$("#"+$(this).attr('switchElement')).removeClass('hidn');
		} else {
			$("#"+$(this).attr('switchElement')).addClass('hidn');
		}
	});

	/**
	 * if empty - disable initially
	*/
	$(".typing-check").each(function() {
		if ($.trim($(this).val()) == "") {
			disable($("#"+$(this).attr('target')));
		} else {
			enable($("#"+$(this).attr('target')));
		}
	});
}

/**
 * 
 * 	functions END
 * 
*/





/****************************************************
 * 
 * 
 * 					DOCUMENT READY
 * 
 * 
 ***************************************************/
$(document).ready(function(){
	
	loadDefaults();
	
	/**
	 * parse CT_TEXT
	*/
	CT_TEXT = parseJson(CT_TEXT);
	
	/**
	 * change parent checkbox when chaning the child checkboxes
	 * 
	 *  ".checkbox-group" is used when having more than one checkbox groups on the same page
	*/
	$(document).on('click', ".checkbox-child", function(e) {
		var $parent = $(this).parents('.checkbox-group');
		var $row = $(this).parents('tr');

		var groupStr = '';
		if ($(this).attr('grpid')) {
			groupStr = '[grpid="'+$(this).attr('grpid')+'"]';
		}

		if ($(this).is(":checked")) {
			$row.addClass('selected');
			
			if ($parent.find(".checkbox-child"+groupStr).length == $parent.find(".checkbox-child:checked"+groupStr).length) {
				$parent.find(".checkbox-parent"+groupStr).prop('indeterminate', false);
				$parent.find(".checkbox-parent"+groupStr).prop('checked', 'checked');
			} else {
				$parent.find(".checkbox-parent"+groupStr).prop('indeterminate', true);
			}
		} else {
			$row.removeClass('selected');

			$parent.find(".checkbox-parent"+groupStr).removeAttr('checked');

			if ($parent.find(".checkbox-child:checked"+groupStr).length > 0) {
				$parent.find(".checkbox-parent"+groupStr).prop('indeterminate', true);
			} else {
				$parent.find(".checkbox-parent"+groupStr).prop('indeterminate', false);
			}
		}

		e.stopPropagation();
	});

	$(document).on('click', ".checkbox-parent", function(e) {
		var $parent = $(this).parents('.checkbox-group');

		var groupStr = '';
		if ($(this).attr('grpid')) {
			groupStr = '[grpid="'+$(this).attr('grpid')+'"]';
		}

		if ($(this).is(":checked")) {
			$parent.find(".checkbox-child"+groupStr).prop('checked', 'checked');
			$parent.find("tr").addClass('selected');
		} else {
			$parent.find(".checkbox-child"+groupStr).removeAttr('checked');
			$parent.find("tr").removeClass('selected');
		}
		
		e.stopPropagation();
	});	
	
	/**
	 * - show submit button loading
	 * - disable the submit button to prevent double submit
	*/
	$(document).on('submit', 'form', function() {
		$(this).find('input[type="submit"]').addClass('disabled');
		var val = $(this).find('input[type="submit"]').attr('data-loading-text');
		if (val) {
			$(this).find('input[type="submit"]').val(val);
		}
	});
	
	
	/**
	 * 
	 * 		AJAX CALLBACKS
	 * 
	*/
	
	
	/**
	 * ajax start event
	 * - update pending ajax status
	*/
	$(document).bind("ajaxStart", function(){
		CT_ACTIVE_AJAX = true;
	});
	
	/**
	 * araxSend callback - attah the csrf token
	 */
	$(document).bind("ajaxSend", function(elm, xhr, s){
		if (typeof CT_CSRF_TOKEN !== 'undefined') {
			xhr.setRequestHeader('CSRF-TOKEN', CT_CSRF_TOKEN);
		}
	});
	
	/**
	 * callback for ajax error requests
	*/
	$(document).ajaxError(function myErrorHandler(event, xhr, ajaxOptions, thrownError) {
		CT_ACTIVE_AJAX = false;
	});

	/**
	 * ajax request completed successfully
	 * - update pending ajax status
	 * - remove submit button disabled state
	*/
	$(document).bind("ajaxComplete", function() {
		CT_ACTIVE_AJAX = false;
		$('form input[type="submit"]').removeClass('disabled');
	});
	
	
	/**
	 * 
	 * 		AJAX CALLBACKS END
	 * 
	*/

	
	/**
	 * clicking on disabled element
	*/
	$(document).on('click', ".disabled", function() {
		return false;
	});
	
	$("form").attr("novalidate", "novalidate");
	
	/**
	 * enable/disable send button, on input/textarea typing
	*/
	$(document).on('keyup', '.typing-check', function (e) {
		if ($.trim($(this).val()) == "") {
			disable($("#"+$(this).attr('target')));
		} else {
			enable($("#"+$(this).attr('target')));	$(document).on('click', ".fake-link", function(e) {
				e.preventDefault();
				return false;
			});
		}
	});
	
	/**
	 * swtich-radio and switch-box - switch-content, attr=switchElement
	*/
	$(document).on('change', ".switch-radio input:radio", function() {
		$(".switch-box .switch-content").addClass('hidn');
		$('#'+$(this).attr('switchElement')).removeClass('hidn');
		
		if ($(".switch-box").attr('callback')) {
			var callback = $(".switch-box").attr('callback');
			caller(callback);
		}
	});

	
	/**
	 * signup captcha update
	*/
	$(document).on('click', ".signup-captcha-change", function(e) {
		e.stopPropagation();
		var src = $("#signupCaptchaImg").attr('src');

		// set last element number
		var arr = src.split("/");
		arr[arr.length-1] = militime();
		
		$("#signupCaptchaImg").attr('src', arr.join("/"));
		
		$("#signupCaptchaImg").addClass('vis-hidden');
		$("#signupCaptchaImg").before('<span id="signupCaptchaLoading">' + CT_LOADING_IMG + '</span>');
		$("#signupCaptchaImg").on('load', function() {
			$("#signupCaptchaImg").removeClass('vis-hidden');
			$("#signupCaptchaLoading").remove();
		});

		return false;
	});
	

	/**
	 * disable button on checkbox click
	*/
	$(document).on('change', ".toggle-btn", function() {
		if ($(this).is(":checked")) {
			enable($("#"+$(this).attr("toggleBtn")));
		} else {
			disable($("#"+$(this).attr("toggleBtn")));
		}
	});
	
	/**
	 * toggle table row
	*/
	$(document).on('click', "table .tr-toggle td:not(.not-tr-toggle)", function() {
		var trtoggleid = $(this).parent().attr('trtoggleid');
		$(".tr-collapse").not("#"+trtoggleid).addClass('hidn');
		$("#"+trtoggleid).toggleClass('hidn');
	});
	
	
	/**
	 * show element(and/or hide another) when the checkbox is checked
	*/
	$(document).on('change', 'input[type="checkbox"].toggle-box', function() {
		var elClass = $(this).attr('target');
		var rElClass = $(this).attr('rtarget');		// reverse target
		
		if ($(this).is(":checked")) {
			$("."+elClass).removeClass('hidn');
			
			if (rElClass) {
				$("."+rElClass).addClass('hidn');

				if (!$(this).attr("rkeep")) {
					$("."+rElClass).find('input:not(:checkbox):not(:radio):not([type="hidden"]), select').val("");
				}
			}

		} else {
			$("."+elClass).addClass('hidn');

			if (!$(this).attr("keep")) {
				$("."+elClass).find('input:not(:checkbox):not(:radio):not([type="hidden"]), select').val("");
			}

			if (rElClass) {
				$("."+rElClass).removeClass('hidn');
			}
		}
	});

	/**
	 * show/hide element when clicked on element (other than checkbox)
	*/
	$(document).on('click', 'div.toggle-box, span.toggle-box, a.toggle-box, button.toggle-box, .toggle-box:header', function(){
		var elClass = $(this).attr('target');

		var txtHtml = $(this).html();
		var txtAttr = $(this).attr('txt');
		$(this).html(txtAttr);
		$(this).attr('txt', txtHtml);
		
		if ($("."+elClass).is(':visible')) {
			$("."+elClass).hide();
		} else {
			$("."+elClass).show();
		}
	});
	
	/**
	 * show element (and/or hide another element) when the checkbox is unchecked
	*/
	$(document).on('change', 'input[type="checkbox"].toggle-box-reverse', function() {
		var elClass = $(this).attr('target');
		var rElClass = $(this).attr('rtarget');		// reverse target
		
		if (!$(this).is(":checked")) {
			$("."+elClass).removeClass('hidn');
			
			if (rElClass) {
				$("."+rElClass).addClass('hidn');
				
				if (!$(this).attr("rkeep")) {
					$("."+rElClass).find('input:not(:checkbox):not(:radio):not([type="hidden"]), select').val("");
				}
			}
		} else {
			if (!$(this).attr("keep")) {
				$("."+elClass).find('input:not(:checkbox):not(:radio):not([type="hidden"]), select').val("");
			}

			$("."+elClass).addClass('hidn');
			
			if (rElClass) {
				$("."+rElClass).removeClass('hidn');
			}
		}
	});
	
	/**
	 * show element(and/or hide another element) when the given select option is selected  
	*/
	$(document).on('change', 'select.toggle-box', function() {
		var val = $(this).val();
		var _val = $(this).attr('_val');
		var showList = $(this).attr('show');
		var hideList = $(this).attr('hide');

		if (showList) {
			var showEls = showList.split(',');
			for (var v = 0; v < showEls.length; v++) {
				var thisEl = $.trim(showEls[v]);
				if (val == _val) {
					$(thisEl).show();
				} else {
					$(thisEl).hide();
				}
			}
		}
			
		if (hideList) {
			var hideEls = hideList.split(',');
			for (var v = 0; v < hideEls.length; v++) {
				var thisEl = $.trim(hideEls[v]);
				if (val == _val) {
					$(thisEl).hide();
				} else {
					$(thisEl).show();
				}
			}
		}
	});
	
	/**
	 * add/remove element readonly when checkbox is checked
	*/
	$(document).on('change', 'input[type="checkbox"].toggle-readonly', function() {
		var elClass = $(this).attr('toggleElement');

		if ($(this).is(":checked")) {
			readOnly($("."+elClass));
		} else {
			unReadOnly($("."+elClass));
		}
	});

	/**
	 * add/remove element readonly on checkbox is unchecked
	 */
	$(document).on('change', 'input[type="checkbox"].toggle-readonly-reverse', function() {
		var elClass = $(this).attr('toggleElement');
		
		if ($(this).is(":checked")) {
			$("."+elClass).removeAttr('readonly');
		} else {
			$("."+elClass).attr('readonly', 'readonly');
		}
	});
	
	/**
	 * disable element dragging
	 */
	$(document).on("dragstart", ".no-drag", function(e) {
		return false;
	});

	/**
	 * load data to child select box - when selecting parent
	*/
	$(document).on('change', ".loading-parent", function() {
		var val = $(this).val();
		
		var $target = $('#'+$(this).attr('target'));
		$target.html('<option>' + CT_TEXT.select_loading + '</option>');

		$target.load($(this).attr('url') + val, function() {
			;
		});
	});
	
	/**
	 * disable element dragging
	*/
	$(document).on("dragstart", ".no-drag", function(e) {
         return false;
	});
	
	/**
	 * link by js
	*/
	$(document).on('click', '.jsa', function() {
		var jsref = $(this).attr('jsref');
		var target = $(this).attr('target');
		
		if (jsref) {
			if (target) {
				window.open(jsref, target);
			} else {
				window.location = jsref;
			}
		}
	});
	
	/**
	 * blur readonly inputs
	*/
	$(document).on('focus', 'input[type="text"][readonly="readonly"]', function() {
		$(this).blur();
	});
	
});


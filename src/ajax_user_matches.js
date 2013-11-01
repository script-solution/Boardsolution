/**
 * Contains the AJAX-request to show matching users
 * 
 * @package			Boardsolution
 * @subpackage	src
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// the current object of AJAXUserSearchConstr
var __currentObj = null;
var __objects = new Array();
var _isReqPending = false;

/**
 * The constructor for the AJAX user search
 *
 * @param string root the root-path
 * @param string inputId the id of the input-field
 * @param string actionParam the action-parameter-name
 * @param string matchText the text to display at the beginning of the matches
 * @param string waitImg the wait-image to use
 * @param boolean multiple is it allowed to enter more than one username?
 * @param boolean quotesAllowed are quotes (") allowed?
 * @param string separator the separator for multiple usernames
 */
function BS_AJAXUserSearch(root,inputId,actionParam,matchText,waitImg,multiple,quotesAllowed,separator)
{
	this.lastKeyword = '';
	this.root = root;
	this.inputId = inputId;
	this.resultId = 'ajax_umatches_result';
	this.waitId = 'ajax_umatches_wait';
	this.actionParam = actionParam;
	this.matchText = matchText;
	this.waitImg = waitImg;
	
	if(typeof multiple == "undefined")
		multiple = true;
	this.multiple = multiple;

	if(typeof quotesAllowed == "undefined")
		quotesAllowed = false;
	this.quotesAllowed = quotesAllowed;
	
	if(typeof separator == "undefined")
		separator = ' ';
	this.separator = separator;
	
	this.foundUser = new Array();
	
	// very bad, but works :)
	__objects[this.inputId] = this;
	
	// methods
	this.displayWaitCursor = displayWaitCursor;
	this.findMatchingUser = findMatchingUser;
	this.displayFoundUser = displayFoundUser;
	
	// store wether the focus is on the input-field
	var inputField = FWS_getElement(this.inputId);
	
	var linputId = this.inputId;
	FWS_addEvent(inputField,'focus',function() {
		if(Browser.isIE)
			this.id = linputId;
		__currentObj = __objects[this.id];
	});
	FWS_addEvent(inputField,'blur',function() {
		__currentObj = null;
	});
	
	// add onkeyup event
	FWS_addEvent(inputField,'keyup',function() {
		if(Browser.isIE)
			this.id = linputId;
		__objects[this.id].findMatchingUser();
	});
}

/**
 * determines the entered username to search for
 * and sends the HTTP-request
 */
function findMatchingUser()
{
	// reject requests if another one is running
	if(_isReqPending)
		return;

	var field = document.getElementById(this.inputId);
	if(field.value == '')
		return;
	
	// determine start-position
	var start = 0;
	if(this.multiple)
	{
		if(this.quotesAllowed && field.value.substr_count('"') % 2 == 1)
			start = field.value.lastIndexOf('"');
		else
			start = field.value.lastIndexOf(this.separator);
		
		if(start < 0)
			start = 0;
		else
			start++;
	}
	
	var keyword = field.value.substring(start);
	keyword = keyword.trim();
	
	// don't fire the request again if the keyword is the same
	if(keyword == this.lastKeyword)
		return;
	
	this.lastKeyword = keyword;
	
	var tempref = this;
	
	var url_parameters = window.location.search;
	var splitted_url_parameters = url_parameters.split("&");
	var split_url_values = splitted_url_parameters[splitted_url_parameters.length-1].split("=");
	var last_url_parametervalue = split_url_values[split_url_values.length-1];
	
	var url = this.root + "index.php?" + this.actionParam + "=ajax_get_user&cmod=" + encodeURIComponent(last_url_parametervalue) + "&kw=";
	url += encodeURIComponent(keyword);
	
	myAjax.setEventHandler('onstart',function() {
		tempref.displayWaitCursor();
		_isReqPending = true;
	});
	myAjax.setEventHandler('onfinish',function() {
		FWS_hideElement(tempref.waitId);
		_isReqPending = false;
	});
	
	myAjax.sendGetRequest(url,function(text) {
		tempref.displayFoundUser(text);
	});
}

/**
 * displays the wait-cursor
 */
function displayWaitCursor()
{
	if(!FWS_getElement(this.waitId))
	{
		var inputField = FWS_getElement(this.inputId);
		var div = document.createElement('div');
		div.id = this.waitId;
		div.style.width = '16px';
		div.style.height = '16px';
		div.style.padding = '4px';
		div.style.backgroundColor = '#fff';
		div.style.border = '1px dotted #999';
		div.style.position = 'absolute';
		div.style.display = 'none';
		div.innerHTML = '<img src="' + this.root + this.waitImg + '" alt="Wait" />';
		inputField.parentNode.appendChild(div);
	}
	
	FWS_displayElement(this.waitId,this.inputId,'tl',5);
}

/**
 * displays the found user
 */
function displayFoundUser(text)
{
	if(!FWS_getElement(this.resultId))
	{
		var inputField = FWS_getElement(this.inputId);
		var div = document.createElement('div');
		div.id = this.resultId;
		div.onmouseover = div.focus;
		div.onmouseout = div.blur;
		div.style.padding = '4px';
		div.style.whiteSpace = 'nowrap';
		div.style.backgroundColor = '#fff';
		div.style.border = '1px dotted #999';
		div.style.position = 'absolute';
		div.style.zIndex = 5;
		div.style.display = 'none';
		inputField.parentNode.appendChild(div);
	}

	var resultField = document.getElementById(this.resultId);
	
	// generate the string with the user
	this.foundUser = new Array();
	var result = text;
	var resParts = result.split(',');
	var resStr = '';
	for(var i = 0;i < resParts.length;i++)
	{
		if(resParts[i] == '...')
			resStr += resParts[i];
		else
		{
			// store the user in an array for later access
			this.foundUser[i] = resParts[i];
			resStr += '<a href="javascript:addUserToField(\'' + this.inputId;
			resStr += '\',\'' + this.resultId + '\',' + this.multiple + ',\'' + this.separator;
			resStr += '\',' + this.quotesAllowed + ',\'' + resParts[i] + '\');">';
			resStr += resParts[i] + '</a>';
		}
		
		// add comma?
		if(i < resParts.length - 1)
			resStr += ', ';
	}
	
	if(resParts.length == 0)
		resStr = '&nbsp;-&nbsp;';
	
	// add the close-image
	resStr += '<img src="' + this.root + 'images/delete.gif" alt=""';
	resStr += ' style="padding-left: 5px;" onmouseover="this.style.cursor = \'pointer\';"';
	resStr += ' onmouseout="this.style.cursor = \'default\';" onclick="hideResults(\'';
	resStr += this.resultId + '\');" />';
	
	resultField.innerHTML = this.matchText + ': ' + resStr;
	
	FWS_displayElement(this.resultId,this.inputId,'tlr',5);
	
	FWS_getElement(this.inputId).focus();
}

/**
 * adds the given username to the specified input-field
 *
 * @param string inputId the input-Id
 * @param string resultId the id of the result-element
 * @param boolean multiple use multiple usernames?
 * @param boolean quotesAllowed are quotes (") allowed?
 * @param string separator the separator for multiple usernames
 * @param string username the username to add
 */
function addUserToField(inputId,resultId,multiple,separator,quotesAllowed,username)
{
	if(!username)
		return;
	
 	var field = document.getElementById(inputId);
 	var usedAlt = false;
 	var start = 0;
 	if(multiple)
 	{
	 	var start = field.value.lastIndexOf(separator);
	 	var altStart = -1;
	 	if(quotesAllowed)
	 	{
		 	altStart = field.value.lastIndexOf('"') + 1;
		 	if(altStart > start)
		 		usedAlt = true;
		}
	 	
	 	start = Math.max(altStart,start);
	 	if(start < 0)
	 		start = 0;
 	}
 	
	field.value = field.value.substring(0,start);
	if(start > 0 && !usedAlt && multiple)
		field.value += separator;
	field.value += username;
	
	document.getElementById(resultId).style.display = 'none';
}

/**
 * hides the div-area with the results
 *
 * @param string resultId the id of the result-element
 */
function hideResults(resultId)
{
	FWS_hideElement(resultId);
	FWS_hideElement('wait_symbol');
}

/**
 * catches the keyup-event
 *
 * @param event pEvent the event-parameter
 */
function keyUp(pEvent)
{
	if(!pEvent)
		pEvent = window.event;
	
	var key = -1;
	if(pEvent.which)
		key = pEvent.which;
	else if(pEvent.keyCode)
		key = pEvent.keyCode;

	// typed in our input-field?
	if(__currentObj != null)
	{
 	// strg + space for autocompletion
		if(key == 32 && pEvent.ctrlKey)
		{
			addUserToField(__currentObj.inputId,__currentObj.resultId,
				__currentObj.multiple,__currentObj.separator,
				__currentObj.quotesAllowed,__currentObj.foundUser[0]);
		}
		// escape to close matches
		else if(key == 27)
			hideResults(__currentObj.resultId);
	}
}

document.onkeyup = keyUp;

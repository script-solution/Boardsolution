/**
 * Contains javascript-functions for the BBCode
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

var bsMarkedTags = new Array();
var popups = new Array();
var tagAdded = new Array();
var cp = new Array();

/**
 * Displays the popup for the given tag
 *
 * @param int number the number of the textarea
 * @param string tag the tag-name
 */
function showPopup(number,tag)
{
	var id = 'tag_' + tag + '_' + number + '_popup';
	if(tagAdded[tag + number])
	{
		_insertAtCursor('bbcode_area' + number,"[/" + tag + "]");
		unmarkItem('tag_' + tag + '_' + number);
		tagAdded[tag + number] = 0;
		popups[tag + number] = 0;
	}
	else if(popups[tag + number])
	{
		if(tag == 'color')
			cp[number].hide();
		else
			FWS_hideElement(id);
		popups[tag + number] = 0;
	}
	else
	{
		// KDE positions the popup wrong :/
		// but it works if we remove it from the document and put it at the end of the body
		if(navigator.vendor && navigator.vendor.indexOf('KDE') != -1 && typeof popups[tag] == 'undefined')
		{
			var body = document.getElementsByTagName('body')[0];
			var el = FWS_getElement(id);
			if(el != null)
			{
				var btn = FWS_getElement('tag_' + tag + '_' + number);
				el.parentNode.removeChild(el);
				el.style.position = 'absolute';
				el.style.display = 'block';
				el.style.left = (btn.offsetLeft + body.offsetLeft - el.offsetWidth) + 'px';
				body.appendChild(el);
			}
		}
	
		if(tag == 'color')
			cp[number].toggle('tag_' + tag + '_' + number,'brl');
		else
			FWS_displayElement(id,'tag_' + tag + '_' + number,'brl');
		popups[tag + number] = 1;
	}
}

/**
 * Performs the click-event for the given tag and param
 *
 * @param int number the number of the textarea
 * @param string tag the tag-name
 * @param string param the parameter of the BBCode-Tag
 */
function popupElementClick(number,tag,param)
{
	FWS_hideElement('tag_' + tag + '_' + number + '_popup');
	FWS_getElement('bbcode_area' + number).focus();
	
	var tagimg = FWS_getElement('tag_' + tag + '_' + number);
	var text = param != '' ? '[' + tag + '=' + param + ']' : '[' + tag + ']';
	insertBBCode(number,'bbcode_area' + number,text,tag,tagimg);
	tagAdded[tag + number] = isItemMarked('tag_' + tag + '_' + number);
	if(!tagAdded[tag + number])
		popups[tag + number] = 0;
}

/**
 * changes the bbcode-mode to given value
 * 
 * @param int number the number of the textarea
 * @param string mode the new mode
 * @param object the form-object to send an AJAX-request
 */
function changeBBCodeMode(number,mode,form)
{
	if(BBCodeModes[number] != 'applet' && mode == 'applet')
		form.getPostForm(mode);
	else if(BBCodeModes[number] == 'applet' && mode != 'applet')
		form.getPostForm(mode);
	
	BBCodeModes[number] = mode;
}

/**
 * Hovers over an item with given id
 *
 * @param int number the number of the textarea
 * @param mixed id the id of the item
 * @param string hoverText the text to display
 */
function hoverItem(number,id,hoverText)
{
	var item = document.getElementById(id);
	item.style.cursor = 'pointer';
	document.getElementById('bbc_explain_' + number).value = hoverText;
	if(item != null && item.src && !bsMarkedTags.contains(id))
		item.style.borderColor = '#555555';
}

/**
 * Undos the hover-effect over an item with given id
 *
 * @param int number the number of the textarea
 * @param mixed id the id of the item
 */
function unhoverItem(number,id)
{
	var item = document.getElementById(id);
	item.style.cursor = 'default';
	document.getElementById('bbc_explain_' + number).value = '';
	if(item != null && item.src && !bsMarkedTags.contains(id))
		item.style.borderColor = '#AAAAAA';
}

/**
 * Checks wether the given item is marked
 *
 * @param mixed id the id of the item
 */
function isItemMarked(id)
{
	return bsMarkedTags.contains(id);
}

/**
 * Marks the item with given id
 *
 * @param mixed id the id of the item
 */
function markItem(id)
{
	var item = document.getElementById(id);
	if(item != null)
	{
		item.style.borderColor = '#FF0000';
		if(!Browser.isOpera)
			item.style.borderStyle = 'dotted';
		bsMarkedTags.push(id);
	}
}

/**
 * Unmarks the item with given id
 *
 * @param mixed id the id of the item
 */
function unmarkItem(id)
{
	var item = document.getElementById(id);
	if(item != null)
	{
		item.style.borderColor = '#AAAAAA';
		item.style.borderStyle = 'solid';
		bsMarkedTags.removeEntry(id);
	}
}

/**
 * inserts a smiley at the current position in the textarea
 *
 * @param int id the id of the textfield
 * @param string smiley the smiley-code
 */
function insertSmiley(id,smiley)
{
	var textarea = document.getElementById(id);
	if(smiley)
	{
		_insertAtCursor(id,smiley);
		textarea.focus();
	}
}

/**
 * inserts a bbcode at the current position in the textarea
 *
 * @param int number the number of the textarea
 * @param int id the id of the textfield
 * @param string text the text to insert (the start-tag)
 * @param string tag the name of the tag
 * @param object object the button or null
 */
function insertBBCode(number,id,text,tag,object)
{
	var textarea = document.getElementById(id);
	if(text)
	{
		if(BBCodeModes[number] == 'advanced')
		{
			if(!_surroundMarkedText(textarea,tag,text))
			{
				if(object != null)
					_pasteTag(id,object,tag,text);
				else
				{
					// insert the start and end-tag and put the cursor between them
					_insertAtCursor(id,text);
					var str = '[/' + tag + ']';
					_insertAtCursor(id,str);
					moveCursorForward(id,-str.length);
				}
			}
		}
		else
		{
			text = text.trim();
			if(_getSelection(id) != "")
				_surroundMarkedText(textarea,tag,text);
			else
			{
				var tagData = _getTagData(tag);
				if(tagData != null && tagData["prompt_text"] == "")
					_pasteTag(id,object,tag,text);
				else
					_showBBCodePrompt(id,text,tagData);
			}
		}
		
		textarea.focus();
	}
}

/**
 * closes all currently open tags in the textfield with given id
 *
 * @param int number the number of the textarea
 * @param int id the id of the textfield
 */
function closeBBCodeTags(number,id)
{
	var textarea = document.getElementById(id);
	var lower_text = textarea.value.toLowerCase();
	var pos;
	var tags = new Array();
	for(var x = 0;x < BBCODE.length;x++)
	{
		// determine the number of opening and closing occurrences of this tag
		var openingCount = 0;
		var closingCount = 0;
		
		switch(BBCODE[x]["param"])
		{
			case "no":
				openingCount = lower_text.substr_count("[" + BBCODE[x]["tag"] + "]");
				pos = lower_text.indexOf("[" + BBCODE[x]["tag"] + "]");
				break;
			
			case "optional":
				openingCount = lower_text.substr_count("[" + BBCODE[x]["tag"] + "]");
				openingCount += lower_text.substr_count("[" + BBCODE[x]["tag"] + "=");
				var p1 = lower_text.indexOf("[" + BBCODE[x]["tag"] + "]");
				var p2 = lower_text.indexOf("[" + BBCODE[x]["tag"] + "=");
				if(p1 >= 0 && p2 >= 0)
					pos = Math.min(p1,p2);
				else if(p1 >= 0)
					pos = p1;
				else
					pos = p2;
				break;
			
			default:
				openingCount = lower_text.substr_count("[" + BBCODE[x]["tag"] + "=");
				pos = lower_text.indexOf("[" + BBCODE[x]["tag"] + "=");
				break;
		}
		
		var closing = "[/" + BBCODE[x]["tag"] + "]";
		closingCount = lower_text.substr_count(closing);
		
		// missing closing tag?
		if(openingCount > closingCount)
			tags.push(new Array(pos,closing));
	}
	
	// ensure that we add the closing tags in the correct order
	tags.sort(_numericSort);
	tags.reverse();

	// add the tags to the end of the textarea
	for(var i = 0;i < tags.length;i++)
		textarea.value += tags[i][1];

	// unmark the buttons
	for(var i = 0;i < BBCODE.length;i++)
	{
		var object = document.getElementById("tag_" + BBCODE[i]["tag"] + "_" + number);
		if(object != null)
		{
			if(bsMarkedTags.contains(object.id))
				unmarkItem(object.id);
		}
	}
}

/**
 * tries to find the tag-data of the given tag
 *
 * @param string tag the tag
 * @return array the data from the BBCODE-array
 */
function _getTagData(tag)
{
	for(var i = 0;i < BBCODE.length;i++)
	{
		if(BBCODE[i]["tag"] == tag)
			return BBCODE[i];
	}
	
	return null;
}

/**
 * determines the current selection in the textfield with given id
 * 
 * @param int id the id of the textfield
 * @return string the selection
 */
function _getSelection(id)
{
	if(typeof document.selection != 'undefined')
		return document.selection.createRange().text;

	var textarea = document.getElementById(id);
	if(typeof textarea.selectionStart != 'undefined')
		return textarea.value.substring(textarea.selectionStart,textarea.selectionEnd);

	return "";
}

/**
 * inserts the given text in the textfield with given id at the position of the cursor
 *
 * @param int id the id of the textfield
 * @param string text the text to insert
 */
function _insertAtCursor(id,text)
{
	var textarea = document.getElementById(id);
	var scroll_top = textarea.scrollTop;
	textarea.focus();

	// for IE and opera
	if(typeof document.selection != 'undefined')
	{
		// paste the text
		var range = document.selection.createRange();
		range.text = text;
	}
	// for the gecko-engine
	else if(typeof textarea.selectionStart != 'undefined')
	{
		// paste the text
		var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		var content = textarea.value;
		textarea.value = content.substring(0,start);
		textarea.value += text;
		textarea.value += content.substring(end,content.length);
	}
	// for all other browser
	else
	{
		// just paste the text at the end
		textarea.value += text;
	}
	
	textarea.scrollTop = scroll_top;
}

/**
 * Moves the cursor by the given amount forward (or backwards, if negative)
 *
 * @param mixed id the id of the textField
 * @param int count the number of characters to move
 */
function moveCursorForward(id,count)
{
	var textarea = document.getElementById(id);
	// for opera
	if(typeof document.selection != 'undefined' && Browser.isOpera)
		range.move('character',count);
	// for the gecko-engine
	else if(typeof textarea.selectionStart != 'undefined')
	{
		var pos = textarea.selectionStart + count;
		textarea.selectionStart = pos;
		textarea.selectionEnd = pos;
	}
}

/**
 * surrounds the selected text with the given tag
 *
 * @param object textarea the textarea
 * @param string tag the affected tag
 * @param string startTag the startTag to paste (to allow parameters)
 * @return boolean true if it was successfull
 */
function _surroundMarkedText(textarea,tag,startTag)
{
	startTag = startTag.trim();
	var scroll_top = textarea.scrollTop;

	var EndTag = "[/" + tag + "]";
	if(EndTag == "")
		return false;

	if(document.selection)
	{
		textarea.focus();
		var selection = document.selection.createRange().text;
		if(selection != "")
		{
			var range = document.selection.createRange();
			range.text = startTag + selection + EndTag;
			range.move('character',range.text);
			range.select();
			textarea.scrollTop = scroll_top;
			return true;
		}
		textarea.scrollTop = scroll_top;
		return false;
	}

	var content = textarea.value;
	if(textarea.selectionEnd != textarea.selectionStart)
	{
		var SelStart = textarea.selectionStart;
		var SelEnd = textarea.selectionEnd;
		textarea.value = content.substring(0,SelStart);
		textarea.value += startTag + content.substring(SelStart,SelEnd) + EndTag;
		textarea.value += content.substring(SelEnd,content.length);
		textarea.scrollTop = scroll_top;
		return true;
	}

	textarea.scrollTop = scroll_top;
	return false;
}

/**
 * inserts the given tag in the textfield. if the tag is currently open
 * (or lets say, the button has a * at the end) the tag will be closed
 * otherwise the given text will be inserted and the button will be "marked"
 *
 * @param int id the id of the textfield
 * @param object object the button or null
 * @param string tag the affected tag
 * @param string text the text to paste
 */
function _pasteTag(id,object,tag,text)
{
	if(object == null)
		_insertAtCursor(id,text);
	else
	{
		if(bsMarkedTags.contains(object.id))
		{
			_insertAtCursor(id,"[/" + tag + "]");
			unmarkItem(object.id);
		}
		else
		{
			_insertAtCursor(id,text);
			markItem(object.id);
		}
	}
}

/**
 * displays the bbcode-prompt for the given tagData (for the simple-mode)
 * 
 * @param int id the id of the textfield
 * @param string startTag the startTag to paste
 * @param array tagData the data of the tag from the BBCODE-array
 */
function _showBBCodePrompt(id,startTag,tagData)
{
	var textarea = document.getElementById(id);
	if(tagData["prompt_param_text"] != "")
	{
		var param = prompt(tagData["prompt_param_text"],"");
		if(param != null && param != "")
		{
			var text = prompt(tagData["prompt_text"],"");
			if(text != null)
				_insertAtCursor(id,"[" + tagData["tag"] + "=" + param + "]" + text + "[/" + tagData["tag"] + "]");
		}
	}
	else
	{
		var text = prompt(tagData["prompt_text"],"");
		if(text != null)
			_insertAtCursor(id,startTag + text + "[/" + tagData["tag"] + "]");
	}
}

/**
 * the sort-function for the closing tags
 * will sort the tags in the order of the tag-positions
 *
 * @param array a the first element
 * @param array b the second element
 * @return int a negative value if b > a, a positive value if a > b, or 0 if a == b
 */
function _numericSort(a,b)
{
	return a[0] - b[0];
}
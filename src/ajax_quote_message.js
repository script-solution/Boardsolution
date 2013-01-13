/**
 * Contains the AJAX-request to quote a message
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

/**
 * constructor for the quote-message (post,pm) AJAX-request
 *
 * @param int number the number of the textarea
 * @param string fieldID the id of the textarea
 * @param string requestURL the url of the PHP-script
 */
function BS_quoteMessageAJAX(number,fieldID,requestURL)
{
	// fields
	this.number = number;
	this.bbcFieldID = fieldID;
	this.requestURL = requestURL;
	
	// methods
	this.quoteMessage = quoteMessage;
}

/**
 * starts the AJAX-request
 *
 * @param int mID the id of the message to quote
 */
function quoteMessage(mID)
{
	var self = this;
	var url = this.requestURL.replace(/__ID__/,mID);
	myAjax.sendGetRequest(url,function(text) {
		var field = document.getElementById(self.bbcFieldID);
		if(field != null)
		{
			if(BBCodeModes[self.number] == 'applet')
			{
				var applet = document.getElementById(self.bbcFieldID);
				applet.insertText(text);
			}
			else
				_insertAtCursor(self.bbcFieldID,"\n" + text + "\n");
		}
	});
}
/**
 * Contains the AJAX-request to quote a message
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
/**
 * Contains the AJAX-request to change the post-form (BBCode-mode)
 * 
 * @version			$Id: ajax_get_post_form.js 569 2008-04-14 08:59:19Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * constructor for the get-post-form AJAX-request
 *
 * @param string postFormID the id of the post-form
 * @param string fieldID the id of the textarea
 * @param string requestURL the url of the PHP-script
 */
function BS_getPostFormAJAXConstr(postFormID,fieldID,requestURL)
{
	// fields
	this.postFormID = postFormID;
	this.bbcFieldID = fieldID;
	this.requestURL = requestURL;
	this.type = '';
	
	// methods
	this.getPostForm = getPostForm;
}

/**
 * starts the AJAX-request
 *
 * @param string type the type to request: simple, advanced or applet
 */
function getPostForm(type)
{
	this.type = type;
	var url = this.requestURL.replace(/%s%/,type);
	var self = this;
	
	myAjax.sendGetRequest(url,function(text) {
		var form = document.getElementById(self.postFormID);
		var field = document.getElementById(self.bbcFieldID);
		if(field != null && form != null)
		{
			var ftext = '';
			if(self.type == 'applet')
				ftext = field.value;
			else if(field.getBBCode || typeof field.getBBCode == 'function')
				ftext = field.getBBCode();
			
			form.innerHTML = text;
			
			var myint = window.setInterval(function() {
				field = document.getElementById(self.bbcFieldID);
				// we have to "wait" until the applet is loaded
				if(self.type != 'applet' || typeof field.insertText == 'function')
				{
					if(self.type == 'applet')
						field.insertText(ftext);
					else
						field.value = ftext;
					window.clearInterval(myint);
				}
			},50);
		}
	});
}
/**
 * Contains functions for quoting posts.
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

var quotedPosts = new Array();

/**
 * Toggles the given post-id in the quoted-posts-array
 *
 * @param int postId the post-id
 * @param string quoteLink the link for the AJAX-quote-request
 */
function toggleQuote(quoteLink,postId)
{
	var link = FWS_getElement('quote_link_' + postId);
	if(quotedPosts.contains(postId))
	{
		FWS_removeClassName(link,'bs_button_selected');
		quotedPosts.removeEntry(postId);
		link.innerHTML = link.innerHTML.substr(0,link.innerHTML.length - 1) + '+';
	
		if(quotedPosts.length == 0)
		{
			FWS_removeClassName(FWS_getElement('reply_btn_1'),'bs_button_selected');
			FWS_removeClassName(FWS_getElement('reply_btn_2'),'bs_button_selected');
		}
	}
	else
	{
		FWS_addClassName(link,'bs_button_selected');
		quotedPosts.push(postId);
		link.innerHTML = link.innerHTML.substr(0,link.innerHTML.length - 1) + '-';
		
		if(typeof FWS_getElement('quickReply') != 'undefined')
		{
			var qm = new BS_quoteMessageAJAX(1,'quickReply_text',quoteLink);
			FWS_showElement('quickReply');
			qm.quoteMessage(postId);
		}
	
		if(quotedPosts.length == 1)
		{
			FWS_addClassName(FWS_getElement('reply_btn_1'),'bs_button_selected');
			FWS_addClassName(FWS_getElement('reply_btn_2'),'bs_button_selected');
		}
	}
}

/**
 * Adds the marked post-ids to the given link and changes the location to the URL
 * 
 * @param string quoteLink the URL
 * @return boolean false if the location has been changed
 */
function quote(quoteLink)
{
	if(quotedPosts.length > 0)
	{
		quoteLink = quoteLink.replace(/__PID__/,quotedPosts.join(','));
		document.location.href = quoteLink;
		return false;
	}
	return true;
}

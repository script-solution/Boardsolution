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
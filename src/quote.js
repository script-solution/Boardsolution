var quotedPosts = new Array();

/**
 * Toggles the given post-id in the quoted-posts-array
 *
 * @param int postId the post-id
 */
function toggleQuote(postId)
{
	var link = FWS_getElement('quote_link_' + postId);
	if(quotedPosts.contains(postId))
	{
		FWS_removeClassName(link,'bs_button_selected');
		quotedPosts.removeEntry(postId);
		link.innerHTML = link.innerHTML.substr(0,link.innerHTML.length - 1) + '+';
	}
	else
	{
		FWS_addClassName(link,'bs_button_selected');
		quotedPosts.push(postId);
		link.innerHTML = link.innerHTML.substr(0,link.innerHTML.length - 1) + '-';
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
		quoteLink += quotedPosts.join(',');
		document.location.href = quoteLink;
		return false;
	}
	return true;
}
/**
 * Contains general javascript-functions
 * 
 * @version			$Id: functions.js 701 2008-05-14 13:37:15Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * performs a mod-action for some topics
 * collects the ids from the checkboxes and redirects to the given url
 *
 * @param string base_url the base_url ending with &amp;URL_ID=
 */
function performModAction(base_url)
{
	if(base_url == '')
		return;
	
	var ids = "";
	var cb = null;
	for(var i = 0;(cb = document.getElementById('id_' + i)) != null;i++)
	{
		if(cb.checked)
			ids += cb.value + ",";
	}

	if(ids == "")
		return;

	document.location.href = base_url + ids;
}

/**
 * claps the area with given id including image and cookie
 * 
 * @param string id the id of the element
 * @param string img_id the id of the image
 * @param string cookie_name the name of the cookie where to store the current status
 * @param string display_value the value for "display" if the element should be displayed
 * 							   (default = 'block')
 */
function clapArea(id,img_id,cookie_name,display_value)
{
	if(typeof display_value == 'undefined')
		display_value = 'block';
	
	var area = document.getElementById(id);
	var display;
	if(area.style.display == 'none')
	{
		if(display_value != 'block')
			display = document.all && !window.opera ? 'block' : display_value;
		else
			display = display_value;
	}
	else
		display = 'none';
	
	area.style.display = display;
	
	setCookie(cookie_name,display == 'none' ? '0' : '1',86400 * 30);

	var img = document.images[img_id];
	if(img != null)
	{
		if(display == "none")
			img.src = img.src.replace(/crossopen/,'crossclosed');
		else
			img.src = img.src.replace(/crossclosed/,'crossopen');
	}
}

/**
 * claps the forum with given id
 *
 * @param int id the id of the forum
 * @param string cookiePrefix the cookie-prefix
 */
function clapForum(id,cookiePrefix)
{
	var setcookies = '';
	var subForums = document.getElementById('subForums_' + id);
	
	if(subForums.style.display == 'none')
	{
		var cookieParts = global_cookie.split(',');
		for(var i = 0;i < cookieParts.length;i++)
		{
			if(cookieParts[i] != '' && cookieParts[i] != id)
				setcookies += cookieParts[i] + ',';
		}
		
		subForums.style.display = 'block';
	}
	else
	{
		setcookies = global_cookie + id + ',';
		subForums.style.display = 'none';
	}

	setCookie(cookiePrefix + 'hidden_forums',setcookies,86400 * 30);
	global_cookie = setcookies;
	
	var image = document.getElementById('cross_' + id);
	if(subForums.style.display == "none")
		image.src = image.src.replace(/crossopen/,'crossclosed');
	else
		image.src = image.src.replace(/crossclosed/,'crossopen');
}

/**
 * inverts all checkboxes with the following id:
 * <code>&lt;prefix&gt;&lt;number&gt; , &lt;number&gt; ? {start, start + 1, ...}</code>
 *
 * @param string prefix the prefix of the checkbox-ids
 * @param int start the start-number
 * @param boolean nullNotRequired if enabled the id "&lt;prefix&gt;0" is not required
 */
function invertSelection(prefix,start,nullNotRequired)
{
	if(!start)
		start = 0;

	var f;
	for(var i = start;;i++)
	{
		f = document.getElementById(prefix + i);
		if(f == null && (!nullNotRequired || i > 0))
			return;

		if(f != null)
			f.checked = !f.checked;
	}
}

/**
 * inverts the enable-status of the agreement button at the registration
 *
 * @param object checkbox the checkbox
 * @param string submit_id the id of the button
 */
function checkAgreement(checkbox,submit_id)
{
	var f = document.getElementById(submit_id);
	if(checkbox.checked)
	{
		f.disabled = false;
		f.style.color = '#000000';
	}
	else
	{
		f.disabled = true;
		f.style.color = '#BBBBBB';
	}
}

/**
 * toggles the display-status of the div-area with given id
 *
 * @param int id the id of the div-area
 */
function toggleArea(id)
{
	var div = document.getElementById(id);
	div.style.display = div.style.display == 'none' ? 'block' : 'none';
}

/**
 * analyses the given password and tries to rate it
 * 
 * @param string pw the password to check
 * @return string the rating: low,medium,high,veryhigh
 */
function analysePassword(pw)
{
	var uppercase = 0;
	var lowercase = 0;
	var numbers = 0;
	var other = 0;
	
	for(var i = 0;i < pw.length;i++)
	{
		var c = pw.charAt(i);
		
		if(Number(c))
			numbers++;
		else if(c >= 'a' && c <= 'z')
			lowercase++; 
		else if(c >= 'A' && c <= 'Z')
			uppercase++;
		else
			other++;
	}
	
	var mixed_number = 0;
	if(uppercase > 0)
		mixed_number++;
	if(lowercase > 0)
		mixed_number++;
	if(numbers > 0)
		mixed_number++;
	if(other > 0)
		mixed_number++;
	
	var complex = (pw.length / 3) * (mixed_number / 3);
	if(complex < 1)
		complexity = 'low';
	else if(complex < 2)
		complexity = 'medium';
	else if(complex < 3)
		complexity = 'high';
	else
		complexity = 'veryhigh';
	
	return complexity;
}

/**
 * Reloads the given image
 *
 * @param object object the image-object
 */
function reloadImage(object)
{
	var url;
	var matches = object.src.match(/dummy=(\d+)/);
	if(matches)
		url = object.src.replace(/dummy=(\d+)/,"dummy=" + (parseInt(matches[1]) + 1));
	else
	{
		if(object.src.indexOf("?") >= 0)
			url = object.src + "&dummy=1";
		else
			url = object.src + "?dummy=1";
	}
	
	object.src = url;
}
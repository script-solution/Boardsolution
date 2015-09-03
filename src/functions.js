/**
 * Contains general javascript-functions
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
 * performs a mod-action for some topics
 * collects the ids from the checkboxes and redirects to the given url
 *
 * @param string base_url the base_url containing __ID__ as placeholder
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

	document.location.href = base_url.replace(/__ID__/,ids);
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
			display = Browser.isIE ? 'block' : display_value;
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
		if(typeof cookiePrefix != 'undefined')
		{
			var cookieParts = global_cookie.split(',');
			for(var i = 0;i < cookieParts.length;i++)
			{
				if(cookieParts[i] != '' && cookieParts[i] != id)
					setcookies += cookieParts[i] + ',';
			}
		}
		
		subForums.style.display = 'block';
	}
	else
	{
		if(typeof cookiePrefix != 'undefined')
			setcookies = global_cookie + id + ',';
		subForums.style.display = 'none';
	}

	if(typeof cookiePrefix != 'undefined')
	{
		setCookie(cookiePrefix + 'hidden_forums',setcookies,86400 * 30);
		global_cookie = setcookies;
	}
	
	var div = document.getElementById('cross_' + id);
	if(subForums.style.display == "none")
		div.className = 'fa fa-plus fa-2x bs_plus_minus';
	else
		div.className = 'fa fa-minus fa-2x bs_plus_minus';
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
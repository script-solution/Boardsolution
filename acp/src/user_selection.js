/**
 * Contains the javascript-functions to select users in a popup
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
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
 * Transfers the user from the comboBox to the hidden-field in the formular
 *
 * @param string comboID the id of the comboBox
 * @param string selUserID the id of the hidden-field
 * @param string formID the id of the formular
 */
function transferSelectedUser(comboID,selUserID,formID)
{
	var str = '';
	var combo = document.getElementById(comboID);
	for(var i = 0;i < combo.length;i++)
		str += combo.options[i].value + ',';

	document.getElementById(selUserID).value = str;

	document.getElementById(formID).submit();
}

/**
 * Removes the selected user in the comboBox
 *
 * @param string comboID the id of the comboBox
 */
function removeMarkedUser(comboID)
{
	var rem = new Array();
	var a = 0;
	var combo = document.getElementById(comboID);
	for(var i = 0;i < combo.length;i++)
	{
		if(combo.options[i].selected)
			rem[a++] = i;
	}

	for(var i = rem.length - 1;i >= 0;i--)
		combo.options[rem[i]] = null;
}

/**
 * Adds the given user to the comboBox
 *
 * @param string comboID the id of the comboBox
 * @param array selectedUser the selected users
 */
function addUserToCombo(comboID,selectedUser)
{
	var combo = document.getElementById(comboID);

	for(var id in selectedUser)
	{
		if(typeof selectedUser[id] == 'string' && !_entryExists(combo,id))
		{
			var newEntry = new Option(selectedUser[id],id,false,false);
			combo.options[combo.length] = newEntry;
		}
	}
}

/**
 * Checks wether the given value exists in the given comboBox
 *
 * @param object combo the comboBox
 * @param mixed val the value to check
 * @return boolean true if the value already exists
 */
function _entryExists(combo,val)
{
	for(var i = 0;i < combo.length;i++)
	{
		if(combo.options[i].value == val)
			return true;
	}

	return false;
}
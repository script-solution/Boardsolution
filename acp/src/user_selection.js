/**
 * Contains the javascript-functions to select users in a popup
 * 
 * @version			$Id: user_selection.js 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
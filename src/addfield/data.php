<?php
/**
 * Contains the addfield-data-class
 *
 * @version			$Id: data.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The data for additional-fields in BS
 *
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_AddField_Data extends PLIB_AddField_Data
{
	public function get_title()
	{
		// You can use this to display a language-dependend field-title. For example:
		// return $this->locale->lang('addfield_'.parent::get_name());
		// Now you just have to insert the field-names in the language-files:
		// $LANG['addfield_FIELDNAME'] = 'Your name';
		// Where FIELDNAME is the name (not the displayed name!) of the field
		
		return parent::get_title();
	}
}
?>
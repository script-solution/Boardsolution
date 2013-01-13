<?php
/**
 * Contains the addfield-data-class
 * 
 * @package			Boardsolution
 * @subpackage	src.addfield
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
 * The data for additional-fields in BS
 *
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_AddField_Data extends FWS_AddField_Data
{
	public function get_title()
	{
		// You can use this to display a language-dependend field-title. For example:
		// return FWS_Props::get()->locale()->lang('addfield_'.parent::get_name());
		// Now you just have to insert the field-names in the language-files:
		// addfield_FIELDNAME = "Your name"
		// Where FIELDNAME is the name (not the displayed name!) of the field
		
		return parent::get_title();
	}
}
?>
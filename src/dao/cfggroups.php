<?php
/**
 * Contains the cfg-groups-dao-class
 * 
 * @package			Boardsolution
 * @subpackage	src.dao
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
 * The DAO-class for the cfg-groups-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_CFGGroups extends FWS_Singleton
{
	/**
	 * @return BS_DAO_CFGGroups the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return array an array with all groups, sorted by "sort" ascending
	 */
	public function get_all()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT * FROM '.BS_TB_CONFIG_GROUPS.' ORDER BY sort ASC'
		);
	}
}
?>
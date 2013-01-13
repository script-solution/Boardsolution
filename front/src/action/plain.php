<?php
/**
 * Contains the plain-action-base-class
 * 
 * @package			Boardsolution
 * @subpackage	front.src.action
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
 * This is the base-class of all plain-actions. Plain-actions are intended for being able to
 * perform some often needed actions programmatically. That means you can specify all data for
 * this action without reading it from POST/GET.
 * <br>
 * The method check_data() checks all necessary stuff to ensure the consistency
 * of the database. It checks NOT wether the user has permission to perform this action
 * with the specified data!
 * <br>
 * Note that you have to make sure that the data which will be inserted into the database is
 * escaped! Because by default this happens in {@link FWS_Input}. If you specify the values
 * manually you have to escape them manually, too.
 * 
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Action_Plain extends FWS_Object
{
	/**
	 * Stores wether the data has been checked
	 *
	 * @var boolean
	 */
	private $_data_checked = false;
	
	/**
	 * Checks all necessary stuff to ensure the consistency of the database. It checks NOT wether
	 * the user has permission to perform this action with the specified data!
	 *
	 * @return string the error-message or an empty string
	 */
	public function check_data()
	{
		$this->_data_checked = true;
		return '';
	}
	
	/**
	 * This method performs the action. It requires that you've called check_data() first!
	 */
	public function perform_action()
	{
		if(!$this->_data_checked)
			FWS_Helper::error('You have to check the data first via check_data() (and that it'
				.' has not detected an error)!');
	}
}
?>
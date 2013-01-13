<?php
/**
 * Contains the front-base-action-class
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
 * The base-action-class for all front-actions
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Action_Base extends FWS_Action_Base
{
	/**
	 * An array for all actions that defines for which action a status-page should be displayed
	 *
	 * @var array
	 */
	static $_action_msgs = null;
	
	/**
	 * Includes the file config/actions.php and stores the action-msgs in a class-attribute
	 */
	public static function load_actions()
	{
		$action_msgs = array();
		include_once(FWS_Path::server_app().'config/actions.php');
		self::$_action_msgs = $action_msgs;
	}
	
	/**
	 * Constructor
	 * 
	 * @param int $id the id of the action
	 */
	public function __construct($id)
	{
		parent::__construct($id);
		
		// set wether we want to show a status-page
		if(isset(self::$_action_msgs[$this->get_action_id()]))
			$this->set_show_status_page(self::$_action_msgs[$this->get_action_id()]);
	}
}
?>
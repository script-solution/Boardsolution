<?php
/**
 * Contains the action-performer
 * 
 * @package			Boardsolution
 * @subpackage	acp.src.action
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
 * The action-performer. We overwrite it to provide a custom get_action_id()
 * method.
 *
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_Performer extends FWS_Action_Performer implements FWS_Action_Listener
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->set_mod_folder('acp/module/');
		$this->set_prefix('BS_ACP_Action_');
		$this->set_listener($this);
	}
	
	/**
	 * @see FWS_Action_Performer::get_action_id()
	 *
	 * @return int
	 */
	protected function get_action_id()
	{
		$input = FWS_Props::get()->input();

		$action_type = $input->get_var('action_type','post',FWS_Input::INTEGER);
		if($action_type === null)
			$action_type = $input->get_var('at','get',FWS_Input::INTEGER);

		return $action_type;
	}
	/**
	 * @see FWS_Action_Listener::before_action_performed()
	 *
	 * @param int $id
	 * @param FWS_Action_Base $action
	 */
	public function before_action_performed($id,$action)
	{
		// we have to add the messages-file if an action should be performed
		$locale = FWS_Props::get()->locale();
		$locale->add_language_file('messages');
	}
	
	/**
	 * @see FWS_Action_Listener::after_action_performed()
	 *
	 * @param int $id
	 * @param FWS_Action_Base $action
	 * @param string $message
	 */
	public function after_action_performed($id,$action,&$message)
	{
		// do nothing
	}
}
?>
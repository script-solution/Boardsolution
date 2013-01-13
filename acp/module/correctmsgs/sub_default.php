<?php
/**
 * Contains the default-submodule for correctmsgs
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The default sub-module for the correctmsgs-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_correctmsgs_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$user->delete_session_data('im_data');
		$incorrect = BS_ACP_Module_CorrectMsgs_Helper::get_incorrect_messages();
		
		$url = BS_URL::get_acpsub_url(0,'cycle');
		$url->set('pos',0);
		
		$tpl->add_variables(array(
			'target' => $url->to_url(),
			'incorrect_messages' => sprintf($locale->lang('incorrect_messages'),count($incorrect)),
			'incorrect_num' => count($incorrect)
		));
	}
}
?>
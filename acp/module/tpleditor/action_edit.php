<?php
/**
 * Contains the edit-tpleditor-action
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
 * The edit-tpleditor-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_tpleditor_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		
		$file = $input->get_var('file','get',FWS_Input::STRING);
		$content = $input->get_var('file_content','post',FWS_Input::STRING);
		
		if($fp = @fopen($path.'/'.$file,'w'))
		{
			flock($fp,LOCK_EX);
			$content = FWS_StringHelper::htmlspecialchars_back(stripslashes(trim($content)));
			fwrite($fp,$content);
			flock($fp,LOCK_UN);
			fclose($fp);
		}
		else
			return 'template_edit_failed';
		
		$this->set_success_msg($locale->lang('template_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>
<?php
/**
 * Contains the advancedsave-themes-action
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
 * The advancedsave-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_advancedsave extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$file = $input->get_var('file','get',FWS_Input::STRING);
		if($file == '' || !preg_match('/^[a-zA-Z0-9_]+\.css$/',$file))
			return 'Invalid filename!';
		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$path = FWS_Path::server_app().'themes/'.$theme.'/'.$file;
		
		$content = $input->get_var('file_content','post',FWS_Input::STRING);
		$content = FWS_StringHelper::htmlspecialchars_back(stripslashes(trim($content)));
		
		if(!FWS_FileUtils::write($path,$content))
			return sprintf($locale->lang('file_not_saved'),$path);
		
		$this->set_action_performed(true);
		$this->set_success_msg($locale->lang('file_saved'));

		return '';
	}
}
?>
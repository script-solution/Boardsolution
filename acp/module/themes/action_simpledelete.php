<?php
/**
 * Contains the simpledelete-themes-action
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
 * The simpledelete-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_simpledelete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = FWS_Path::server_app().'themes/'.$theme.'/basic.css';
		$css = new FWS_CSS_StyleSheet(FWS_FileUtils::read($file));
		
		$split = explode(',',$input->get_var('ids','get',FWS_Input::STRING));
		for($i = 0;$i < count($split);$i++)
		{
			if($split[$i] != '')
			{
				$explode = explode('|',$split[$i]);
				$block = $css->get_block($explode[0]);
				if($block !== null && $block->get_type() == FWS_CSS_Block::RULESET)
					$block->remove_property($explode[1]);
			}
		}

		if(!FWS_FileUtils::write($file,(string)$css))
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_success_msg($locale->lang('file_saved'));
		$this->set_action_performed(true);

		return '';
	}
}
?>
<?php
/**
 * Contains the simplesave-themes-action
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
 * The simplesave-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_simplesave extends BS_ACP_Action_Base
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
		foreach($input->get_vars_from_method('post') as $key => $value)
		{
			if(FWS_String::strpos($key,'|') !== false)
			{
				$split = explode('|',$key);
				list(,$blockno) = explode('_',$split[0]);
				if(isset($split[2]))
				{
					$val = $input->get_var($split[0].'|'.$split[1].'|val','post');
					$type = $input->get_var($split[0].'|'.$split[1].'|type','post');
					$value = $val.$type;
				}
				$block = $css->get_block($blockno);
				if($block->get_type() == FWS_CSS_Block::RULESET)
					$block->set_property($split[1],$input->unescape_value($value,'post'));
			}
		}
		
		if(!FWS_FileUtils::write($file,(string)$css))
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_action_performed(true);
		$this->set_success_msg($locale->lang('file_saved'));
		
		return '';
	}
}
?>
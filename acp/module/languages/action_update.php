<?php
/**
 * Contains the update-languages-action
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
 * The update-languages-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_languages_update extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$names = $input->get_var('names','post');
		$folders = $input->get_var('folders','post');
		if(count($names) == 0 || count($folders) == 0 || count($names) != count($folders))
			return 'Invalid POST-variables "names" and "folders". No array? Empty? Size not equal?';
		
		$count = 0;
		foreach($names as $id => $value)
		{
			$data = $cache->get_cache('languages')->get_element($id);
			if(FWS_Helper::is_integer($id) && isset($folders[$id]) &&
				($data['lang_name'] != $value || $data['lang_folder'] != $folders[$id]))
			{
				BS_DAO::get_langs()->update_by_id($id,$value,$folders[$id]);
				$count++;
			}
		}

		if($count > 0)
			$cache->refresh('languages');

		$this->set_success_msg($locale->lang('langs_updated_notice'));
		$this->set_action_performed(true);

		return '';
	}
}
?>
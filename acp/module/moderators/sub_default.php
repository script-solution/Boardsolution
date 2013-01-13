<?php
/**
 * Contains the default-submodule for moderators
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
 * The default sub-module for the moderators-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_moderators_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_ADD_MODERATORS,'add');
		$renderer->add_action(BS_ACP_ACTION_REMOVE_MODERATORS,'remove');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$cache = FWS_Props::get()->cache();
		$forums = FWS_Props::get()->forums();

		$nodes = $forums->get_all_nodes();
		$num = count($nodes);
		$tplforums = array();
		
		$hiddenfields = BS_URL::get_acpmod_comps();
		$hiddenfields['action'] = 'edituser';
		
		$url = BS_URL::get_acpmod_url('usersearch','&');
		$url->set('comboid','__cid__');
		$tpl->add_variables(array(
			'search_url' => $url->to_url(),
			'action_param' => BS_URL_ACTION,
			'hiddenfields' => $hiddenfields
		));
		
		if($num == 0)
			$msgs->add_notice($locale->lang('create_forum_first'));
		else
		{
			$sub_cats = array();

			$images = array(
				'dot' => 'acp/images/forums/path_dot.gif',
				'middle' => 'acp/images/forums/path_middle.gif'
			);
			
			$delurl = BS_URL::get_acpsub_url();
			$delurl->set('at',BS_ACP_ACTION_REMOVE_MODERATORS);
			
			$mods = $cache->get_cache('moderators');
			foreach($nodes as $node)
			{
				$data = $node->get_data();
				if(!isset($sub_cats[$data->get_parent_id()]))
					$sub_cats[$data->get_parent_id()] = 1;
				else
					$sub_cats[$data->get_parent_id()]++;

				$space = '';
				for($x = 0;$x < $node->get_layer();$x++)
					$space .= '---';

				$moderators = '';
				if($data->get_forum_type() == 'contains_threads')
				{
					$forum_mods = $mods->get_elements_with(array('rid' => $data->get_id()));
					$mod_num = count($forum_mods);
					if($mod_num > 0)
					{
						$x = 0;
						foreach($forum_mods as $mdata)
						{
							$moderators .= $mdata['user_name'];
							$delurl->set('f',$data->get_id());
							$delurl->set('uid',$mdata['user_id']);
							$moderators .= ' <a href="'.$delurl->to_url().'">';
							$moderators .= '<img src="'.FWS_Path::client_app().'acp/images/delete_small.png"';
							$moderators .= ' alt="'.$locale->lang('remove').'"';
							$moderators .= ' title="'.$locale->lang('remove').'" />';
							$moderators .= '</a>'."\n";
							if($x < $mod_num - 1)
								$moderators .= ', ';
							$x++;
						}
					}
					else
						$moderators = '-';
				}
				
				$tplforums[] = array(
					'path' => BS_ForumUtils::get_path_images($node,$sub_cats,$images,1),
					'name' => $data->get_name(),
					'fid' => $data->get_id(),
					'type' => $data->get_forum_type(),
					'moderators' => $moderators
				);
			}
			
			$tpl->add_variables(array(
				'action_type' => BS_ACP_ACTION_ADD_MODERATORS,
				'add_button' => sprintf(
					$locale->lang('add_chosen_moderators'),
					'<input type="submit" value="'.$locale->lang('add').'" />'
				)
			));
		}
		
		$tpl->add_variable_ref('forums',$tplforums);
	}
}
?>
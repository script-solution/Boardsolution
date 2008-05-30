<?php
/**
 * Contains the default-submodule for moderators
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_MODERATORS => 'add',
			BS_ACP_ACTION_REMOVE_MODERATORS => 'remove'
		);
	}
	
	public function run()
	{
		$forums = $this->forums->get_all_nodes();
		$num = count($forums);
		$tplforums = array();
		
		$hiddenfields = $this->url->get_acpmod_comps();
		$hiddenfields['action'] = 'edituser';
		
		$this->tpl->add_variables(array(
			'search_url' => $this->url->get_standalone_url('acp','user_search','&comboid=user_','&'),
			'action_param' => BS_URL_ACTION,
			'hiddenfields' => $hiddenfields
		));
		
		if($num == 0)
			$this->msgs->add_notice($this->locale->lang('create_forum_first'));
		else
		{
			$sub_cats = array();

			$images = array(
				'dot' => 'acp/images/forums/path_dot.gif',
				'middle' => 'acp/images/forums/path_middle.gif'
			);
			
			$forum_funcs = BS_ForumUtils::get_instance();
			$mods = $this->cache->get_cache('moderators');
			foreach($forums as $node)
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
							$del_url = $this->url->get_acpmod_url(
								0,'&amp;at='.BS_ACP_ACTION_REMOVE_MODERATORS.'&amp;f='.$data->get_id()
								 .'&amp;uid='.$mdata['user_id']
							);
							$moderators .= ' <a href="'.$del_url.'">';
							$moderators .= '<img src="'.PLIB_Path::inner().'acp/images/delete_small.png"';
							$moderators .= ' alt="'.$this->locale->lang('remove').'"';
							$moderators .= ' title="'.$this->locale->lang('remove').'" />';
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
					'path' => $forum_funcs->get_path_images($node,$sub_cats,$images,1),
					'name' => $data->get_name(),
					'fid' => $data->get_id(),
					'type' => $data->get_forum_type(),
					'moderators' => $moderators
				);
			}
			
			$this->tpl->add_variables(array(
				'action_type' => BS_ACP_ACTION_ADD_MODERATORS,
				'add_button' => sprintf(
					$this->locale->lang('add_chosen_moderators'),
					'<input type="submit" value="'.$this->locale->lang('add').'" />'
				)
			));
		}
		
		$this->tpl->add_array('forums',$tplforums);
	}
	
	public function get_location()
	{
		return array();
	}
}
?>
<?php
/**
 * Contains the favorite forums-userprofile-submodule
 * 
 * @version			$Id: sub_favforums.php 743 2008-05-24 12:27:36Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The favorite forums submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_favforums extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_SAVE_FAVFORUMS => 'favforums'
		);
	}
	
	public function run()
	{
		// collect the forum-ids
		$forum_ids = array();
		foreach(BS_DAO::get_unreadhide()->get_all_of_user($this->user->get_user_id()) as $data)
			$forum_ids[] = $data['forum_id'];
		
		$forum_ids = $this->forums->get_nodes_with_other_ids($forum_ids,false);

		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=favforums'),
			'action_type' => BS_ACTION_SAVE_FAVFORUMS,
		));

		$sub_cats = array();
		$images = array(
			'dot' => $this->user->get_theme_item_path('images/forums/path_dot.gif'),
			'middle' => $this->user->get_theme_item_path('images/forums/path_middle.gif')
		);
		$utils = BS_ForumUtils::get_instance();
		
		$tplforums = array();
		$index = 0;
		foreach($this->forums->get_all_nodes() as $forum)
		{
			$data = $forum->get_data();
			$id = $forum->get_id();
			/* @var $data BS_Forums_NodeData */

			if(!isset($sub_cats[$data->get_parent_id()]))
				$sub_cats[$data->get_parent_id()] = 1;
			else
				$sub_cats[$data->get_parent_id()]++;
			
			$tplforums[] = array(
				'name' => $data->get_name(),
				'id' => $id,
				'index' => $index,
				'contains_forums' => $data->get_forum_type() == 'contains_cats',
				'path_images' => $utils->get_path_images($forum,$sub_cats,$images,1),
				'forum_url' => $this->url->get_topics_url($id),
				'selected' => in_array($id,$forum_ids)
			);
			
			if($data->get_forum_type() == 'contains_threads')
				$index++;
		}
		
		$this->tpl->add_array('forums',$tplforums,false);
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('favorite_forums') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=favforums')
		);
	}
}
?>
<?php
/**
 * Contains the favorite forums-userprofile-submodule
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_SAVE_FAVFORUMS,'favforums');

		$renderer->add_breadcrumb(
			$locale->lang('favorite_forums'),
			$url->get_url(0,'&amp;'.BS_URL_LOC.'=favforums')
		);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$tpl = FWS_Props::get()->tpl();
		$url = FWS_Props::get()->url();

		// collect the forum-ids
		$forum_ids = array();
		foreach(BS_DAO::get_unreadhide()->get_all_of_user($user->get_user_id()) as $data)
			$forum_ids[] = $data['forum_id'];
		
		$forum_ids = $forums->get_nodes_with_other_ids($forum_ids,false);

		$tpl->add_variables(array(
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=favforums'),
			'action_type' => BS_ACTION_SAVE_FAVFORUMS,
		));

		$sub_cats = array();
		$images = array(
			'dot' => $user->get_theme_item_path('images/forums/path_dot.gif'),
			'middle' => $user->get_theme_item_path('images/forums/path_middle.gif')
		);
		$utils = BS_ForumUtils::get_instance();
		
		$tplforums = array();
		$index = 0;
		foreach($forums->get_all_nodes() as $forum)
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
				'forum_url' => $url->get_topics_url($id),
				'selected' => in_array($id,$forum_ids)
			);
			
			if($data->get_forum_type() == 'contains_threads')
				$index++;
		}
		
		$tpl->add_array('forums',$tplforums,false);
	}
}
?>
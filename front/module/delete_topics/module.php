<?php
/**
 * Contains the delete-topics-module
 * 
 * @version			$Id: module_delete_topics.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_delete_topics extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_DELETE_TOPICS,'default');

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$ids = $input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);

		$this->add_loc_forum_path($fid);
		$renderer->add_breadcrumb(
			$locale->lang('delete_topics'),
			$url->get_url('delete_topics','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$ids)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$auth = PLIB_Props::get()->auth();
		$user = PLIB_Props::get()->user();
		$forums = PLIB_Props::get()->forums();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		// check parameters
		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$id_str = $input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
		{
			$this->report_error();
			return;
		}

		if($fid == null)
		{
			$this->report_error();
			return;
		}
		
		$selected_topic_data = array();
		$selected_topic_ids = array();
		$last_data = null;

		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// skip this topic if the user is not allowed to delete it
			if(!$auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS,$data['post_user']))
				continue;
			
			// forum closed?
			if(!$user->is_admin() && $forums->forum_is_closed($data['rubrikid']))
				continue;
			
			$selected_topic_data[] = $data;
			$selected_topic_ids[] = $data['id'];

			$last_data = $data;
		}

		$selected_topics = BS_TopicUtils::get_instance()->get_selected_topics($selected_topic_data);
		if(count($selected_topics) == 0)
		{
			$this->report_error(PLIB_Document_Messages::ERROR,$locale->lang('no_topics_chosen'));
			return;
		}

		if(count($selected_topic_ids) == 1 && $last_data['moved_tid'] == 0)
			BS_PostingUtils::get_instance()->add_topic_review($last_data,false);
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_DELETE_TOPICS,
			'target_url' => $url->get_url('delete_topics','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$id_str),
			'selected_topics' => $selected_topics,
			'back_url' => $url->get_topics_url($fid)
		));
	}
}
?>
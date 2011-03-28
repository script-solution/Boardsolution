<?php
/**
 * Contains the move-topics-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The move-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_move_topics extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_MOVE_TOPICS,'default');
		
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$ids = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		// don't show forum-title if its intern
		if($fid !== null && $auth->has_access_to_intern_forum($fid))
			$this->add_loc_forum_path($fid);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$ids);
		$renderer->add_breadcrumb($locale->lang('move_topics'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
		{
			$this->report_error();
			return;
		}
	
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
	
		// check get-parameter
		if($fid == null)
		{
			$this->report_error();
			return;
		}
	
		// has the user permission to move topics?
		if(!$auth->has_current_forum_perm(BS_MODE_MOVE_TOPICS))
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
	
		if($input->isset_var('preview','post'))
			BS_PostingUtils::add_post_preview();
	
		$selected_topic_data = array();
		$selected_topic_ids = array();
		$last_data = null;
	
		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// is it a shadow topic?
			if($data['moved_tid'] != 0)
				continue;
			
			$selected_topic_data[] = $data;
			$selected_topic_ids[] = $data['id'];
	
			$last_data = $data;
		}
	
		$selected_topics = BS_TopicUtils::get_selected_topics($selected_topic_data);
		if(count($selected_topics) == 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('no_topics_chosen'));
			return;
		}
	
		$form = $this->request_formular(false,true);
		
		$pform = new BS_PostingForm($locale->lang('reasonformove').':');
		$pform->set_show_options(true);
		$pform->add_form();
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$id_str);
		
		if(count($selected_topic_ids) == 1)
		{
			$purl = BS_URL::get_mod_url();
			$purl->copy_params($url,array(BS_URL_FID,BS_URL_ID));
			BS_PostingUtils::add_topic_review($last_data,true,$purl);
		}
		
		$target_forum = $form->get_input_value('target_forum',0);
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_MOVE_TOPICS,
			'target_url' => $url->to_url(),
			'selected_topics' => $selected_topics,
			'forum_combo' => BS_ForumUtils::get_recursive_forum_combo(
				'target_forum',$target_forum,$fid
			),
			'back_url' => BS_URL::build_topics_url($fid)
		));
	}
}
?>
<?php
/**
 * Contains the open/close-topics-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The open/close-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_openclose_topics extends BS_Front_Module
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
		$renderer = $doc->use_default_renderer();
		$auth = FWS_Props::get()->auth();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_OPEN_TOPICS,array('default','open'));
		$renderer->add_action(BS_ACTION_CLOSE_TOPICS,array('default','close'));

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$ids = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		$mode = $input->correct_var(
			BS_URL_MODE,'get',FWS_Input::STRING,array('open','close'),'open'
		);
		// don't show thread- and forum-title if its intern
		if($fid !== null && $auth->has_access_to_intern_forum($fid))
			$this->add_loc_forum_path($fid);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$ids);
		$url->set(BS_URL_MODE,$mode);
		$renderer->add_breadcrumb($locale->lang($mode.'_topics'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
		{
			$this->report_error();
			return;
		}
	
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$mode = $input->get_var(BS_URL_MODE,'get',FWS_Input::STRING);
	
		// check if the parameters are valid
		if($fid == null)
		{
			$this->report_error();
			return;
		}
	
		if($input->isset_var('preview','post'))
			BS_PostingUtils::add_post_preview();
		
		$selected_topic_data = array();
		$selected_topic_ids = array();
		$notices = array();
		$last_data = null;
		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// check if the user has permission to open/close this topic
			if(!$auth->has_current_forum_perm(BS_MODE_OPENCLOSE_TOPICS,$data['post_user']))
				continue;
	
			// check if this is a shadow topic
			if($data['moved_tid'] != 0)
				continue;
	
			// check if the topic is already opened / closed
			if(($data['thread_closed'] == 1 && $mode == 'close') ||
				($data['thread_closed'] == 0 && $mode == 'open'))
				continue;
			
			// forum closed?
			if(!$user->is_admin() && $forums->forum_is_closed($data['rubrikid']))
				continue;
	
			// check wether a user has locked this topic
			if(BS_TopicUtils::is_locked($data['locked'],BS_LOCK_TOPIC_OPENCLOSE))
				continue;
	
			$selected_topic_data[] = $data;
			$selected_topic_ids[] = $data['id'];
			
			if($data['type'] == -1)
			{
				if($mode == 'open')
					$notices['event'] = $locale->lang('open_event_explain');
				else
					$notices['event'] = $locale->lang('close_event_explain');
			}
			else if($data['type'] > 0)
			{
				if($mode == 'open')
					$notices['poll'] = $locale->lang('open_poll_explain');
				else
					$notices['poll'] = $locale->lang('close_poll_explain');
			}
	
			$last_data = $data;
		}
	
		$selected_topics = BS_TopicUtils::get_selected_topics($selected_topic_data);
		if(count($selected_topics) == 0)
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,$locale->lang('no_topics_chosen_openclose')
			);
			return;
		}
	
		$this->request_formular(false,true);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,implode(',',$selected_topic_ids));
		$url->set(BS_URL_MODE,$mode);
	
		if($mode == 'open')
			$text_explain = $locale->lang('reason_for_open');
		else
			$text_explain = $locale->lang('reason_for_close');
		
		$title = $locale->lang('text').':<br /><span class="bs_desc">'.$text_explain.'</span>';
		$pform = new BS_PostingForm($title);
		$pform->set_show_options(true);
		$pform->add_form();
		
		if(count($selected_topic_ids) == 1)
		{
			$murl = clone $url;
			BS_PostingUtils::add_topic_review($last_data,true,$murl);
		}
		
		$tpl->add_variables(array(
			'title' => $locale->lang($mode == 'open' ? 'open_topics' : 'close_topics'),
			'target_url' => $url->to_url(),
			'action_type' => $mode == 'open' ? BS_ACTION_OPEN_TOPICS : BS_ACTION_CLOSE_TOPICS,
			'selected_topics' => $selected_topics,
			'back_url' => BS_URL::build_topics_url($fid),
			'notices' => array_values($notices)
		));
	}
}
?>
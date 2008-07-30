<?php
/**
 * Contains the open/close-topics-module
 * 
 * @version			$Id: module_openclose_topics.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The open/close-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_openclose_topics extends BS_Front_Module
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
		
		$renderer->add_action(BS_ACTION_OPEN_TOPICS,array('default','open'));
		$renderer->add_action(BS_ACTION_CLOSE_TOPICS,array('default','close'));

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$ids = $input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		$mode = $input->correct_var(
			BS_URL_MODE,'get',PLIB_Input::STRING,array('open','close'),'open'
		);
		
		$this->add_loc_forum_path($fid);
		$renderer->add_breadcrumb(
			$locale->lang($mode.'_topics'),
			$url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$ids.'&amp;mode='.$mode)
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

		$id_str = $input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
		{
			$this->report_error();
			return;
		}
	
		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$mode = $input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING);
	
		// check if the parameters are valid
		if($fid == null)
		{
			$this->report_error();
			return;
		}
	
		if($input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
		
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
			if(BS_TopicUtils::get_instance()->is_locked($data['locked'],BS_LOCK_TOPIC_OPENCLOSE))
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
	
		$selected_topics = BS_TopicUtils::get_instance()->get_selected_topics($selected_topic_data);
		if(count($selected_topics) == 0)
		{
			$this->report_error(
				PLIB_Document_Messages::ERROR,$locale->lang('no_topics_chosen_openclose')
			);
			return;
		}
	
		$this->request_formular(false,true);
		
		$mode_add = '&amp;'.BS_URL_MODE.'='.$mode;
		$target_url = $url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.implode(',',$selected_topic_ids).$mode_add
		);
	
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
			$murl = $url->get_url(
				0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_MODE.'='.$mode.'&amp;'.BS_URL_ID.'='.$id_str
					.'&amp;'.BS_URL_PID.'='
			);
			BS_PostingUtils::get_instance()->add_topic_review($last_data,true,$murl);
		}
		
		$tpl->add_variables(array(
			'title' => $locale->lang($mode == 'open' ? 'open_topics' : 'close_topics'),
			'target_url' => $target_url,
			'action_type' => $mode == 'open' ? BS_ACTION_OPEN_TOPICS : BS_ACTION_CLOSE_TOPICS,
			'selected_topics' => $selected_topics,
			'back_url' => $url->get_topics_url($fid),
			'notices' => array_values($notices)
		));
	}
}
?>
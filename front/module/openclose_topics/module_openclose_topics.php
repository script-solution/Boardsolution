<?php
/**
 * Contains the open/close-topics-module
 * 
 * @version			$Id$
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
	public function get_actions()
	{
		return array(
			BS_ACTION_OPEN_TOPICS => array('default','open'),
			BS_ACTION_CLOSE_TOPICS => array('default','close'),
		);
	}
	
	public function run()
	{
		$id_str = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
		{
			$this->_report_error();
			return;
		}
	
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$mode = $this->input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING);
	
		// check if the parameters are valid
		if($fid == null)
		{
			$this->_report_error();
			return;
		}
	
		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
		
		$selected_topic_data = array();
		$selected_topic_ids = array();
		$notices = array();
		$last_data = null;
		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// check if the user has permission to open/close this topic
			if(!$this->auth->has_current_forum_perm(BS_MODE_OPENCLOSE_TOPICS,$data['post_user']))
				continue;
	
			// check if this is a shadow topic
			if($data['moved_tid'] != 0)
				continue;
	
			// check if the topic is already opened / closed
			if(($data['thread_closed'] == 1 && $mode == 'close') ||
				($data['thread_closed'] == 0 && $mode == 'open'))
				continue;
			
			// forum closed?
			if(!$this->user->is_admin() && $this->forums->forum_is_closed($data['rubrikid']))
				continue;
	
			// check wether a user has locked this topic
			if(BS_TopicUtils::get_instance()->is_locked($data['locked'],BS_LOCK_TOPIC_OPENCLOSE))
				continue;
	
			$selected_topic_data[] = $data;
			$selected_topic_ids[] = $data['id'];
			
			if($data['type'] == -1)
			{
				if($mode == 'open')
					$notices['event'] = $this->locale->lang('open_event_explain');
				else
					$notices['event'] = $this->locale->lang('close_event_explain');
			}
			else if($data['type'] > 0)
			{
				if($mode == 'open')
					$notices['poll'] = $this->locale->lang('open_poll_explain');
				else
					$notices['poll'] = $this->locale->lang('close_poll_explain');
			}
	
			$last_data = $data;
		}
	
		$selected_topics = BS_TopicUtils::get_instance()->get_selected_topics($selected_topic_data);
		if(count($selected_topics) == 0)
		{
			$this->_report_error(
				PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('no_topics_chosen_openclose')
			);
			return;
		}
	
		$this->_request_formular(false,true);
		
		$mode_add = '&amp;'.BS_URL_MODE.'='.$mode;
		$target_url = $this->url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.implode(',',$selected_topic_ids).$mode_add
		);
	
		if($mode == 'open')
			$text_explain = $this->locale->lang('reason_for_open');
		else
			$text_explain = $this->locale->lang('reason_for_close');
		
		$title = $this->locale->lang('text').':<br /><span class="bs_desc">'.$text_explain.'</span>';
		$pform = new BS_PostingForm($title);
		$pform->set_show_options(true);
		$pform->add_form();
		
		if(count($selected_topic_ids) == 1)
		{
			$url = $this->url->get_url(
				0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_MODE.'='.$mode.'&amp;'.BS_URL_ID.'='.$id_str
					.'&amp;'.BS_URL_PID.'='
			);
			BS_PostingUtils::get_instance()->add_topic_review($last_data,true,$url);
		}
		
		$this->tpl->add_variables(array(
			'title' => $this->locale->lang($mode == 'open' ? 'open_topics' : 'close_topics'),
			'target_url' => $target_url,
			'action_type' => $mode == 'open' ? BS_ACTION_OPEN_TOPICS : BS_ACTION_CLOSE_TOPICS,
			'selected_topics' => $selected_topics,
			'back_url' => $this->url->get_topics_url($fid),
			'notices' => array_values($notices)
		));
	}
	
	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$ids = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		$mode = $this->input->correct_var(
			BS_URL_MODE,'get',PLIB_Input::STRING,array('open','close'),'open'
		);
		
		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$url = $this->url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$ids.'&amp;mode='.$mode
		);
		$result[$this->locale->lang($mode.'_topics')] = $url;
		
		return $result;
	}
	
	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>
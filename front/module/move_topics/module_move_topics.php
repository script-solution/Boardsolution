<?php
/**
 * Contains the move-topics-module
 * 
 * @version			$Id: module_move_topics.php 728 2008-05-22 22:09:30Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The move-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_move_topics extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_MOVE_TOPICS => 'default'
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
	
		// check get-parameter
		if($fid == null)
		{
			$this->_report_error();
			return;
		}
	
		// has the user permission to move topics?
		if(!$this->auth->has_current_forum_perm(BS_MODE_MOVE_TOPICS))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
	
		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
	
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
	
		$selected_topics = BS_TopicUtils::get_instance()->get_selected_topics($selected_topic_data);
		if(count($selected_topics) == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('no_topics_chosen'));
			return;
		}
	
		$form = $this->_request_formular(false,true);
		
		$pform = new BS_PostingForm($this->locale->lang('reasonformove').':');
		$pform->set_show_options(true);
		$pform->add_form();
	
		if(count($selected_topic_ids) == 1)
		{
			$url = $this->url->get_url(
				0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$id_str.'&amp;'.BS_URL_PID.'='
			);
			BS_PostingUtils::get_instance()->add_topic_review($last_data,true,$url);
		}
		
		$target_forum = $form->get_input_value('target_forum',0);
		$this->tpl->add_variables(array(
			'action_type' => BS_ACTION_MOVE_TOPICS,
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$id_str),
			'selected_topics' => $selected_topics,
			'forum_combo' => BS_ForumUtils::get_instance()->get_recursive_forum_combo(
				'target_forum',$target_forum,$fid
			),
			'back_url' => $this->url->get_topics_url($fid)
		));
	}
	
	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$ids = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		
		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$url = $this->url->get_url('move_topics','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$ids);
		$result[$this->locale->lang('move_topics')] = $url;
		
		return $result;
	}
	
	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>
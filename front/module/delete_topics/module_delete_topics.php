<?php
/**
 * Contains the delete-topics-module
 * 
 * @version			$Id$
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
	public function get_actions()
	{
		return array(
			BS_ACTION_DELETE_TOPICS => 'default'
		);
	}
	
	public function run()
	{
		// check parameters
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$id_str = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
		{
			$this->_report_error();
			return;
		}

		if($fid == null)
		{
			$this->_report_error();
			return;
		}
		
		$selected_topic_data = array();
		$selected_topic_ids = array();
		$last_data = null;

		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// skip this topic if the user is not allowed to delete it
			if(!$this->auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS,$data['post_user']))
				continue;
			
			// forum closed?
			if(!$this->user->is_admin() && $this->forums->forum_is_closed($data['rubrikid']))
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

		if(count($selected_topic_ids) == 1 && $last_data['moved_tid'] == 0)
			BS_PostingUtils::get_instance()->add_topic_review($last_data,false);
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACTION_DELETE_TOPICS,
			'target_url' => $this->url->get_url('delete_topics','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$id_str),
			'selected_topics' => $selected_topics,
			'back_url' => $this->url->get_topics_url($fid)
		));
	}

	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$ids = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);

		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$url = $this->url->get_url('delete_topics','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$ids);
		$result[$this->locale->lang('delete_topics')] = $url;

		return $result;
	}

	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>
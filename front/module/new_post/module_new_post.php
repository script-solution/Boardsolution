<?php
/**
 * Contains the new-post-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-post-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_post extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_REPLY => 'default'
		);
	}
	
	public function run()
	{
		// check parameter
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		if($fid == null || $tid == null)
		{
			$this->_report_error();
			return;
		}
		
		// does the forum exist?
		$forum_data = $this->forums->get_node_data($fid);
		if($forum_data === null || $forum_data->get_forum_type() != 'contains_threads')
		{
			$this->_report_error();
			return;
		}
		
		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('forum_is_closed'));
			return;
		}
		
		// check if the topic is valid
		$topicdata = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($topicdata === null || $topicdata['comallow'] == 0 || 
			 (!$this->user->is_admin() && $topicdata['thread_closed'] == 1) ||
			 $topicdata['rubrikid'] != $fid)
		{
			$this->_report_error();
			return;
		}

		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		if($site == null)
			$site = $this->input->set_var(BS_URL_SITE,'get',1);

		if($this->input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
		
		$form = new BS_PostingForm($this->locale->lang('post').':');
		$form->set_show_attachments(true);
		$form->set_show_options(true);
		$form->add_form();
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
				.'&amp;'.BS_URL_SITE.'='.$site),
			'action_type' => BS_ACTION_REPLY,
			'back_url' => $this->url->get_url('posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
				.'&amp;'.BS_URL_SITE.'='.$site)
		));

		$url = $this->url->get_url(
			0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_SITE.'='.$site
				.'&amp;'.BS_URL_PID.'='
		);
		BS_PostingUtils::get_instance()->add_topic_review($topicdata,true,$url);
	}

	public function get_location()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$site = ($s = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER)) != null ? '&amp;'.BS_URL_SITE.'='.$s : '';

		$result = array();
		$this->_add_loc_forum_path($result,$fid);
		$this->_add_loc_topic($result);
		$url = $this->url->get_url(
			'new_post','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.$site
		);
		$result[$this->locale->lang('newentry')] = $url;

		return $result;
	}

	public function has_access()
	{
		return $this->auth->has_current_forum_perm(BS_MODE_REPLY);
	}
}
?>
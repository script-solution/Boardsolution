<?php
/**
 * Contains the chg_read_status-action
 *
 * @version			$Id: chg_read_status.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The chg_read_status-action
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_chg_read_status extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// the user has to be logged in
		if(!$this->user->is_loggedin())
			return 'You are a guest';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		$read = $this->input->correct_var(
			BS_URL_LOC,'get',PLIB_Input::STRING,array('read','unread'),'read'
		);
		$mode = $this->input->correct_var(
			BS_URL_MODE,'get',PLIB_Input::STRING,array('topics','forum','all'),'topics'
		);
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::ID);

		switch($mode)
		{
			case 'topics':
				$id_str = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
				if(!($ids = PLIB_StringHelper::get_ids($id_str)))
					return 'Invalid id-string got via GET';

				if($read == 'read')
					$this->unread->mark_topics_read($ids);
				else
					$this->unread->mark_topics_unread($ids);

				$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
				if($fid != null)
					$this->add_link($this->locale->lang('back'),$this->url->get_topics_url($fid,'&amp;',$site));
				else
					$this->add_link($this->locale->lang('back'),$this->url->get_url('unread'));
				break;

			case 'forum':
				$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
				if($fid == null)
					return 'Invalid forum-id "'.$fid.'"';

				$this->unread->mark_forum_read($fid);

				if($this->input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING) == 'topics')
					$this->add_link($this->locale->lang('back'),$this->url->get_topics_url($fid,'&amp;',$site));
				else
					$this->add_link($this->locale->lang('back'),$this->url->get_forums_url());
				break;

			case 'all':
				$this->unread->mark_all_read();

				$this->add_link($this->locale->lang('forumindex'),$this->url->get_forums_url());
				break;
		}

		$this->set_success_msg($this->locale->lang('success_'.BS_ACTION_CHANGE_READ_STATUS.'_'.$read));
		$this->set_action_performed(true);

		return '';
	}
}
?>
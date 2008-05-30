<?php
/**
 * Contains the edit-event-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_lock_topics_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$id_str = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Invalid id-string got via GET';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		if($fid == null)
			return 'The forum-id "'.$fid.'" is invalid';

		$topic_ids = array();

		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// skip this topic if the user is not allowed to delete it
			if(!$this->auth->has_current_forum_perm(BS_MODE_LOCK_TOPICS))
				continue;

			// check if this is a shadow topic
			if($data['moved_tid'] != 0)
				continue;

			$topic_ids[] = $data['id'];
		}

		// no valid topics?
		if(count($topic_ids) == 0)
			return 'no_topics_chosen';

		// grab vars
		$edit_topic = $this->input->get_var('edit_topic','post',PLIB_Input::INT_BOOL);
		$openclose_topic = $this->input->get_var('openclose_topic','post',PLIB_Input::INT_BOOL);
		$posts_topic = $this->input->get_var('posts_topic','post',PLIB_Input::INT_BOOL);

		// build locked-value
		$locked = 0;
		if($edit_topic == 1)
			$locked |= BS_LOCK_TOPIC_EDIT;
		if($openclose_topic == 1)
			$locked |= BS_LOCK_TOPIC_OPENCLOSE;
		if($posts_topic == 1)
			$locked |= BS_LOCK_TOPIC_POSTS;

		// set new locked-status
		BS_DAO::get_topics()->update_by_ids($topic_ids,array('locked' => $locked));

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back'),$this->url->get_topics_url($fid));

		return '';
	}
}
?>
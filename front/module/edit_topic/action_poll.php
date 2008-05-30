<?php
/**
 * Contains the edit-poll-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-poll-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_edit_topic_poll extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);

		// are the parameters valid?
		if($id == null || $fid == null)
			return 'The GET-parameter "id" or "fid" is missing';

		// the user has to be logged in
		if(!$this->user->is_loggedin())
			return 'Not loggedin';

		// does the topic exist?
		$topic_data = BS_DAO::get_topics()->get_by_id($id);
		if($topic_data === false)
			return 'A topic with id "'.$id.'" has not been found';

		// has the user the permission to edit this poll?
		if(!$this->auth->has_current_forum_perm(BS_MODE_EDIT_TOPIC,$topic_data['post_user']))
			return 'No permission to edit this topic';

		// is it a poll?
		if($topic_data['type'] <= 0)
			return 'The topic is no poll';
		
		// does the forum exist?
		$forum_data = $this->forums->get_node_data($fid);
		if($forum_data === null)
			return 'The forum with id "'.$fid.'" doesn\'t exist';

		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
			return 'You are no admin and the forum is closed';

		// shadow-threads cannot be edited
		if($topic_data['moved_tid'] > 0)
			return 'shadow_thread_deny';

		// check if the topic is locked
		if(BS_TopicUtils::get_instance()->is_locked($topic_data['locked'],BS_LOCK_TOPIC_EDIT))
			return 'no_permission_to_edit_thread';
		
		// store the old options to compare with the new ones
		$total_votes = 0;
		$old_is_mc = false;
		$old_options = array();
		foreach(BS_DAO::get_polls()->get_options_by_id($topic_data['type']) as $data)
		{
			$old_options[] = $data['option_name'];
			$total_votes += $data['option_value'];
			$old_is_mc = $data['multichoice'] == 1;
		}

		$can_edit_options = $this->auth->has_global_permission('always_edit_poll_options') ||
			$total_votes == 0;

		$important = $this->input->get_var('important','post',PLIB_Input::INT_BOOL);
		$allow_posts = $this->input->get_var('allow_posts','post',PLIB_Input::INT_BOOL);

		$fields = array(
			'comallow' => $allow_posts
		);
		
		if($can_edit_options)
			$fields['name'] = $this->input->get_var('topic_name','post',PLIB_Input::STRING);

		// check if the user is allowed to mark a topic important
		if($this->auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT))
			$fields['important'] = $important;

		// update topic
		BS_DAO::get_topics()->update($id,$fields);

		if($can_edit_options)
		{
			$multichoice = $this->input->get_var('multichoice','post',PLIB_Input::INT_BOOL);
			$options = $this->input->get_var('poll_options','post',PLIB_Input::STRING);
			$lines = explode("\n",trim($options));
			
			// determine the "real" options
			$new_options = array();
			for($i = 0,$len = count($lines);$i < $len;$i++)
			{
				if($lines[$i] != '')
					$new_options[] = $lines[$i];
			}
			
			// check wether we have to update all options
			$complete_change = count($new_options) != count($old_options);
			if(!$complete_change)
			{
				for($i = 0,$len = count($new_options);$i < $len;$i++)
				{
					if($new_options[$i] != $old_options[$i])
					{
						$complete_change = true;
						break;
					}
				}
			}

			// update just multichoice?
			if(!$complete_change && $multichoice != $old_is_mc)
				BS_DAO::get_polls()->set_multichoice($topic_data['type'],$multichoice);
			// update all options?
			else if($complete_change)
			{
				// too few options?
				if(count($new_options) < 2)
					return 'pollmoeglichkeitenleer';
	
				// too many options?
				if(count($new_options) > $this->cfg['max_poll_options'])
					return 'max_poll_options';
	
				// delete all options
				BS_DAO::get_polls()->delete_by_ids(array($topic_data['type']));
				BS_DAO::get_pollvotes()->delete_by_polls(array($topic_data['type']));
	
				// insert them again
				// because the user might have changed the order, added options or deleted options
				// this would be too complicated to update (and probably slower...)
				foreach($new_options as $line)
					BS_DAO::get_polls()->create($topic_data['type'],$line,$multichoice);
			}
		}

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back_to_forum'),$this->url->get_topics_url($fid));

		return '';
	}
}
?>
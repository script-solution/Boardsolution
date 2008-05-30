<?php
/**
 * Contains the vote-poll-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The vote-poll-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_posts_votepoll extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// parameter valid?
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		if($fid == null || $tid == null)
			return 'The forum- or topic-id is invalid';

		$topic_data = BS_DAO::get_polls()->get_data_by_topic_id($tid);
		
		// does the topic exist?
		if($topic_data['type'] == '')
			return 'A topic with id "'.$tid.'" doesn\'t exist';

		// is has to be a not closed poll
		if($topic_data['thread_closed'] == 1 || $topic_data['type'] <= 0)
			return 'The topic is closed or no poll';

		// guests are not allowed to vote
		if(!$this->user->is_loggedin())
			return 'You are a guest';

		if(BS_UserUtils::get_instance()->user_voted_for_poll($topic_data['type']))
			return 'poll_user_voted';

		if($topic_data['multichoice'] == 0)
		{
			$choice = $this->input->get_var('vote_option','post',PLIB_Input::ID);
			if($choice == null)
				return 'no_radiobutton_clicked';

			BS_DAO::get_polls()->vote($choice);
		}
		else
		{
			$choice = $this->input->get_var('vote_option','post');
			if($choice == null || count($choice) == 0 || !PLIB_Array_Utils::is_integer($choice))
				return 'no_checkbox_clicked';

			foreach($choice as $value)
				BS_DAO::get_polls()->vote($value);
		}

		BS_DAO::get_pollvotes()->create($topic_data['type'],$this->user->get_user_id());

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('go_to_topic'),$this->url->get_posts_url($fid,$tid));
	
		return '';
	}
}
?>
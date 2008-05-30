<?php
/**
 * Contains the vote-link-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The vote-link-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_linklist_votelink extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$vote = $this->input->get_var('link_rating','post',PLIB_Input::INTEGER);
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameters
		if($id == null || $vote == null || !$this->user->is_loggedin())
			return 'The id or your rating is invalid or you\'re a guest';

		// has the user already voted?
		if(BS_UserUtils::get_instance()->user_voted_for_link($id))
			return 'already_voted';

		// check if the vote is valid
		if($vote < 1 || $vote > 6)
			return 'invalid_vote_option';

		BS_DAO::get_links()->vote($id,$vote);
		BS_DAO::get_linkvotes()->vote($id,$this->user->get_user_id());

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back'),$this->url->get_url('linklist'));

		return '';
	}
}
?>
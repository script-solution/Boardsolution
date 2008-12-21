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
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$vote = $input->get_var('link_rating_'.$id,'post',FWS_Input::INTEGER);

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameters
		if($id == null || $vote == null || !$user->is_loggedin())
			return 'The id or your rating is invalid or you\'re a guest';

		// has the user already voted?
		if(BS_UserUtils::user_voted_for_link($id))
			return 'already_voted';

		// check if the vote is valid
		if($vote < 1 || $vote > 6)
			return 'invalid_vote_option';

		BS_DAO::get_links()->vote($id,$vote);
		BS_DAO::get_linkvotes()->vote($id,$user->get_user_id());

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_mod_url('linklist'));

		return '';
	}
}
?>
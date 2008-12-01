<?php
/**
 * Contains the update-sig-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-sig-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_updatesig extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$user->is_loggedin())
			return 'You are a guest';

		if($cfg['enable_signatures'] == 0)
			return 'Signatures are disabled';

		$post_text = $input->get_var('text','post',FWS_Input::STRING);

		$text = '';
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db($text,$post_text,'sig');
		if($error != '')
			return $error;

		BS_DAO::get_profile()->update_user_by_id(array(
			'signatur' => $text,
			'signature_posted' => $post_text
		),$user->get_user_id());

		$user->set_profile_val('signatur',$text);
		$user->set_profile_val('signature_posted',$post_text);

		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','signature')
		);

		return '';
	}
}
?>
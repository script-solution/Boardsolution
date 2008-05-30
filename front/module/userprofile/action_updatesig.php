<?php
/**
 * Contains the update-sig-action
 *
 * @version			$Id: action_updatesig.php 713 2008-05-20 21:59:54Z nasmussen $
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
		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$this->user->is_loggedin())
			return 'You are a guest';

		if($this->cfg['enable_signatures'] == 0)
			return 'Signatures are disabled';

		$post_text = $this->input->get_var('text','post',PLIB_Input::STRING);

		$text = '';
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db($text,$post_text,'sig');
		if($error != '')
			return $error;

		BS_DAO::get_profile()->update_user_by_id(array(
			'signatur' => $text,
			'signature_posted' => $post_text
		),$this->user->get_user_id());

		$this->user->set_profile_val('signatur',$text);
		$this->user->set_profile_val('signature_posted',$post_text);

		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),$this->url->get_url(0,'&amp;'.BS_URL_LOC.'=signature')
		);

		return '';
	}
}
?>
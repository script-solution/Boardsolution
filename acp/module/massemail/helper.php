<?php
/**
 * Contains the helper-class for the massemail-module
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the massemail-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_MassEmail_Helper extends FWS_UtilBase
{
	/**
	 * Determines the receiver from POST
	 *
	 * @return array an array of the form: <code>array('user' => ...,'groups' => ...)</code>
	 */
	public static function get_receiver()
	{
		$input = FWS_Props::get()->input();

		$recipient_groups = $input->get_var('recipient_groups','post');
		$recipient_user = array();
		
		if($recipient_groups === null || !FWS_Array_Utils::is_integer($recipient_groups))
			$recipient_groups = array();
		
		$srecipient_user = $input->get_var('recipient_user','post',FWS_Input::STRING);
		if($srecipient_user !== null)
		{
			$recipient_user = FWS_Array_Utils::advanced_explode(',',$srecipient_user);
			if(!FWS_Array_Utils::is_integer($recipient_user))
				$recipient_user = array();
		}
		
		return array(
			'user' => $recipient_user,
			'groups' => $recipient_groups
		);
	}

	/**
	 * builds the email-text
	 *
	 * @return string the text
	 */
	public static function get_mail_text()
	{
		$input = FWS_Props::get()->input();

		$text = $input->get_var('text','post',FWS_Input::STRING);
		if($input->get_var('content_type','post',FWS_Input::STRING) == 'html')
		{
			$bbcode = new BS_BBCode_Parser($text,'posts',true,true);
			$bbcode->set_board_path(FWS_Path::outer());
			try
			{
				$bbcode->get_message_for_db();
			}
			catch(BS_BBCode_Exception $ex)
			{
				// ignore
			}
			$bbcode->stripslashes();
			return FWS_StringHelper::htmlspecialchars_back($bbcode->get_message_for_output());
		}
		
		return stripslashes(FWS_StringHelper::htmlspecialchars_back($text));
	}
}
?>
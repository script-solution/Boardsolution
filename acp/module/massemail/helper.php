<?php
/**
 * Contains the helper-class for the massemail-module
 *
 * @version			$Id: helper.php 737 2008-05-23 18:26:46Z nasmussen $
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
final class BS_ACP_Module_MassEmail_Helper extends PLIB_Singleton
{
	/**
	 * @return BS_ACP_Module_MassEmail_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Determines the receiver from POST
	 *
	 * @return array an array of the form: <code>array('user' => ...,'groups' => ...)</code>
	 */
	public function get_receiver()
	{
		$recipient_groups = $this->input->get_var('recipient_groups','post');
		$recipient_user = array();
		
		if($recipient_groups === null || !PLIB_Array_Utils::is_integer($recipient_groups))
			$recipient_groups = array();
		
		$srecipient_user = $this->input->get_var('recipient_user','post',PLIB_Input::STRING);
		if($srecipient_user !== null)
		{
			$recipient_user = PLIB_Array_Utils::advanced_explode(',',$srecipient_user);
			if(!PLIB_Array_Utils::is_integer($recipient_user))
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
	public function get_mail_text()
	{
		$text = $this->input->get_var('text','post',PLIB_Input::STRING);
		if($this->input->get_var('content_type','post',PLIB_Input::STRING) == 'html')
		{
			$bbcode = new BS_BBCode_Parser($text,'posts',true,true);
			$bbcode->set_board_path(PLIB_Path::outer());
			$bbcode->get_message_for_db();
			$bbcode->stripslashes();
			return PLIB_StringHelper::htmlspecialchars_back($bbcode->get_message_for_output());
		}
		
		return stripslashes(PLIB_StringHelper::htmlspecialchars_back($text));
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
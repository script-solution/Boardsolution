<?php
/**
 * Contains the send-errorlogs-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The send-errorlogs-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_errorlog_send extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$http = new PLIB_HTTP('www.script-solution.de',80);
		$response = $http->post('/bserrors/receive.php',array('errors' => $this->_get_error_xml()));
		if($response === false)
			return 'send_errors_failed';
		
		$this->set_success_msg($this->locale->lang('send_errors_success'));
		$this->set_action_performed(true);

		return '';
	}
	
	/**
	 * Builds the xml-document to send
	 *
	 * @return string the xml-doc
	 */
	private function _get_error_xml()
	{
		$email = $this->input->get_var('email','post',PLIB_Input::STRING);
		$text = $this->input->get_var('text','post',PLIB_Input::STRING);
		
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
		$xml .= '<errors>'."\n";
		$xml .= '	<id>'.md5(PLIB_Path::outer()).'</id>'."\n";
		$xml .= '	<version>'.BS_VERSION.'</version>'."\n";
		$xml .= '	<phpversion>'.phpversion().'</phpversion>'."\n";
		$xml .= '	<mysqlversion>'.$this->db->get_server_version().'</mysqlversion>'."\n";
		$xml .= '	<email>'.htmlspecialchars($email,ENT_QUOTES).'</email>'."\n";
		$xml .= '	<text>'.htmlspecialchars($text,ENT_QUOTES).'</text>'."\n";

		foreach(BS_DAO::get_logerrors()->get_list('id','DESC',0,1000) as $data)
		{
			$xml .= '	<error>'."\n";
			$xml .= '		<id>'.$data['id'].'</id>'."\n";
			$xml .= '		<query>'.htmlspecialchars($data['query'],ENT_QUOTES).'</query>'."\n";
			$xml .= '		<msg>'.htmlspecialchars($data['message'],ENT_QUOTES).'</msg>'."\n";
			$xml .= '		<trace>'.htmlspecialchars($data['backtrace'],ENT_QUOTES).'</trace>'."\n";
			$xml .= '	</error>'."\n";
		}
		
		$xml .= '</errors>';
		return urlencode($xml);
	}
}
?>
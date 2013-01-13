<?php
/**
 * Contains the send-errorlogs-action
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
		$locale = FWS_Props::get()->locale();

		$http = new FWS_HTTP('www.script-solution.de',80);
		$response = $http->post('/bserrors/receive.php',array('errors' => $this->_get_error_xml()));
		if($response === false)
			return 'send_errors_failed';
		
		$this->set_success_msg($locale->lang('send_errors_success'));
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
		$input = FWS_Props::get()->input();
		$db = FWS_Props::get()->db();

		$email = $input->get_var('email','post',FWS_Input::STRING);
		$text = $input->get_var('text','post',FWS_Input::STRING);
		
		$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
		$xml .= '<errors>'."\n";
		$xml .= '	<id>'.md5(FWS_Path::outer()).'</id>'."\n";
		$xml .= '	<version>'.BS_VERSION.'</version>'."\n";
		$xml .= '	<phpversion>'.phpversion().'</phpversion>'."\n";
		$xml .= '	<mysqlversion>'.$db->get_server_version().'</mysqlversion>'."\n";
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
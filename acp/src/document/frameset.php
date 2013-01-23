<?php
/**
 * Contains the acp-frameset-document-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.src.document
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
 * The acp-frameset-document. We have no modules here and no renderer.
 *
 * @package			Boardsolution
 * @subpackage	acp.src.document
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Document_Frameset extends BS_ACP_Document
{
	/**
	 * The login-error-message
	 *
	 * @var string
	 */
	private $_error_msg;
	
	/**
	 * @see BS_ACP_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		parent::prepare_rendering();
		
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		
		// login the user?
		if($input->isset_var('login','post'))
		{
			$username = $input->get_var('user_login','post',FWS_Input::STRING);
			$pw = $input->get_var('pw_login','post',FWS_Input::STRING);
			$error_code = $user->login($username,$pw,true);
			if($error_code == BS_User_Current::LOGIN_ERROR_MAX_LOGIN_TRIES)
				$this->_error_msg = $locale->lang('max_login_tries_notice');
			else if($error_code != BS_User_Current::LOGIN_ERROR_NO_ERROR)
				$this->_error_msg = $locale->lang('error_login');
		}
		
		// logout?
		if($user->is_loggedin() && $input->get_var('logout','get') == 1)
			$user->logout();
	}
	
	/**
	 * @see FWS_Document::render()
	 *
	 * @return string
	 */
	public function render()
	{
		$this->prepare_rendering();
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		
		if($user->is_loggedin())
		{
			$navi_file = FWS_Path::client_app().'admin.php?page=navi&amp;';
			$navi_file .= BS_URL_SID.'='.$user->get_session_id();
			
			$content_file = FWS_Path::client_app().'admin.php?page=content&amp;';
			$content_file .= BS_URL_SID.'='.$user->get_session_id();
			
			$tpl->set_template('frameset.htm');
			$tpl->add_variables(array(
				'navi_file' => $navi_file,
				'content_file' => $content_file,
				'charset' => 'charset='.BS_HTML_CHARSET,
				'page_title' => sprintf($locale->lang('page_title'),BS_VERSION)
			));
			$res = $tpl->parse_template();
		}
		else
		{
			$tpl->set_template('login.htm');
			$tpl->add_variables(array(
				'login_target' => 'admin.php',
				'error_msg' => $this->_error_msg,
				'charset' => 'charset='.BS_HTML_CHARSET,
				'page_title' => sprintf($locale->lang('page_title'),BS_VERSION)
			));
			$res = $tpl->parse_template();
		}

		$this->finish();
		return $res;
	}
}
?>
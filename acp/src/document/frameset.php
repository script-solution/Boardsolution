<?php
/**
 * Contains the acp-frameset-document-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.document
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		  	'login_target' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
		  	'error_msg' => $this->_error_msg,
				'charset' => 'charset='.BS_HTML_CHARSET,
		  	'page_title' => sprintf($locale->lang('page_title'),BS_VERSION)
		  ));
		  $res = $tpl->parse_template();
		}
		
		$this->finish();
		return $res;
	}

	/**
	 * @see FWS_Document::load_module()
	 *
	 * @return BS_Front_Module
	 */
	protected function load_module()
	{
		// no module here
		return null;
	}
}
?>
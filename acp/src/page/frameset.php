<?php
/**
 * Contains acp-frameset-page
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The frame-set / login-page of the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Page_FrameSet extends BS_ACP_Page
{
	/**
	 * The login-error-message
	 *
	 * @var string
	 */
	private $_error_msg = '';

	/**
	 * @see PLIB_Page::init()
	 */
	protected function init()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		
		// login the user?
		if($input->isset_var('login','post'))
		{
			$username = $input->get_var('user_login','post',PLIB_Input::STRING);
			$pw = $input->get_var('pw_login','post',PLIB_Input::STRING);
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
	 * @see PLIB_Page::content()
	 */
	protected function content()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$user = PLIB_Props::get()->user();
		
		if($user->is_loggedin())
		{
			$navi_file = PLIB_Path::client_app().'admin.php?page=navi&amp;';
			$navi_file .= BS_URL_SID.'='.$user->get_session_id();
			
			$content_file = PLIB_Path::client_app().'admin.php?page=content&amp;';
			$content_file .= BS_URL_SID.'='.$user->get_session_id();
			
		  $tpl->set_template('frameset.htm');
		  $tpl->add_variables(array(
		  	'navi_file' => $navi_file,
		  	'content_file' => $content_file,
				'charset' => 'charset='.BS_HTML_CHARSET,
		  	'page_title' => sprintf($locale->lang('page_title'),BS_VERSION)
		  ));
		  $tpl->restore_template();
		  
		  $this->set_template('frameset.htm');
		}
		else
		{
		  $tpl->set_template('login.htm');
		  $tpl->add_variables(array(
		  	'login_target' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
		  	'error_msg' => $this->_error_msg,
				'charset' => 'charset='.BS_HTML_CHARSET,
		  	'page_title' => sprintf($locale->lang('page_title'),BS_VERSION)
		  ));
		  $tpl->restore_template();
		  
		  $this->set_template('login.htm');
		}
	}

	/**
	 * @see PLIB_Page::footer()
	 */
	protected function footer()
	{
		// do nothing
	}

	/**
	 * @see PLIB_Page::header()
	 */
	protected function header()
	{
		// do nothing
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
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
final class BS_ACP_Page_FrameSet extends BS_ACP_Document
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
			
			$this->_start_document(BS_ENABLE_ADMIN_GZIP);
			
			// login the user?
			$error_msg = '';
			if($this->input->isset_var('login','post'))
			{
				$user = $this->input->get_var('user_login','post',PLIB_Input::STRING);
				$pw = $this->input->get_var('pw_login','post',PLIB_Input::STRING);
				$error_code = $this->user->login($user,$pw,true);
				if($error_code == BS_User_Current::LOGIN_ERROR_MAX_LOGIN_TRIES)
					$error_msg = $this->locale->lang('max_login_tries_notice');
				else if($error_code != BS_User_Current::LOGIN_ERROR_NO_ERROR)
					$error_msg = $this->locale->lang('error_login');
			}
			
			// logout?
			if($this->user->is_loggedin() && $this->input->get_var('logout','get') == 1)
				$this->user->logout();
			
			if($this->user->is_loggedin())
			{
				$navi_file = PLIB_Path::inner().'admin.php?page=navi&amp;';
				$navi_file .= BS_URL_SID.'='.$this->user->get_session_id();
				
				$content_file = PLIB_Path::inner().'admin.php?page=content&amp;';
				$content_file .= BS_URL_SID.'='.$this->user->get_session_id();
				
			  $this->tpl->set_template('frameset.htm',0);
			  $this->tpl->add_variables(array(
			  	'navi_file' => $navi_file,
			  	'content_file' => $content_file,
					'charset' => 'charset='.BS_HTML_CHARSET,
			  	'page_title' => sprintf($this->locale->lang('page_title'),BS_VERSION)
			  ));
			  echo $this->tpl->parse_template();
			}
			else
			{
			  $this->tpl->set_template('login.htm',0);
			  $this->tpl->add_variables(array(
			  	'login_target' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			  	'error_msg' => $error_msg,
					'charset' => 'charset='.BS_HTML_CHARSET,
			  	'page_title' => sprintf($this->locale->lang('page_title'),BS_VERSION)
			  ));
			  echo $this->tpl->parse_template();
			}
			
			$this->_finish();
	
			$this->_send_document(BS_ENABLE_ADMIN_GZIP);
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
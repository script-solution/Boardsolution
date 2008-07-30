<?php
/**
 * Contains dba-document
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The document of the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Document extends PLIB_Document
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
	
			$this->_handle_session();
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}
	
	/**
	 * @see PLIB_Document::use_default_renderer
	 *
	 * @return BS_DBA_Renderer_HTML
	 */
	public function use_default_renderer()
	{
		$renderer = $this->get_renderer();
		if($renderer instanceof BS_DBA_Renderer_HTML)
			return $renderer;
		
		$renderer = new BS_DBA_Renderer_HTML();
		$this->set_renderer($renderer);
		return $renderer;
	}

	/**
	 * @see PLIB_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		parent::prepare_rendering();
		
		$this->set_charset(BS_HTML_CHARSET);
		$this->set_gzip(BS_DBA_ENABLE_GZIP);
		
		// set default renderer
		if($this->get_renderer() === null)
			$this->use_default_renderer();
	}

	/**
	 * @see PLIB_Document::load_module()
	 *
	 * @return BS_DBA_Module
	 */
	protected function load_module()
	{
		$this->_module_name = PLIB_Helper::get_module_name(
			'BS_DBA_Module_','action','index','dba/module/'
		);
		$class = 'BS_DBA_Module_'.$this->_module_name;
		return new $class();
	}
	
	/**
	 * Handles all session-operations
	 */
	private function _handle_session()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		
		// we want to require a session-id via GET
		if($input->get_var('sid','get',PLIB_Input::STRING) != $user->get_session_id())
			$user->logout();
		
		if(!$user->is_loggedin())
		{
			if($input->isset_var('login','post'))
			{
				$p_user = $input->get_var('user_login','post',PLIB_Input::STRING);
				$p_pw = $input->get_var('pw_login','post',PLIB_Input::STRING);
				$user->login($p_user,$p_pw);
			}
		}
		else if($input->isset_var('logout','get'))
			$user->logout();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
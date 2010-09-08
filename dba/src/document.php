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
final class BS_DBA_Document extends FWS_Document
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
		catch(FWS_Exception_Critical $e)
		{
			echo $e;
		}
	}
	
	/**
	 * Returns the default renderer. If it is already set the instance will be returned. Otherwise
	 * it will be created, set and returned.
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
	 * @see FWS_Document::prepare_rendering()
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
	 * @see FWS_Document::load_module()
	 *
	 * @return BS_DBA_Module
	 */
	protected function load_module()
	{
		$this->_module_name = FWS_Document::load_module_def(
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
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		
		// we want to require a session-id via GET
		if($input->get_var('sid','get',FWS_Input::STRING) != $user->get_session_id())
			$user->logout();
		
		if(!$user->is_loggedin())
		{
			if($input->isset_var('login','post'))
			{
				$p_user = $input->get_var('user_login','post',FWS_Input::STRING);
				$p_pw = $input->get_var('pw_login','post',FWS_Input::STRING);
				$user->login($p_user,$p_pw);
			}
		}
		else if($input->isset_var('logout','get'))
			$user->logout();
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
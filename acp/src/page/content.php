<?php
/**
 * Contains acp-content-page
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The content-page of the ACP. This page contains the modules.
 *
 * @package			Boardsolution
 * @subpackage	acp.src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Page_Content extends BS_ACP_Document
{
	/**
	 * The current module
	 *
	 * @var BS_ACP_Module
	 */
	private $_module;

	/**
	 * The name of the current module
	 *
	 * @var string
	 */
	private $_module_name;
	
	/**
	 * Indicates wether GZip should be used
	 *
	 * @var boolean
	 */
	private $_use_gzip = BS_ENABLE_ADMIN_GZIP;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
	
			$this->_module = $this->_load_module();
			
			$this->_start_document($this->_use_gzip);
			
			// output
			$this->_add_head();
			$this->_add_module();
			$this->_add_foot();
			
			// add redirect information
			$redirect = $this->get_redirect();
			if($redirect)
				$this->tpl->add_array('redirect',$redirect,'inc_header.htm');
			
			// notify the template if an error has occurred
			$this->tpl->add_global('module_error',$this->_module->error_occurred());
			
			// add messages
			$this->msgs->print_messages();
			
			if($this->_template != '')
				echo $this->tpl->parse_template($this->_template);
			else
				echo $this->tpl->parse_template($this->_module->get_template());
	
			$this->_finish();
	
			$this->_send_document($this->_use_gzip);
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}
	
	/**
	 * Sets wether gzip should be used. This method should be called
	 * before _start_document() will be called, that means for example
	 * in the constructor of a module.
	 *
	 * @param boolean $gzip the new value
	 */
	public function set_use_gzip($gzip)
	{
		$this->_use_gzip = (bool)$gzip;
	}

	/**
	 * Loads the corresponding module
	 * 
	 * @return BS_ACP_Module the loaded module
	 */
	private function _load_module()
	{
		$this->_module_name = PLIB_Helper::get_module_name(
			'BS_ACP_Module_','loc','index','acp/module/'
		);
		$class = 'BS_ACP_Module_'.$this->_module_name;
		$c = new $class();

		$this->_action_perf->set_prefix('BS_ACP_Action_');

		// add actions of the current module
		$this->_action_perf->add_actions($this->_module_name,$c->get_actions());

		return $c;
	}

	/**
	 * Adds the loaded module to the template
	 */
	private function _add_module()
	{
		// perform actions
		$this->perform_actions();
		
		$action_result = $this->get_action_result();
		
		// Note that we may do this here because the template will be parsed later
		// after all is finished!
		
		// add global variables
		$this->tpl->add_global('action_result',$action_result);
		$this->tpl->add_global('module_error',false);
		
		if(!$this->auth->has_access_to_module($this->_module_name))
		{
			if($this->_module_name == 'index')
				$this->msgs->add_notice($this->locale->lang('welcome_message'));
			else
				$this->msgs->add_error($this->locale->lang('access_to_module_denied'));
			$this->_module->set_error();
		}
		else
		{
			$this->tpl->set_template($this->_module->get_template());
			$this->_module->run();
			$this->tpl->restore_template();
		}
	}

	/**
	 * Adds the header to the page
	 *
	 */
	private function _add_head()
	{
		$title = PLIB_Helper::generate_location(
			$this->_module,$this->locale->lang('adminarea'),$this->url->get_acpmod_url('index')
		);
		
		$this->tpl->set_template('inc_header.htm');
		$this->tpl->add_variables(array(
			'charset' => 'charset='.BS_HTML_CHARSET,
			'position' => $title['position'],
			'cookie_path' => $this->cfg['cookie_path'],
			'cookie_domain' => $this->cfg['cookie_domain'],
			 'page_title' => sprintf($this->locale->lang('page_title'),BS_VERSION)
		));
		$this->tpl->restore_template();
	}

	/**
	 * Adds the footer to the page
	 *
	 */
	private function _add_foot()
	{
		$this->tpl->set_template('inc_footer.htm');
		$this->tpl->add_variables(array(
			'debug' => BS_DEBUG,
			'render_time' => $this->get_script_time(),
			'db_queries' => $this->db->get_performed_query_num(),
			'queries' => PLIB_PrintUtils::to_string($this->db->get_performed_queries())
		));
		$this->tpl->restore_template();
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
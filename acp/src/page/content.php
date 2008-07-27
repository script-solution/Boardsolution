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
final class BS_ACP_Page_Content extends BS_ACP_Page
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
	 * Wether the headline should be displayed
	 *
	 * @var boolean
	 */
	private $_show_headline = true;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
	
			$this->_module = $this->_load_module();
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}

	/**
	 * @return boolean true if the headline will be shown
	 */
	public final function is_headline_shown()
	{
		return $this->_show_headline;
	}
	
	/**
	 * Sets wether the headline should be shown
	 *
	 * @param boolean $show the new value
	 */
	public final function set_show_headline($show)
	{
		$this->_show_headline = (bool)$show;
	}
	
	/**
	 * @see BS_Page::before_start()
	 */
	protected function before_start()
	{
		parent::before_start();
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		
		// add the home-breadcrumb
		$this->add_breadcrumb($locale->lang('adminarea'),$url->get_acpmod_url('index'));
		
		$this->_action_perf->set_prefix('BS_ACP_Action_');
		
		// init the module
		$this->_module->init($this);
		
		if($this->is_output_enabled())
			$user->set_location();
	}

	/**
	 * @see PLIB_Page::content()
	 */
	protected function content()
	{
		$tpl = PLIB_Props::get()->tpl();
		$auth = PLIB_Props::get()->auth();
		$msgs = PLIB_Props::get()->msgs();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();
		
		if($user->is_loggedin() && !$auth->has_access_to_module($this->_module_name))
		{
			if($this->_module_name == 'index')
				$msgs->add_notice($locale->lang('welcome_message'));
			else
				$msgs->add_error($locale->lang('access_to_module_denied'));
			$this->set_error();
		}
		else
		{
			// set the default template if not already done
			if($this->get_template() === null)
			{
				$prefixlen = PLIB_String::strlen('BS_ACP_Module_');
				$classname = get_class($this->_module);
				$template = PLIB_String::strtolower(PLIB_String::substr($classname,$prefixlen)).'.htm';
				$this->set_template($template);
			}
			
			if($user->is_loggedin())
			{
				$tpl->set_template($this->get_template());
				$this->_module->run();
				$tpl->restore_template();
			}
			else
				$this->set_error();
		}
	}

	/**
	 * @see PLIB_Page::before_render()
	 */
	protected final function before_render()
	{
		$tpl = PLIB_Props::get()->tpl();
		$msgs = PLIB_Props::get()->msgs();
		
		// add redirect information
		$redirect = $this->get_redirect();
		if($redirect)
			$tpl->add_array('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$this->error_occurred());
		
		// add messages
		$msgs->add_messages();
		
		$this->set_gzip(BS_ENABLE_ADMIN_GZIP);
	}

	/**
	 * @see PLIB_Page::header()
	 */
	protected function header()
	{
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();

		// perform actions
		$this->perform_actions();
		
		$action_result = $this->get_action_result();
		
		$this->set_title(sprintf($locale->lang('page_title'),BS_VERSION));
		
		// add global variables
		$tpl->add_global('action_result',$action_result);
		$tpl->add_global('module_error',false);

		$tpl->set_template('inc_header.htm');
		$tpl->add_variables(array(
			'charset' => 'charset='.BS_HTML_CHARSET,
			'position' => $this->_show_headline ? PLIB_Helper::generate_location($this) : '',
			'cookie_path' => $cfg['cookie_path'],
			'cookie_domain' => $cfg['cookie_domain'],
			'title' => $this->get_title(),
			'charset' => 'charset='.$this->get_charset(),
			'mimetype' => $this->get_mimetype(),
			'cssfiles' => $this->get_css_files(),
			'cssblocks' => $this->get_css_blocks(),
			'jsfiles' => $this->get_js_files(),
			'jsblocks' => $this->get_js_blocks(),
			'sig_max_height' => $cfg['sig_max_height']
		));
		$tpl->restore_template();
	}

	/**
	 * @see PLIB_Page::footer()
	 */
	protected function footer()
	{
		$tpl = PLIB_Props::get()->tpl();
		$db = PLIB_Props::get()->db();
		$profiler = PLIB_Props::get()->profiler();

		$tpl->set_template('inc_footer.htm');
		$tpl->add_variables(array(
			'debug' => BS_DEBUG,
			'render_time' => $profiler->get_time(),
			'db_queries' => $db->get_performed_query_num(),
			'queries' => PLIB_PrintUtils::to_string(
				array(
					'Load properties' => array_keys(PLIB_Props::get()->get_all()),
					'DB-Queries' => $db->get_performed_queries()
				)
			)
		));
		$tpl->restore_template();
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
		return new $class();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
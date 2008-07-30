<?php
/**
 * Contains acp-content-page
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The content-renderer of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Renderer_Content extends PLIB_Document_Renderer_HTML_Default
{
	/**
	 * Wether the headline should be displayed
	 *
	 * @var boolean
	 */
	private $_show_headline = true;

	/**
	 * @return boolean true if the headline will be shown
	 */
	public function is_headline_shown()
	{
		return $this->_show_headline;
	}
	
	/**
	 * Sets wether the headline should be shown
	 *
	 * @param boolean $show the new value
	 */
	public function set_show_headline($show)
	{
		$this->_show_headline = (bool)$show;
	}
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		
		// always set the location in html-pages
		$user->set_location();
		
		// add the home-breadcrumb
		$this->add_breadcrumb($locale->lang('adminarea'),$url->get_acpmod_url('index'));
		
		$this->_action_perf->set_prefix('BS_ACP_Action_');
	}
	
	/**
	 * @see BS_Page::before_start()
	 */
	protected function before_start()
	{
		parent::before_start();
		
		$doc = PLIB_Props::get()->doc();
		
		// set the default template if not already done
		if($this->get_template() === null)
		{
			$prefixlen = PLIB_String::strlen('BS_ACP_Module_');
			$classname = get_class($doc->get_module());
			$template = PLIB_String::strtolower(PLIB_String::substr($classname,$prefixlen)).'.htm';
			$this->set_template($template);
		}
	}

	/**
	 * @see PLIB_Page::content()
	 */
	protected function content()
	{
		$auth = PLIB_Props::get()->auth();
		$msgs = PLIB_Props::get()->msgs();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();
		$doc = PLIB_Props::get()->doc();
		
		if($user->is_loggedin() && !$auth->has_access_to_module($doc->get_module_name()))
		{
			if($this->_module_name == 'index')
				$msgs->add_notice($locale->lang('welcome_message'));
			else
				$msgs->add_error($locale->lang('access_to_module_denied'));
			$doc->get_module()->set_error();
		}
		else
		{
			if($user->is_loggedin())
				parent::content();
			else
				$doc->get_module()->set_error();
		}
	}

	/**
	 * @see PLIB_Page::before_render()
	 */
	protected function before_render()
	{
		$tpl = PLIB_Props::get()->tpl();
		$doc = PLIB_Props::get()->doc();
		
		// add redirect information
		$redirect = $doc->get_redirect();
		if($redirect)
			$tpl->add_array('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$doc->get_module()->error_occurred());
		
		$action_result = $this->get_action_result();
		$tpl->add_global('action_result',$action_result);
		
		// handle messages
		$msgs = PLIB_Props::get()->msgs();
		if($msgs->contains_msg())
			$this->handle_msgs($msgs);
		
		$doc->set_gzip(BS_ENABLE_ADMIN_GZIP);
	}

	/**
	 * Handles the collected messages
	 *
	 * @param PLIB_Document_Messages $msgs
	 */
	protected function handle_msgs($msgs)
	{
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		
		$amsgs = $msgs->get_all_messages();
		$links = $msgs->get_links();
		$tpl->set_template('inc_messages.htm');
		$tpl->add_array('errors',$amsgs[PLIB_Document_Messages::ERROR]);
		$tpl->add_array('warnings',$amsgs[PLIB_Document_Messages::WARNING]);
		$tpl->add_array('notices',$amsgs[PLIB_Document_Messages::NOTICE]);
		$tpl->add_array('links',$links);
		$tpl->add_variables(array(
			'title' => $locale->lang('information'),
			'messages' => $msgs->contains_error() || $msgs->contains_notice() || $msgs->contains_warning()
		));
		$tpl->restore_template();
	}

	/**
	 * @see PLIB_Page::header()
	 */
	protected function header()
	{
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$doc = PLIB_Props::get()->doc();

		// perform actions
		$this->perform_actions();
		
		$tpl->set_template('inc_header.htm');
		$tpl->add_variables(array(
			'charset' => 'charset='.BS_HTML_CHARSET,
			'position' => $this->_show_headline ? $this->get_breadcrumbs() : '',
			'cookie_path' => $cfg['cookie_path'],
			'cookie_domain' => $cfg['cookie_domain'],
			'title' => sprintf($locale->lang('page_title'),BS_VERSION),
			'charset' => 'charset='.$doc->get_charset(),
			'mimetype' => $doc->get_mimetype(),
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
	 * @see PLIB_Document_Renderer_HTML_Default::load_action_perf()
	 *
	 * @return BS_ACP_Action_Performer
	 */
	protected function load_action_perf()
	{
		return new BS_ACP_Action_Performer();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
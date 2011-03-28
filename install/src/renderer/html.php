<?php
/**
 * Contains the install-html-renderer-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The HTML-renderer for the install-script
 *
 * @package			Boardsolution
 * @subpackage	install.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Renderer_HTML extends FWS_Document_Renderer_HTML_Default
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		FWS_URL::set_append_extern_vars(false);
		
		// set language?
		if($input->isset_var('lang','get'))
		{
			$lang = $input->correct_var('lang','get',FWS_Input::STRING,
				array('ger_du','ger_sie','en','dk'),'ger_du');
			$user->set_session_data('lang',$lang);
		}
		
		$lang = $user->get_session_data('lang','ger_du');
		$locale->add_language_file('install',$lang);

		$tpl->set_path('install/templates/');
		$tpl->set_cache_folder(FWS_Path::server_app().'cache/');
		
		$this->_action_perf->set_prefix('BS_Install_Action_');
		$this->_action_perf->set_mod_folder('install/module/');
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::before_start()
	 */
	protected function before_start()
	{
		$doc = FWS_Props::get()->doc();
		
		// set the default template if not already done
		$template = '';
		if($this->get_template() === null)
		{
			$classname = get_class($doc->get_module());
			$prefixlen = FWS_String::strlen('BS_Install_Module_');
			$template = FWS_String::strtolower(FWS_String::substr($classname,$prefixlen)).'.htm';
			$this->set_template($template);
		}
	}
	
	/**
	 * @see FWS_Document_Renderer_HTML_Default::before_render()
	 */
	protected function before_render()
	{
		$tpl = FWS_Props::get()->tpl();
		$msgs = FWS_Props::get()->msgs();
		$user = FWS_Props::get()->user();
		$doc = FWS_Props::get()->doc();
		
		// add redirect information
		$redirect = $doc->get_redirect();
		if($redirect)
			$tpl->add_variable_ref('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$doc->get_module()->error_occurred());
		
		// add messages
		if($msgs->contains_msg())
			$this->handle_msgs($msgs);
	}

	/**
	 * Handles the collected messages
	 *
	 * @param FWS_Document_Messages $msgs
	 */
	protected function handle_msgs($msgs)
	{
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		
		$amsgs = $msgs->get_all_messages();
		$links = $msgs->get_links();
		$tpl->set_template('inc_messages.htm');
		$tpl->add_variable_ref('errors',$amsgs[FWS_Document_Messages::ERROR]);
		$tpl->add_variable_ref('warnings',$amsgs[FWS_Document_Messages::WARNING]);
		$tpl->add_variable_ref('notices',$amsgs[FWS_Document_Messages::NOTICE]);
		$tpl->add_variable_ref('links',$links);
		$tpl->add_variables(array(
			'title' => $locale->lang('information'),
			'messages' => $msgs->contains_error() || $msgs->contains_notice() || $msgs->contains_warning()
		));
		$tpl->restore_template();
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::header()
	 */
	protected function header()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		$doc = FWS_Props::get()->doc();
		$step = (int)$doc->get_module_name();
		$locale = FWS_Props::get()->locale();
		
		$lang = $user->get_session_data('lang','ger_du');
		$turl = new FWS_URL();
		$turl->set('action',$step);
		
		$tpl->set_template('inc_header.htm');
		$tpl->add_variables(array(
			'target_url' => $turl->to_url(),
			'step' => $step,
			'sel_ger_du' => $lang == 'ger_du' ? ' selected="selected"' : '',
			'sel_ger_sie' => $lang == 'ger_sie' ? ' selected="selected"' : '',
			'sel_en' => $lang == 'en' ? ' selected="selected"' : '',
			'charset' => 'charset='.BS_HTML_CHARSET,
			'title' => sprintf($locale->lang('installationtitle'),BS_VERSION)
		));
		$tpl->restore_template();
		
		$url = new FWS_URL();
		$url->set('action',$step);
		$url->set('at',$step);
		
		$tpl->set_template('inc_navi.htm');
		$tpl->add_variables(array(
			'step' => $step,
			'back_url' => $url->set('dir','back')->to_url(),
			'forward_url' => $url->set('dir','forward')->to_url()
		));
		$tpl->restore_template();
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::footer()
	 */
	protected function footer()
	{
		$tpl = FWS_Props::get()->tpl();
		$doc = FWS_Props::get()->doc();
		$step = (int)$doc->get_module_name();
		$tpl->add_variables(array(
			'step' => $step
		),'inc_footer.htm');
	}
}
?>
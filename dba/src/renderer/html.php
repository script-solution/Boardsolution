<?php
/**
 * Contains the dba-html-renderer-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The HTML-renderer for the DBA-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Renderer_HTML extends FWS_Document_Renderer_HTML_Default
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		FWS_URL::set_append_extern_vars(false);
		
		include_once(FWS_Path::server_app().'config/actions.php');
		
		$locale->add_language_file('dbbackup',BS_DBA_LANGUAGE);
		$locale->add_language_file('index',BS_DBA_LANGUAGE);

		$tpl->set_path('dba/templates/');
		$tpl->set_cache_folder(FWS_Path::server_app().'cache/');
		
		// add the home-breadcrumb
		$this->add_breadcrumb($locale->lang('dbbackup'),BS_DBA_URL::build_url('index'));
		
		$this->_action_perf->set_prefix('BS_DBA_Action_');
		$this->_action_perf->set_mod_folder('dba/module/');
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
			$prefixlen = FWS_String::strlen('BS_DBA_Module_');
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
		
		// add some global variables
		$tpl->add_global('gisloggedin',$user->is_loggedin());
		
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
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		$doc = FWS_Props::get()->doc();
		
		$this->perform_action();
		
		// change db?
		if($input->isset_var('selectdb','post'))
		{
			$dbname = $input->get_var('database','post',FWS_Input::STRING);
			if($dbname !== null)
			{
				try
				{
					$db->get_row('SHOW DATABASES LIKE "'.$dbname.'"');
					$user->set_session_data('database',$dbname);
				}
				catch(FWS_DB_Exception_QueryFailed $ex)
				{
					// ignore
				}
			}
		}
		
		$breadcrumbs = $this->get_breadcrumbs();
		$class = FWS_String::strtolower(get_class($doc->get_module()));
		$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
		$show_db_combo = $user->is_loggedin() &&
			($class == 'bs_dba_module_index' || $class == 'bs_dba_module_backups');
		
		if($show_db_combo)
		{
			// collect available dbs
			$dbs = array();
			$rows = $db->get_rows('SHOW DATABASES',false);
			if($rows !== false)
			{
				foreach($rows as $data)
					$dbs[$data['Database']] = $data['Database'];
			}
			else
				$dbs[BS_MYSQL_DATABASE] = BS_MYSQL_DATABASE;
			
			//form.get_combobox('database',databases,selected_db)
			$dbcombo = new FWS_HTML_ComboBox('database','database',$selected_db,null);
			$dbcombo->set_options($dbs);
			$dbcombo->set_option_style(BS_MYSQL_DATABASE,'font-weight','bold');
		}
		
		$tpl->set_template('inc_header.htm');
		$tpl->add_variables(array(
			'charset' => 'charset='.$doc->get_charset(),
			'position' => $breadcrumbs,
			'board_url' => '../'.$functions->get_board_file(false),
			'is_loggedin' => $user->is_loggedin(),
			'db_combo' => $show_db_combo ? $dbcombo->to_html() : '',
			'show_db_combo' => $show_db_combo
		));
		$tpl->restore_template();
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::content()
	 */
	protected function content()
	{
		$user = FWS_Props::get()->user();

		if(!$user->is_loggedin())
			$this->set_template('login.htm');
		
		parent::content();
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::footer()
	 */
	protected function footer()
	{
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$db = FWS_Props::get()->db();
		$doc = FWS_Props::get()->doc();
		$profiler = $doc->get_profiler();
		
		$mem = FWS_StringHelper::get_formated_data_size(
			$profiler->get_memory_usage(),$locale->get_thousands_separator(),
			$locale->get_dec_separator()
		);
		
		$tpl->set_template('inc_footer.htm');
		$tpl->add_variables(array(
			'debug' => BS_DEBUG,
			'time' => $profiler->get_time(),
			'query_count' => $db->get_query_count(),
			'memory' => $mem,
			'queries' => FWS_Printer::to_string($db->get_queries())
		));
		$tpl->restore_template();
	}
}
?>
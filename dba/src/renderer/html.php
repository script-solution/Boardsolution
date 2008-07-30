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
final class BS_DBA_Renderer_HTML extends PLIB_Document_Renderer_HTML_Default
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$tpl = PLIB_Props::get()->tpl();
		
		include_once(PLIB_Path::server_app().'config/actions.php');
		
		$locale->add_language_file('dbbackup',BS_DBA_LANGUAGE);
		$locale->add_language_file('index',BS_DBA_LANGUAGE);

		$tpl->set_path('dba/templates/');
		$tpl->set_cache_folder(PLIB_Path::server_app().'cache/');
		
		// add the home-breadcrumb
		$this->add_breadcrumb($locale->lang('dbbackup'),$url->get_url('index'));
		
		$this->_action_perf->set_prefix('BS_DBA_Action_');
		$this->_action_perf->set_mod_folder('dba/module/');
	}

	/**
	 * @see PLIB_Document_Renderer_HTML_Default::before_start()
	 */
	protected function before_start()
	{
		$doc = PLIB_Props::get()->doc();
		
		// set the default template if not already done
		$template = '';
		if($this->get_template() === null)
		{
			$classname = get_class($doc->get_module());
			$prefixlen = PLIB_String::strlen('BS_DBA_Module_');
			$template = PLIB_String::strtolower(PLIB_String::substr($classname,$prefixlen)).'.htm';
			$this->set_template($template);
		}
	}
	
	/**
	 * @see PLIB_Document_Renderer_HTML_Default::before_render()
	 */
	protected function before_render()
	{
		$tpl = PLIB_Props::get()->tpl();
		$msgs = PLIB_Props::get()->msgs();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$doc = PLIB_Props::get()->doc();
		
		// add redirect information
		$redirect = $doc->get_redirect();
		if($redirect)
			$tpl->add_array('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$doc->get_module()->error_occurred());
		
		// add some global variables
		$tpl->add_global('gisloggedin',$user->is_loggedin());
		$tpl->add_global('gpath',PLIB_Path::client_app());
		$tpl->add_global('glibpath',PLIB_Path::client_lib());
		
		$js = PLIB_Javascript::get_instance();
		$js->set_cache_folder('cache/');
		$tpl->add_global_ref('gjs',$js);
		$tpl->add_global_ref('glocale',$locale);
		$tpl->add_global_ref('gurl',$url);
		
		// set callable methods
		$tpl->add_allowed_method('gjs','get_file');
		$tpl->add_allowed_method('glocale','lang');
		$tpl->add_allowed_method('gurl','get_url');
		
		// add messages
		if($msgs->contains_msg())
			$this->handle_msgs($msgs);
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
	 * @see PLIB_Document_Renderer_HTML_Default::header()
	 */
	protected function header()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();
		$db = PLIB_Props::get()->db();
		$user = PLIB_Props::get()->user();
		$doc = PLIB_Props::get()->doc();
		
		$this->perform_actions();
		
		// change db?
		if($input->isset_var('selectdb','post'))
		{
			$dbname = $input->get_var('database','post',PLIB_Input::STRING);
			if($dbname !== null)
			{
				if($db->sql_fetch_assoc($db->sql_qry('SHOW DATABASES LIKE "'.$dbname.'"')))
					$user->set_session_data('database',$dbname);
			}
		}
		
		$breadcrumbs = $this->get_breadcrumbs();
		$class = PLIB_String::strtolower(get_class($doc->get_module()));
		$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
		$show_db_combo = $user->is_loggedin() &&
			($class == 'bs_dba_module_index' || $class == 'bs_dba_module_backups');
		
		if($show_db_combo)
		{
			// collect available dbs
			$dbs = array();
			$qry = $db->sql_qry('SHOW DATABASES');
			while($data = $db->sql_fetch_assoc($qry))
				$dbs[$data['Database']] = $data['Database'];
			$db->sql_free($qry);
			
			//form.get_combobox('database',databases,selected_db)
			$dbcombo = new PLIB_HTML_ComboBox('database','database',$selected_db,null);
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
	 * @see PLIB_Document_Renderer_HTML_Default::content()
	 */
	protected function content()
	{
		$user = PLIB_Props::get()->user();

		if(!$user->is_loggedin())
			$this->set_template('login.htm');
		
		parent::content();
	}

	/**
	 * @see PLIB_Document_Renderer_HTML_Default::footer()
	 */
	protected function footer()
	{
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$db = PLIB_Props::get()->db();
		$profiler = PLIB_Props::get()->profiler();

		$mem = PLIB_StringHelper::get_formated_data_size(
			$profiler->get_memory_usage(),$locale->get_thousands_separator(),
			$locale->get_dec_separator()
		);
		
		$tpl->set_template('inc_footer.htm');
		$tpl->add_variables(array(
			'debug' => BS_DEBUG,
			'time' => $profiler->get_time(),
			'query_count' => $db->get_performed_query_num(),
			'memory' => $mem,
			'queries' => PLIB_PrintUtils::to_string($db->get_performed_queries())
		));
		$tpl->restore_template();
	}
}
?>
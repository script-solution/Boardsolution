<?php
/**
 * Contains acp-content-page
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The content-page of the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Page extends PLIB_Page
{
	/**
	 * The current module
	 *
	 * @var BS_DBA_Module
	 */
	private $_module;

	/**
	 * The name of the current module
	 *
	 * @var string
	 */
	private $_module_name;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
	
			$this->_handle_session();
			$this->_module = $this->_load_module();
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}
	
	/**
	 * @see BS_Page::before_start()
	 */
	protected function before_start()
	{
		parent::before_start();
		
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
		
		// init the module
		$this->_module->init($this);

		// add actions of the current module
		// TODO we can improve that, right?
		$this->_action_perf->add_actions($this->_module_name,$this->get_actions());
		
		$this->perform_actions();
		
		// set the default template if not already done
		$template = '';
		if($this->get_template() === null)
		{
			$classname = get_class($this->_module);
			$prefixlen = PLIB_String::strlen('BS_DBA_Module_');
			$template = PLIB_String::strtolower(PLIB_String::substr($classname,$prefixlen)).'.htm';
			$this->set_template($template);
		}
	}

	/**
	 * @see PLIB_Page::before_render()
	 */
	protected final function before_render()
	{
		$tpl = PLIB_Props::get()->tpl();
		$msgs = PLIB_Props::get()->msgs();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		// add redirect information
		$redirect = $this->get_redirect();
		if($redirect)
			$tpl->add_array('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$this->error_occurred());
		
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
		$msgs->add_messages();
		
		$this->set_charset(BS_HTML_CHARSET);
		$this->set_gzip(BS_DBA_ENABLE_GZIP);
	}

	/**
	 * @see PLIB_Page::header()
	 */
	protected function header()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();
		$db = PLIB_Props::get()->db();
		$user = PLIB_Props::get()->user();
		
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
		
		$breadcrumbs = PLIB_Helper::generate_location($this);
		$class = PLIB_String::strtolower(get_class($this->_module));
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
			'charset' => 'charset='.$this->get_charset(),
			'position' => $breadcrumbs,
			'board_url' => '../'.$functions->get_board_file(false),
			'is_loggedin' => $user->is_loggedin(),
			'db_combo' => $show_db_combo ? $dbcombo->to_html() : '',
			'show_db_combo' => $show_db_combo
		));
		$tpl->restore_template();
	}

	/**
	 * @see PLIB_Page::content()
	 */
	protected final function content()
	{
		$tpl = PLIB_Props::get()->tpl();
		$user = PLIB_Props::get()->user();

		// run the module
		if($user->is_loggedin())
		{
			$tpl->set_template($this->get_template());
			$this->_module->run();
			$tpl->restore_template();
		}
		else
			$this->set_template('login.htm');
	}

	/**
	 * @see PLIB_Page::footer()
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

	/**
	 * Loads the corresponding module
	 *
	 * @return BS_DBA_Module the loaded module
	 */
	private function _load_module()
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
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
final class BS_DBA_Page extends BS_DBA_Document
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
	private $_use_gzip = BS_DBA_ENABLE_GZIP;

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
			
			if($_SESSION['loggedin'])
				echo $this->tpl->parse_template($this->_module->get_template());
			else
				echo $this->tpl->parse_template('login.htm');
	
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
	 * @return BS_DBA_Module the loaded module
	 */
	private function _load_module()
	{
		$this->_module_name = PLIB_Helper::get_module_name(
			'BS_DBA_Module_','action','index','dba/module/'
		);
		$class = 'BS_DBA_Module_'.$this->_module_name;
		$c = new $class();

		$this->_action_perf->set_prefix('BS_DBA_Action_');
		$this->_action_perf->set_mod_folder('dba/module/');

		// add actions of the current module
		$this->_action_perf->add_actions($this->_module_name,$c->get_actions());

		return $c;
	}

	/**
	 * Adds the loaded module to the template
	 *
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
		
		if($_SESSION['loggedin'])
		{
			$this->tpl->set_template($this->_module->get_template());
			$this->_module->run();
			$this->tpl->restore_template();
		}
		else
		{
			$this->tpl->set_template('login.htm');
			$this->tpl->restore_template();
		}
	}

	/**
	 * Adds the header to the page
	 *
	 */
	private function _add_head()
	{
		// collect available dbs
		$dbs = array();
		$qry = $this->db->sql_qry('SHOW DATABASES');
		while($data = $this->db->sql_fetch_assoc($qry))
			$dbs[$data['Database']] = $data['Database'];
		$this->db->sql_free($qry);
		
		// change db?
		if($this->input->isset_var('selectdb','post'))
		{
			$db = $this->input->get_var('database','post',PLIB_Input::STRING);
			if($db !== null)
			{
				if($this->db->sql_fetch_assoc($this->db->sql_qry('SHOW DATABASES LIKE "'.$db.'"')))
					$_SESSION['database'] = $db;
			}
		}
		
		$title = PLIB_Helper::generate_location(
			$this->_module,$this->locale->lang('dbbackup'),$this->url->get_url('index')
		);
		$class = PLIB_String::strtolower(get_class($this->_module));
		$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
		$show_db_combo = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] &&
			($class == 'bs_dba_module_index' || $class == 'bs_dba_module_backups');
		
		//form.get_combobox('database',databases,selected_db)
		$dbcombo = new PLIB_HTML_ComboBox('database','database',$selected_db,null);
		$dbcombo->set_options($dbs);
		$dbcombo->set_option_style(BS_MYSQL_DATABASE,'font-weight','bold');
		
		$this->tpl->set_template('inc_header.htm');
		$this->tpl->add_variables(array(
			'charset' => 'charset='.BS_HTML_CHARSET,
			'position' => $title['position'],
			'board_url' => '../'.$this->functions->get_board_file(false),
			'is_loggedin' => $_SESSION['loggedin'],
			'db_combo' => $dbcombo->to_html(),
			'show_db_combo' => $show_db_combo
		));
		$this->tpl->restore_template();
	}

	/**
	 * Adds the footer to the page
	 *
	 */
	private function _add_foot()
	{
		$mem = PLIB_StringHelper::get_formated_data_size(
			memory_get_usage(),$this->locale->get_thousands_separator(),
			$this->locale->get_dec_separator()
		);
		
		$this->tpl->set_template('inc_footer.htm');
		$this->tpl->add_variables(array(
			'debug' => BS_DEBUG,
			'time' => $this->get_script_time(),
			'query_count' => $this->_module->db->get_performed_query_num(),
			'memory' => $mem,
			'queries' => PLIB_PrintUtils::to_string($this->db->get_performed_queries())
		));
		$this->tpl->restore_template();
	}
	
	/**
	 * Handles all session-operations
	 */
	private function _handle_session()
	{
		// we want to require a session-id via GET
		if($this->input->get_var(session_name(),'get',PLIB_Input::STRING) != session_id())
			$this->_logout();
		
		if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'])
		{
			$_SESSION['loggedin'] = false;
			if($this->input->isset_var('login','post'))
			{
				include_once(PLIB_Path::inner().'dba/access.php');
				
				$p_user = $this->input->get_var('user_login','post',PLIB_Input::STRING);
				$p_pw = $this->input->get_var('pw_login','post',PLIB_Input::STRING);
				
				if(BS_DBA_USERNAME == $p_user && BS_DBA_PASSWORD == $p_pw)
				{
					$_SESSION['loggedin'] = true;
					$_SESSION['user'] = $p_user;
					$_SESSION['pw'] = $p_pw;
				}
			}
		}
		else if($_SESSION['loggedin'])
		{
			if($this->input->isset_var('logout','get'))
				$this->_logout();
			else
			{
				include_once(PLIB_Path::inner().'dba/access.php');
				if(BS_DBA_USERNAME != $_SESSION['user'] || BS_DBA_PASSWORD != $_SESSION['pw'])
					$_SESSION['loggedin'] = false;
			}
		}
	}
	
	/**
	 * Logouts the user
	 */
	private function _logout()
	{
		unset($_SESSION['user']);
		unset($_SESSION['pw']);
		session_destroy();
		session_start();
		$_SESSION['loggedin'] = false;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
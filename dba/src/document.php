<?php
/**
 * Contains the document-class for the dbbackup-script
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The document for the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_DBA_Document extends PLIB_Document
{
	public function __construct()
	{
		include_once(PLIB_Path::inner().'config/general.php');
		include_once(PLIB_Path::inner().'config/actions.php');
		// we have to start the session here
		session_start();
		
		parent::__construct();
		
		$this->locale->add_language_file('dbbackup',BS_DBA_LANGUAGE);
		$this->locale->add_language_file('index',BS_DBA_LANGUAGE);
	}
	
	protected function _load_action_perf()
	{
		return new PLIB_Actions_Performer();
	}
	
	protected function _send_document($use_gzip = true)
	{
		if(!headers_sent())
			@header('Content-Type: text/html; charset='.BS_HTML_CHARSET);
		
		parent::_send_document($use_gzip);
	}
	
	protected function _get_const_dependency_list()
	{
		$list = parent::_get_const_dependency_list();
		$list['functions'] = array();
		$list['tpl'][] = 'locale';
		$list['backups'] = array();
		return $list;
	}
	
	protected function _get_init_dependency_list()
	{
		$list = parent::_get_init_dependency_list();
		return $list;
	}
	
	protected function _load_backups()
	{
		return new BS_DBA_Backup_Manager(PLIB_Path::inner().'dba/backups/backups.txt');
	}

	protected function _load_tpl()
	{
		$c = new PLIB_Template_Handler();
		$c->set_path(PLIB_Path::inner().'dba/templates/');
		$c->set_cache_folder(PLIB_Path::inner().'cache/');
		
		// add some global variables
		$c->add_global('gpath',PLIB_Path::inner());
		$c->add_global('glibpath',PLIB_Path::lib());
		
		$js = PLIB_Javascript::get_instance();
		$js->set_cache_folder(PLIB_Path::inner().'cache/');
		$c->add_global_ref('gjs',$js);
		$c->add_global_ref('glocale',$this->locale);
		$c->add_global_ref('gurl',$this->url);
		
		// set callable methods
		$c->add_allowed_method('gjs','get_file');
		$c->add_allowed_method('glocale','lang');
		$c->add_allowed_method('gurl','get_url');
		
		return $c;
	}
	
	protected function _load_db()
	{
		include_once(PLIB_Path::inner().'config/mysql.php');
		$c = PLIB_MySQL::get_instance();
		$db = BS_DBA_Utils::get_instance()->get_selected_database();
		$c->connect(BS_MYSQL_HOST,BS_MYSQL_LOGIN,BS_MYSQL_PASSWORD,$db);
		$c->set_use_transactions(BS_USE_TRANSACTIONS);
		$c->init(BS_DB_CHARSET);
		$c->set_debugging_enabled(BS_DEBUG > 1);
		return $c;
	}
	
	protected function _load_msgs()
	{
		return new BS_Messages();
	}
	
	protected function _load_input()
	{
		$c = PLIB_Input::get_instance();
		return $c;
	}

	protected function _load_cookies()
	{
		return new PLIB_Cookies(BS_COOKIE_PREFIX);
	}

	protected function _load_functions()
	{
		return new BS_Functions();
	}

	protected function _load_url()
	{
		return BS_DBA_URL::get_instance();
	}
	
	protected function _load_locale()
	{
		return new BS_Locale();
	}
}
?>
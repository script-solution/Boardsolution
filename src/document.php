<?php
/**
 * The document-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The document
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Document extends PLIB_Document
{
	/**
	 * The template that should be displayed instead of the one of the module
	 *
	 * @var string
	 */
	protected $_template = '';
	
	public function __construct()
	{
		include_once(PLIB_Path::inner().'config/general.php');
		include_once(PLIB_Path::inner().'config/community.php');
		BS_Front_Action_Base::load_actions();
		
		parent::__construct();
		
		PLIB_Path::set_outer($this->cfg['board_url'].'/');
		
		// set our error-logger
		PLIB_Error_Handler::get_instance()->set_logger(new BS_Error_Logger());
		
		// load language
		$this->locale->add_language_file('index');
		
		// set global template-vars
		$this->tpl->add_global('gisloggedin',$this->user->is_loggedin());
		$this->tpl->add_global('gusername',$this->user->get_user_name());
		$this->tpl->add_global('guserid',$this->user->get_user_id());
		$this->tpl->add_global('gisadmin',$this->user->is_admin());
		$this->tpl->add_global('glang',$this->user->get_language());
		// TODO add theme
		// TODO add current module
		
		// run tasks
		$taskcon = new BS_Tasks_Container();
		$taskcon->run_tasks();
	}
	
	/**
	 * @return boolean wether the document is a standalone-document (we are in a standalone-module)
	 */
	public final function is_standalone()
	{
		return $this instanceof BS_Page_Standalone;
	}
	
	/**
	 * @return boolean wether the document is a acp-document (we are in ACP)
	 */
	public final function is_acp()
	{
		return $this instanceof BS_ACP_Document;
	}
	
	/**
	 * Sets the template that should be displayed instead of the one of the module
	 *
	 * @param string $tpl the template
	 */
	public final function set_base_template($tpl)
	{
		if(empty($tpl))
			PLIB_Helper::def_error('notempty','tpl',$tpl);
		
		$this->_template = $tpl;
	}
	
	protected function _load_action_perf()
	{
		return new BS_Front_Action_Performer();
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
		$list['tpl'] = array('locale','url','user');
		$list['sessions'] = array('cache');
		
		// add additional properties
		$list['functions'] = array();
		$list['auth'] = array();
		$list['unread'] = array();
		$list['forums'] = array();
		$list['cache'] = array();
		$list['cfg'] = array('cache');
		$list['ips'] = array();
		return $list;
	}
	
	protected function _get_init_dependency_list()
	{
		$list = parent::_get_init_dependency_list();
		
		// add additional properties
		$list['user'][] = 'cache';
		$list['auth'] = array('user','sessions');
		$list['unread'] = array('user');
		$list['cache'] = array();
		return $list;
	}
	
	/**
	 * Loads the auth property
	 *
	 * @return BS_Auth the property
	 */
	protected function _load_auth()
	{
		return new BS_Auth();
	}
	
	/**
	 * Loads the ips property
	 *
	 * @return BS_IPs the property
	 */
	protected function _load_ips()
	{
		return new BS_IPs();
	}
	
	/**
	 * Loads the unread property
	 *
	 * @return BS_Unread the property
	 */
	protected function _load_unread()
	{
		return new BS_Unread();
	}
	
	/**
	 * Loads the forums property
	 *
	 * @return BS_Forums_Manager the property
	 */
	protected function _load_forums()
	{
		return new BS_Forums_Manager();
	}
	
	/**
	 * Loads the cache property
	 *
	 * @return BS_Cache_Container the property
	 */
	protected function _load_cache()
	{
		return new BS_Cache_Container();
	}
	
	/**
	 * Loads the cfg property
	 *
	 * @return array the property
	 */
	protected function _load_cfg()
	{
		$cfg = $this->cache->get_cache('config')->get_elements_quick();
		return $cfg;
	}

	protected function _load_tpl()
	{
		$c = new PLIB_Template_Handler();
		$c->set_cache_folder(PLIB_Path::inner().'cache/');
		
		// add some global variables
		$c->add_global('gpath',PLIB_Path::inner());
		$c->add_global('glibpath',PLIB_Path::lib());
		
		$js = PLIB_Javascript::get_instance();
		$js->set_cache_folder(PLIB_Path::inner().'cache/');
		$c->add_global_ref('gjs',$js);
		$c->add_global_ref('glocale',$this->locale);
		$c->add_global_ref('gurl',$this->url);
		$c->add_global_ref('guser',$this->user);
		
		// set callable methods
		$c->add_allowed_method('gjs','get_file');
		$c->add_allowed_method('glocale','lang');
		$c->add_allowed_method('gurl','simple_url');
		$c->add_allowed_method('gurl','get_admin_url');
		$c->add_allowed_method('gurl','get_forums_url');
		$c->add_allowed_method('gurl','get_portal_url');
		$c->add_allowed_method('gurl','get_topics_url');
		$c->add_allowed_method('gurl','get_posts_url');
		$c->add_allowed_method('guser','get_theme_item_path');
		
		return $c;
	}
	
	protected function _load_sessions()
	{
		$c = new BS_Session_Manager();
		return $c;
	}

	protected function _load_user()
	{
		$storage = new BS_User_Storage_DB();
		$c = new BS_User_Current($storage);
		return $c;
	}
	
	protected function _load_db()
	{
		include_once(PLIB_Path::inner().'config/mysql.php');
		$c = PLIB_MySQL::get_instance();
		$c->connect(BS_MYSQL_HOST,BS_MYSQL_LOGIN,BS_MYSQL_PASSWORD,BS_MYSQL_DATABASE);
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
		
		// predefine values
		/*$c->set_predef(TDL_URL_ACTION,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_ORDER,'get',PLIB_Input::STRING,
			array('changed','type','title','project','start','fixed'));
		$c->set_predef(TDL_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'));
		$c->set_predef(TDL_URL_MODE,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_LOC,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_AT,'get',PLIB_Input::INTEGER);
		$c->set_predef(TDL_URL_ID,'get',PLIB_Input::ID);
		$c->set_predef(TDL_URL_IDS,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_SID,'get',PLIB_Input::ID);
		$c->set_predef(TDL_URL_SITE,'get',PLIB_Input::INTEGER);
		$c->set_predef(TDL_URL_LIMIT,'get',PLIB_Input::INTEGER);
		$c->set_predef(TDL_URL_S_KEYWORD,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_S_FROM_CHANGED_DATE,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_S_TO_CHANGED_DATE,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_S_FROM_START_DATE,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_S_TO_START_DATE,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_S_FROM_FIXED_DATE,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_S_TO_FIXED_DATE,'get',PLIB_Input::STRING);
		$c->set_predef(TDL_URL_S_TYPE,'get',PLIB_Input::STRING,
			array('','bug','feature','improvement','test'));
		$c->set_predef(TDL_URL_S_PRIORITY,'get',PLIB_Input::STRING,
			array('','current','next','anytime'));
		$c->set_predef(TDL_URL_S_STATUS,'get',PLIB_Input::STRING,
			array('','open','running','fixed','not_tested'));
		$c->set_predef(TDL_URL_S_CATEGORY,'get',PLIB_Input::ID);*/
		return $c;
	}

	protected function _load_cookies()
	{
		$c = new PLIB_Cookies(BS_COOKIE_PREFIX);
		$c->set_lifetime(BS_COOKIE_LIFETIME);
		return $c;
	}

	protected function _load_functions()
	{
		return new BS_Functions();
	}

	protected function _load_url()
	{
		return new BS_URL();
	}
	
	protected function _load_locale()
	{
		return new BS_Locale();
	}
}
?>
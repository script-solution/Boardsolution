<?php
/**
 * Contains the property-loader-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The property-loader for Boardsolution. We change and add some properties to the predefined
 * ones.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_PropLoader extends PLIB_PropLoader
{
	/**
	 * @return BS_Auth the property
	 */
	protected function auth()
	{
		return new BS_Auth();
	}
	
	/**
	 * @return BS_IPs the property
	 */
	protected function ips()
	{
		return new BS_IPs();
	}
	
	/**
	 * @return BS_Unread the property
	 */
	protected function unread()
	{
		return new BS_Unread();
	}
	
	/**
	 * @return BS_Forums_Manager the property
	 */
	protected function forums()
	{
		return new BS_Forums_Manager();
	}
	
	/**
	 * @return PLIB_Cache_Container the property
	 */
	protected function cache()
	{
		$storage = new PLIB_Cache_Storage_DB(BS_TB_CACHE,'table_name','table_content');
		$cache = new PLIB_Cache_Container($storage);
		
		// config
		$s = new BS_Cache_Source_Config();
		$cache->init_content('config',$s);
		
		$config = $cache->get_cache('config');
		if($config->get_element_count() == 0)
			PLIB_Helper::error('The Config-entries are missing',false);
		
		// stats
		$s = new BS_Cache_Source_Stats();
		$cache->init_content('stats',$s);
		
		// default ones without key
		$defs = array(
			'config' =>				BS_TB_DESIGN,
			'acp_access' =>		BS_TB_ACP_ACCESS,
		);
		foreach($defs as $name => $table)
		{
			$s = new PLIB_Cache_Source_SimpleDB($table,null,null);
			$cache->init_content($name,$s);
		}
		
		// default ones with id as key
		$defids = array(
			'banlist' =>			BS_TB_BANS,
			'themes' =>				BS_TB_THEMES,
			'languages' =>		BS_TB_LANGS,
			'bots' =>					BS_TB_BOTS,
			'intern' =>				BS_TB_INTERN,
			'user_groups' =>	BS_TB_USER_GROUPS,
			'tasks' =>				BS_TB_TASKS
		);
		foreach($defids as $name => $table)
		{
			$s = new PLIB_Cache_Source_SimpleDB($table);
			$cache->init_content($name,$s);
		}
		
		// user-fields
		$s = new PLIB_Cache_Source_SimpleDB(BS_TB_USER_FIELDS,'id','field_sort','ASC');
		$cache->init_content('user_fields',$s);
		
		// user_ranks
		$s = new PLIB_Cache_Source_SimpleDB(BS_TB_RANKS,'id','post_from','ASC');
		$cache->init_content('user_ranks',$s);
		
		// moderators
		$s = new BS_Cache_Source_CustomDB(
			"SELECT m.id,m.user_id,m.rid,u.`".BS_EXPORT_USER_NAME."` user_name
			 FROM ".BS_TB_MODS." m
 			 LEFT JOIN ".BS_TB_USER." u ON m.user_id = u.`".BS_EXPORT_USER_ID."`"
		);
		$cache->init_content('moderators',$s);
		
		return $cache;
	}
	
	/**
	 * @return array the property
	 */
	protected function cfg()
	{
		$cache = PLIB_Props::get()->cache();

		$cfg = $cache->get_cache('config')->get_elements_quick();
		return $cfg;
	}

	/**
	 * @return PLIB_Template_Handler the property
	 */
	protected function tpl()
	{
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();

		$c = new PLIB_Template_Handler();
		$c->set_cache_folder(PLIB_Path::server_app().'cache/');
		
		// add some global variables
		$c->add_global('gpath',PLIB_Path::client_app());
		$c->add_global('glibpath',PLIB_Path::client_lib());
		
		$js = PLIB_Javascript::get_instance();
		$js->set_cache_folder('cache/');
		$js->set_shrink(BS_DEBUG <= 1);
		$c->add_global_ref('gjs',$js);
		$c->add_global_ref('glocale',$locale);
		$c->add_global_ref('gurl',$url);
		$c->add_global_ref('guser',$user);
		
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
	
	/**
	 * @return BS_Session_Manager the property
	 */
	protected function sessions()
	{
		$c = new BS_Session_Manager();
		return $c;
	}
	
	/**
	 * @return BS_User_Current the property
	 */
	protected function user()
	{
		$storage = new BS_User_Storage_DB();
		$c = new BS_User_Current($storage);
		return $c;
	}
	
	/**
	 * @return PLIB_MySQL the property
	 */
	protected function db()
	{
		$c = PLIB_MySQL::get_instance();
		$c->connect(BS_MYSQL_HOST,BS_MYSQL_LOGIN,BS_MYSQL_PASSWORD,BS_MYSQL_DATABASE);
		$c->set_use_transactions(BS_USE_TRANSACTIONS);
		$c->init(BS_DB_CHARSET);
		$c->set_debugging_enabled(BS_DEBUG > 1);
		return $c;
	}
	
	/**
	 * @return BS_Messages the property
	 */
	protected function msgs()
	{
		return new BS_Messages();
	}
	
	/**
	 * @return PLIB_Input the property
	 */
	protected function input()
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

	/**
	 * @return PLIB_Cookies the property
	 */
	protected function cookies()
	{
		$c = new PLIB_Cookies(BS_COOKIE_PREFIX);
		$c->set_lifetime(BS_COOKIE_LIFETIME);
		return $c;
	}

	/**
	 * @return BS_Functions the property
	 */
	protected function functions()
	{
		return new BS_Functions();
	}

	/**
	 * @return BS_URL the property
	 */
	protected function url()
	{
		return new BS_URL();
	}
	
	/**
	 * @return BS_Locale the property
	 */
	protected function locale()
	{
		return new BS_Locale();
	}
}
?>
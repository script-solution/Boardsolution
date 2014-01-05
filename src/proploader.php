<?php
/**
 * Contains the property-loader-class
 * 
 * @package			Boardsolution
 * @subpackage	src
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * The property-loader for Boardsolution. We change and add some properties to the predefined
 * ones.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_PropLoader extends FWS_PropLoader
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
	 * @return FWS_Cache_Container the property
	 */
	protected function cache()
	{
		$storage = new FWS_Cache_Storage_DB(BS_TB_CACHE,'table_name','table_content');
		$cache = new FWS_Cache_Container($storage);
		
		// config
		$s = new BS_Cache_Source_Config();
		$cache->init_content('config',$s);
		
		$config = $cache->get_cache('config');
		if($config === null || $config->get_element_count() == 0)
			FWS_Helper::error('The Config-entries are missing. DB-Cache gone/broken?',false,E_USER_WARNING);
		
		// stats
		$s = new BS_Cache_Source_Stats();
		$cache->init_content('stats',$s);
		
		// default ones without key
		$defs = array(
			'acp_access' =>		BS_TB_ACP_ACCESS,
		);
		foreach($defs as $name => $table)
		{
			$s = new FWS_Cache_Source_SimpleDB($table,null,null);
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
			$s = new FWS_Cache_Source_SimpleDB($table);
			$cache->init_content($name,$s);
		}
		
		// user-fields
		$s = new FWS_Cache_Source_SimpleDB(BS_TB_USER_FIELDS,'id','field_sort','ASC');
		$cache->init_content('user_fields',$s);
		
		// user_ranks
		$s = new FWS_Cache_Source_SimpleDB(BS_TB_RANKS,'id','post_from','ASC');
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
		$cache = FWS_Props::get()->cache();

		$cfg = $cache->get_cache('config');
		if($cfg === null)
			return array();
		return $cfg->get_elements_quick();
	}

	/**
	 * @return FWS_Template_Handler the property
	 */
	protected function tpl()
	{
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();

		$c = new FWS_Template_Handler();
		$c->set_access_to_foreign_tpls(true);
		$c->set_cache_folder(FWS_Path::server_app().'cache/');
		
		// add some global variables
		$c->add_global('gpath',FWS_Path::client_app());
		$c->add_global('gfwspath',FWS_Path::client_fw());
		
		$js = FWS_Javascript::get_instance();
		$js->set_cache_folder('cache/');
		$js->set_shrink(BS_DEBUG <= 1);
		$c->add_global_ref('gjs',$js);
		$c->add_global_ref('glocale',$locale);
		$url = new BS_URL();
		$c->add_global_ref('gurl',$url);
		$c->add_global_ref('guser',$user);
		
		// set callable methods
		$c->add_allowed_method('gjs','get_file');
		$c->add_allowed_method('glocale','lang');
		$c->add_allowed_method('gurl','simple_url');
		$c->add_allowed_method('gurl','build_admin_url');
		$c->add_allowed_method('gurl','build_forums_url');
		$c->add_allowed_method('gurl','build_portal_url');
		$c->add_allowed_method('gurl','build_topics_url');
		$c->add_allowed_method('gurl','build_posts_url');
		$c->add_allowed_method('guser','get_theme_item_path');
		
		return $c;
	}
	
	/**
	 * @return BS_Session_Manager the property
	 */
	protected function sessions()
	{
		$c = new BS_Session_Manager();
		$c->set_online_timeout(BS_ONLINE_TIMEOUT);
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
	 * @return FWS_DB_MySQL_Connection the property
	 */
	protected function db()
	{
		$functions = FWS_Props::get()->functions();
		
		return $functions->connect_to_db(BS_MYSQL_HOST, BS_MYSQL_LOGIN, BS_MYSQL_PASSWORD, BS_MYSQL_DATABASE);
	}
	
	/**
	 * @return FWS_Input the property
	 */
	protected function input()
	{
		return FWS_Input::get_instance();
	}

	/**
	 * @return FWS_Cookies the property
	 */
	protected function cookies()
	{
		$c = new FWS_Cookies(BS_COOKIE_PREFIX);
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
	 * @return BS_Locale the property
	 */
	protected function locale()
	{
		return new BS_Locale();
	}
}
?>

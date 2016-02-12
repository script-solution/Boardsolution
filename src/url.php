<?php
/**
 * Contains the url-class
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
 * The URL-class for BS. Contains some additional methods for convenience.
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_URL extends FWS_URL
{
	/**
	 * Replaces $ABC with the value of BS_ABC.
	 *
	 * @param string $str the input value
	 * @return string the result
	 */
	private static function replace_constants($str)
	{
		if($str != '')
		{
			$str = preg_replace_callback(
				'/\$([a-z0-9_]+)/i',
				function($match) {
					return constant('BS_'.$match[1]);
				},
				$str
			);
		}
		return $str;
	}
	
	/**
	 * This method is intended for using it in templates.
	 * You can use the following shortcut for the constants (in <var>$additional</var>):
	 * <code>$<name></code>
	 * This will be mapped to the constant:
	 * <code><constants_prefix><name></code>
	 * Note that the constants will be assumed to be in uppercase!
	 * 
	 * @param string|int $module the module-name (0 = current, -1 = none)
	 * @param string $additional additional parameters
	 * @param string $separator the separator of the params (default is &amp;)
	 * @return string the url
	 */
	public static function simple_acp_url($module = 0,$additional = '',$separator = '&amp;')
	{
		$url = self::get_acpmod_url($module,$separator);
		self::_set_additional_params($url,$separator,self::replace_constants($additional));
		return $url->to_url();
	}
	
	/**
	 * This method is intended for using it in templates.
	 * You can use the following shortcut for the constants (in <var>$additional</var>):
	 * <code>$<name></code>
	 * This will be mapped to the constant:
	 * <code><constants_prefix><name></code>
	 * Note that the constants will be assumed to be in uppercase!
	 * 
	 * @param string|int $module the module-name (0 = current, -1 = none)
	 * @param string $additional additional parameters
	 * @param string $separator the separator of the params (default is &amp;)
	 * @param boolean $force_sid forces the method to append the session-id
	 * @return string the url
	 */
	public static function simple_url($module = 0,$additional = '',$separator = '&amp;',
		$force_sid = false)
	{
		$url = self::get_mod_url($module,$separator,$force_sid);
		self::_set_additional_params($url,$separator,self::replace_constants($additional));
		return $url->to_url();
	}
	
	/**
	 * Sets the given additional parameters to the given URL
	 *
	 * @param BS_URL $url the URL-instance
	 * @param string $separator the separator that should be used
	 * @param string $additional the additional parameters
	 */
	private static function _set_additional_params($url,$separator,$additional)
	{
		$params = FWS_Array_Utils::advanced_explode($separator,$additional);
		foreach($params as $p)
		{
			@list($k,$v) = explode('=',$p);
			$url->set($k,$v);
		}
	}
	
	/**
	 * Builds a standalone-URL for the given module. Will target the file standalone.php
	 *
	 * @param string|int $mod the module-name (0 = current, -1 = none)
	 * @param string $separator the separator of the params (default is &amp;)
	 * @return string the URL
	 */
	public static function build_standalone_url($mod = 0,$separator = '&amp;')
	{
		$url = self::get_standalone_url($mod,$separator);
		return $url->to_url();
	}
	
	/**
	 * Builds a standalone-URL for the given module. Will target the file standalone.php
	 *
	 * @param string|int $mod the module-name (0 = current, -1 = none)
	 * @param string $separator the separator of the params (default is &amp;)
	 * @return BS_URL the URL-instance
	 */
	public static function get_standalone_url($mod = 0,$separator = '&amp;')
	{
		$url = self::get_mod_url($mod,$separator);
		$url->set_path(BS_PATH);
		$url->set_file('standalone.php');
		return $url;
	}
	
	/**
	 * Builds an URL for the given module.
	 *
	 * @param string|int $mod the module-name (0 = current, -1 = none)
	 * @param string $separator the separator of the params (default is &amp;)
	 * @return string the url
	 */
	public static function build_mod_url($mod = 0,$separator = '&amp;')
	{
		$url = self::get_mod_url($mod,$separator);
		return $url->to_url();
	}
	
	/**
	 * Builds an URL-instance for the given module.
	 *
	 * @param string|int $mod the module-name (0 = current, -1 = none)
	 * @param string $separator the separator of the params (default is &amp;)
	 * @param boolean $force_sid forces the method to append the session-id
	 * @return BS_URL the url-instance
	 */
	public static function get_mod_url($mod = 0,$separator = '&amp;',$force_sid = false)
	{
		$url = new BS_URL();
		if($force_sid)
			$url->set_sid_policy(self::SID_FORCE);
		$url->set_separator($separator);

		if($mod === 0)
		{
			$input = FWS_Props::get()->input();
			$action = $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING);
			if($action != null)
				$url->set(BS_URL_ACTION,$action);
		}
		else if($mod !== -1)
			$url->set(BS_URL_ACTION,$mod);
		
		return $url;
	}
	
	/**
	 * Builds an URL which targets the given module and submodule
	 *
	 * @param string|int $module the name of the module (0 = current)
	 * @param string|int $sub the name of the submodule (0 = current)
	 * @param string $separator the separator of the params (default is &amp;)
	 * @return string the URL
	 */
	public static function build_sub_url($module = 0,$sub = 0,$separator = '&amp;')
	{
		$url = self::get_sub_url($module,$sub);
		$url->set_separator($separator);
		return $url->to_url();
	}
	
	/**
	 * Builds an instance of {@link BS_URL} which targets the given module and submodule
	 *
	 * @param string|int $module the name of the module (0 = current)
	 * @param string|int $sub the name of the submodule (0 = current)
	 * @return BS_URL the url-instance
	 */
	public static function get_sub_url($module = 0,$sub = 0)
	{
		$url = self::get_mod_url($module);
		
		if($sub === 0)
		{
			$input = FWS_Props::get()->input();
			$submod = $input->get_var(BS_URL_SUB,'get',FWS_INPUT::STRING);
			if($submod != null)
				$url->set(BS_URL_SUB,$submod);
		}
		else
			$url->set(BS_URL_SUB,$sub);
		
		return $url;
	}
	
	/**
	 * Builds an URL for a ACP-module
	 *
	 * @param string|int $module the module. (0 = current)
	 * @param string $separator the separator of the parameters (default = &amp;amp;)
	 * @return string the URL
	 */
	public static function build_acpmod_url($module = 0,$separator = '&amp;')
	{
		$url = self::get_acpmod_url($module,$separator);
		return $url->to_url();
	}
	
	/**
	 * Builds an URL for a ACP-module
	 *
	 * @param string|int $module the module. (0 = current)
	 * @param string $separator the separator of the parameters (default = &amp;amp;)
	 * @return BS_URL the URL-instance
	 */
	public static function get_acpmod_url($module = 0,$separator = '&amp;')
	{
		$url = new BS_URL();
		// we want to force the sid in the ACP
		$url->set_sid_policy(self::SID_FORCE);
		$url->set_file('admin.php');
		$url->set_separator($separator);
		
		// collect infos
		if($module === 0)
		{
			$input = FWS_Props::get()->input();
			$loc = $input->get_var('loc','get',FWS_Input::IDENTIFIER);
			if($loc != null)
				$url->set('loc',$loc);
		}
		else
			$url->set('loc',$module);

		$url->set('page','content');

		return $url;
	}
	
	/**
	 * Builds an URL for a ACP-submodule
	 *
	 * @param string|int $module the module. (0 = current)
	 * @param string|int $sub the submodule (0 = current)
	 * @param string $separator the separator of the parameters (default = &amp;amp;)
	 * @return string the URL
	 */
	public static function build_acpsub_url($module = 0,$sub = 0,$separator = '&amp;')
	{
		$url = self::get_acpsub_url($module,$sub,$separator);
		return $url->to_url();
	}
	
	/**
	 * Builds an URL-instance for a ACP-submodule
	 *
	 * @param string|int $module the module. (0 = current)
	 * @param string|int $sub the submodule (0 = current)
	 * @param string $separator the separator of the parameters (default = &amp;amp;)
	 * @return BS_URL the URL-instance
	 */
	public static function get_acpsub_url($module = 0,$sub = 0,$separator = '&amp;')
	{
		$url = self::get_acpmod_url($module,$separator);
		if($sub === 0)
		{
			$input = FWS_Props::get()->input();
			$sub = $input->get_var('action','get',FWS_Input::IDENTIFIER);
		}
		$url->set('action',$sub);
		return $url;
	}

	/**
	 * Builds an URL to the adminarea
	 * 
	 * @param string $separator the separator to use
	 * @return string the url to the admin-area
	 */
	public static function build_admin_url($separator = '&amp;')
	{
		$url = self::get_admin_url($separator);
		return $url->to_url();
	}

	/**
	 * Builds an URL to the adminarea
	 * 
	 * @param string $separator the separator to use
	 * @return BS_URL the URL-instance
	 */
	public static function get_admin_url($separator = '&amp;')
	{
		$url = new BS_URL();
		// we want to force the sid in the ACP
		$url->set_sid_policy(self::SID_FORCE);
		$url->set_path(FWS_Path::client_app());
		$url->set_file('admin.php');
		$url->set_separator($separator);
		return $url;
	}
	
	/**
	 * Generates an absolute URL to the frontend
	 * 
	 * @param string $module the module-name (null = no module)
	 * @param string $separator the separator to use
	 * @param boolean $use_sid append the sid if necessary?
	 * @return string the absolute URL
	 */
	public static function build_frontend_url($module = null,$separator = '&amp;',$use_sid = true)
	{
		$url = self::get_frontend_url($module,$separator,$use_sid);
		return $url->to_url();
	}
	
	/**
	 * Generates an absolute URL to the frontend
	 * 
	 * @param string $module the module-name (null = no module)
	 * @param string $separator the separator to use
	 * @param boolean $use_sid append the sid if necessary?
	 * @return BS_URL the URL-instance
	 */
	public static function get_frontend_url($module = null,$separator = '&amp;',$use_sid = true)
	{
		$url = new BS_URL();
		if(!$use_sid)
			$url->set_sid_policy(self::SID_OFF);
		$url->set_separator($separator);
		$url->set_absolute(true);
		
		// set file
		$pos = strpos(BS_FRONTEND_FILE,'?');
		$file = BS_FRONTEND_FILE;
		if($pos !== false)
		{
			$file = FWS_String::substr(BS_FRONTEND_FILE,0,$pos);
			$params = FWS_String::substr(BS_FRONTEND_FILE,$pos + 1);
			if($separator == '&amp;')
				$params = str_replace('&','&amp;',$params);
			self::_set_additional_params($url,$separator,$params);
		}
		$url->set_file($file);
		
		if($module !== null)
			$url->set(BS_URL_ACTION,$module);
		
		return $url;
	}
	
	/**
	 * Builds the start-url for the current user
	 * 
	 * @return string the URL
	 */
	public static function build_start_url()
	{
		$url = self::get_start_url();
		return $url->to_url();
	}
	
	/**
	 * Builds the start-url for the current user
	 * 
	 * @return BS_URL the URL-instance
	 */
	public static function get_start_url()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		if($cfg['enable_portal'] == 1 &&
			(!$user->is_loggedin() || $user->get_profile_val('startmodule') == 'portal'))
			return BS_URL::get_portal_url();
		
		return BS_URL::get_forums_url();
	}
	
	/**
	 * Builds the URL for the portal. May build a SEF-URL
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public static function build_portal_url($separator = '&amp;')
	{
		$url = self::get_portal_url($separator);
		$url->set_sef(true);
		return $url->to_url();
	}
	
	/**
	 * Builds the URL for the portal. Note that the method doesn't enable SEF!
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return BS_URL the URL-instance
	 */
	public static function get_portal_url($separator = '&amp;')
	{
		$url = new BS_URL();
		$url->set(BS_URL_ACTION,'portal');
		$url->set_separator($separator);
		return $url;
	}
	
	/**
	 * Builds the URL for the forum-index. May build a SEF-URL
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public static function build_forums_url($separator = '&amp;')
	{
		$url = self::get_forums_url($separator);
		$url->set_sef(true);
		return $url->to_url();
	}
	
	/**
	 * Builds the URL for the forum-index. Note that the method doesn't enable SEF!
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return BS_URL the URL-instance
	 */
	public static function get_forums_url($separator = '&amp;')
	{
		$url = new BS_URL();
		$url->set(BS_URL_ACTION,'forums');
		$url->set_separator($separator);
		return $url;
	}
	
	/**
	 * Builds the URL for the topics-view. May build a SEF-URL
	 * 
	 * @param int $fid the forum-id
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public static function build_topics_url($fid,$site = 0,$separator = '&amp;')
	{
		$url = self::get_topics_url($fid,$site,$separator);
		$url->set_sef(true);
		return $url->to_url();
	}
	
	/**
	 * Builds the URL for the topics-view. Note that the method doesn't enable SEF!
	 * 
	 * @param int $fid the forum-id
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @param string $separator the separator to use for the parameters
	 * @return BS_URL the URL-instance
	 */
	public static function get_topics_url($fid,$site = 0,$separator = '&amp;')
	{
		if($site === 0)
		{
			$input = FWS_Props::get()->input();
			$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);
			if($site === null)
				$site = 1;
		}
		
		$url = new BS_URL();
		$url->set(BS_URL_ACTION,'topics');
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_SITE,$site);
		$url->set_separator($separator);
		return $url;
	}
	
	/**
	 * Builds the URL for the posts-view. May build a SEF-URL
	 * 
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public static function build_posts_url($fid,$tid,$site = 0,$separator = '&amp;')
	{
		$url = self::get_posts_url($fid,$tid,$site,$separator);
		$url->set_sef(true);
		return $url->to_url();
	}
	
	/**
	 * Builds the URL-instance for the posts-view. Note that the method doesn't enable SEF!
	 * 
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @param string $separator the separator to use for the parameters
	 * @return BS_URL the URL-instance
	 */
	public static function get_posts_url($fid,$tid,$site = 0,$separator = '&amp;')
	{
		$input = FWS_Props::get()->input();

		if($site === 0)
		{
			$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);
			if($site === null)
				$site = 1;
		}
		
		$url = new BS_URL();
		$url->set_separator($separator);
		$url->set(BS_URL_ACTION,'posts');
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		$url->set(BS_URL_SITE,$site);
		return $url;
	}
	
	/**
	 * Builds the components for the URL of the current module in the ACP. This may be
	 * usefull for example if you want to use a formular via GET and therefore have to
	 * know all GET-parameters
	 *
	 * @return array an associative array with all components
	 */
	public static function get_acpmod_comps()
	{
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();

		return array(
			'page' => 'content',
			BS_URL_SID => $user->get_session_id(),
			'loc' => $input->get_var('loc','get',FWS_Input::IDENTIFIER)
		);
	}
	
	/**
	 * Wether a SEF (search-engine-friendly) URL should be build
	 *
	 * @var boolean
	 */
	private $_sef = false;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		// no SID for bots
		$user = FWS_Props::get()->user();
		if($user instanceof BS_User_Current && $user->is_bot())
			$this->set_sid_policy(self::SID_OFF);
		// by default we use the frontend-file
		$this->set_file(strtok(BS_FRONTEND_FILE,'?'));
	}
	
	/**
	 * @return boolean wether a SEF (search-engine-friendly) URL will be build
	 */
	public function is_sef()
	{
		return $this->_sef;
	}
	
	/**
	 * Sets wether a SEF (search-engine-friendly) URL should be build. If enabled the method
	 * <var>to_url()</var> will build the URL depending on the parameter
	 * <var>BS_URL_ACTION</var>. Currently this is supported for the values 'portal','forums',
	 * 'topics' and 'posts'. The first two require no additional parameters. The value
	 * 'topics' requires the forum-id and 'posts' requires the forum- and topic-id.
	 * <br>
	 * Note that other parameters that may have been set will be ignored!
	 *
	 * @param boolean $sef the new value
	 */
	public function set_sef($sef)
	{
		$this->_sef = $sef ? true : false;
	}

	/**
	 * @see FWS_URL::is_intern($param)
	 *
	 * @param string $param
	 * @return boolean
	 */
	public function is_intern($param)
	{
		static $params = null;
		if($params === null)
		{
			$params = array(
				BS_URL_ACTION,BS_URL_FID,BS_URL_TID,BS_URL_PID,BS_URL_LOC,BS_URL_MODE,BS_URL_ID,BS_URL_SITE,
				BS_URL_ORDER,BS_URL_AD,BS_URL_LIMIT,BS_URL_DEL,BS_URL_HL,BS_URL_DAY,BS_URL_WEEK,BS_URL_MONTH,
				BS_URL_YEAR,BS_URL_KW,BS_URL_AT,BS_URL_MS_NAME,BS_URL_MS_EMAIL,BS_URL_MS_GROUP,
				BS_URL_MS_FROM_POSTS,BS_URL_MS_TO_POSTS,BS_URL_MS_FROM_POINTS,BS_URL_MS_TO_POINTS,
				BS_URL_MS_FROM_REG,BS_URL_MS_TO_REG,BS_URL_MS_FROM_LASTLOGIN,BS_URL_MS_TO_LASTLOGIN,BS_URL_SID,
				BS_URL_MS_MODS,BS_URL_UN,BS_URL_SUB,BS_URL_SEARCH_MODE,BS_URL_CURRENT
			);
		}

		return in_array($param,$params);
	}

	/**
	 * @see FWS_URL::to_url()
	 *
	 * @return string
	 */
	public function to_url()
	{
		if(!$this->_sef)
			return parent::to_url();
		
		$cfg = FWS_Props::get()->cfg();
		if(!$cfg['enable_modrewrite'])
			return parent::to_url();
		
		$action = $this->get(BS_URL_ACTION);
		switch($action)
		{
			case 'portal':
				return $this->_get_sef_url('portal');
			
			case 'forums':
				return $this->_get_sef_url('forums');
			
			case 'posts':
			case 'topics':
				$site = $this->get(BS_URL_SITE);
				if($site === null)
				{
					$input = FWS_Props::get()->input();
					$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);
					if($site === null)
						$site = 1;
				}
				$fid = $this->get(BS_URL_FID);
				if($action == 'topics')
					return $this->_get_sef_url('topics_'.$fid.'_'.$site);
				
				$tid = $this->get(BS_URL_TID);
				return $this->_get_sef_url('posts_'.$fid.'_'.$tid.'_'.$site);
		}
		
		FWS_Helper::error('Unable to generate a SEF-url from this URL!');
		return '';
	}
	
	/**
	 * Builds the following mod-rewrite-URL:
	 * <code>FWS_Path::outer()$prefix$sid.html</code>
	 *
	 * @param string $prefix the prefix
	 * @return string the URL
	 */
	private function _get_sef_url($prefix)
	{
		$sidpolicy = $this->get_sid_policy();
		$sid = false;
		if($sidpolicy == self::SID_FORCE)
			$sid = $this->get_session_param(true);
		else if($sidpolicy != self::SID_OFF)
			$sid = self::get_session_id();
		
		$url = FWS_Path::outer().$prefix;
		if($sid !== false)
			$url .= '_s'.$sid[1];
		$url .= '.html';
		if($this->get_anchor() !== null)
			$url .= '#'.$this->get_anchor();
		
		return $url;
	}
}
?>

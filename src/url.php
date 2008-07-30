<?php
/**
 * Contains the url-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * Works the same like get_url but is mainly intended for usage in the templates.
	 * You can use the following shortcut for the constants (in <var>$additional</var>):
	 * <code>$<name></code>
	 * This will be mapped to the constant:
	 * <code><constants_prefix><name></code>
	 * Note that the constants will be assumed to be in uppercase!
	 * 
	 * @param string $target the action-parameter (0 = current, -1 = none)
	 * @param string $additional additional parameters
	 * @param string $separator the separator of the params (default is &amp;)
	 * @param boolean $force_sid forces the method to append the session-id
	 * @return string the url
	 */
	public static function simple_url($target = 0,$additional = '',$separator = '&amp;',
		$force_sid = false)
	{
		if($additional != '')
			$additional = preg_replace('/\$([a-z0-9_]+)/ie','BS_\\1',$additional);
		return self::get_url($target,$additional,$separator,$force_sid);
	}
	
	/**
	 * The default method. This generates an URL with given parameters and returns it.
	 * The extern-variables (if you want it) and the session-id (if necessary)
	 * will be appended.
	 * The file will be <var>$_SERVER['PHP_SELF']</var>.
	 *
	 * @param string $target the action-parameter (0 = current, -1 = none)
	 * @param string $additional additional parameters
	 * @param string $seperator the separator of the params (default is &amp;)
	 * @param boolean $force_sid forces the method to append the session-id
	 * @return string the url
	 */
	public static function get_url($target = 0,$additional = '',$separator = '&amp;',$force_sid = false)
	{
		$url = new BS_URL();
		if($force_sid)
			$url->set_sid_policy(self::SID_FORCE);
		$url->set_separator($separator);

		if($target === 0)
		{
			$input = FWS_Props::get()->input();
			$action = $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING);
			if($action != null)
				$url->set(BS_URL_ACTION,$action);
		}
		else
			$url->set(BS_URL_ACTION,$target);
		
		self::_set_additional_params($url,$separator,$additional);
		
		return $url->to_url();
	}
	
	/**
	 * Builds an URL for a ACP-module
	 *
	 * @param mixed $module the module. (0 = current)
	 * @param string $additional additional parameters, starting with $separator
	 * @param string $separator the separator of the parameters (default = &amp;amp;)
	 * @return string the URL
	 */
	public static function get_acpmod_url($module = 0,$additional = '',$separator = '&amp;')
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
		self::_set_additional_params($url,$separator,$additional);

		return $url->to_url();
	}

	/**
	 * Builds an URL to the adminarea
	 * 
	 * @param string $additional additional parameters
	 * @param string $separator the separator to use
	 * @return string the url to the admin-area
	 */
	public static function get_admin_url($additional = '',$separator = '&amp;')
	{
		$url = new BS_URL();
		// we want to force the sid in the ACP
		$url->set_sid_policy(self::SID_FORCE);
		$url->set_path(FWS_Path::client_app());
		$url->set_file('admin.php');
		$url->set_separator($separator);
		self::_set_additional_params($url,$separator,$additional);
		return $url->to_url();
	}
	
	/**
	 * Generates an absolute URL to the frontend
	 * 
	 * @param string $additional additional parameters you would like to add
	 * @param string $separator the separator to use
	 * @param boolean $use_sid append the sid if necessary?
	 * @return string the absolute URL
	 */
	public static function get_frontend_url($additional = '',$separator = '&amp;',$use_sid = true)
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
		
		// add additional params
		self::_set_additional_params($url,$separator,$additional);
		
		return $url->to_url();
	}
	
	/**
	 * Builds an URL for the given file (for example for the inc-directory)
	 * Note that this method does NOT append the external vars. Therefore you should
	 * always create a link to a standalone-file!
	 * 
	 * @param string $file the file (starting at FWS_Path::client_app())
	 * @param string $additional additional parameters
	 * @param string $separator the separator for the parameters (default = &amp;)
	 * @param boolean $absolute use FWS_Path::outer() (=absolute) or FWS_Path::client_app()?
	 * @return string the url
	 */
	public static function get_file_url($file,$additional = '',$separator = '&amp;',$absolute = false)
	{
		$url = new BS_URL();
		$url->set_file($file);
		$url->set_absolute($absolute);
		$url->set_separator($separator);
		self::_set_additional_params($url,$separator,$additional);
		
		return $url->to_url();
	}
	
	/**
	 * Builds the URL for the portal. May build a mod-rewrite-URL
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public static function get_portal_url($separator = '&amp;')
	{
		$cfg = FWS_Props::get()->cfg();
		
		if($cfg['enable_modrewrite'])
			return self::_get_modrewrite_url('portal');
		
		$url = new BS_URL();
		$url->set(BS_URL_ACTION,'portal');
		$url->set_absolute(true);
		$url->set_separator($separator);
		return $url->to_url();
	}
	
	/**
	 * Builds the URL for the forum-index. May build a mod-rewrite-URL
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public static function get_forums_url($separator = '&amp;')
	{
		$cfg = FWS_Props::get()->cfg();

		if($cfg['enable_modrewrite'])
			return self::_get_modrewrite_url('forums');
		
		$url = new BS_URL();
		$url->set(BS_URL_ACTION,'forums');
		$url->set_absolute(true);
		$url->set_separator($separator);
		return $url->to_url();
	}
	
	/**
	 * Builds the URL for the topics-view. May build a mod-rewrite-URL
	 * 
	 * @param int $fid the forum-id
	 * @param string $separator the separator to use for the parameters
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @return string the URL
	 */
	public static function get_topics_url($fid,$separator = '&amp;',$site = 0)
	{
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();

		if($site === 0)
		{
			$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);
			if($site === null)
				$site = 1;
		}
		
		if($cfg['enable_modrewrite'])
			return self::_get_modrewrite_url('topics_'.$fid.'_'.$site);
		
		$url = new BS_URL();
		$url->set(BS_URL_ACTION,'topics');
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_SITE,$site);
		$url->set_absolute(true);
		$url->set_separator($separator);
		return $url->to_url();
	}
	
	/**
	 * Builds the URL for the posts-view. May build a mod-rewrite-URL
	 * 
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param string $separator the separator to use for the parameters
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @return string the URL
	 */
	public static function get_posts_url($fid,$tid,$separator = '&amp;',$site = 0)
	{
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();

		if($site === 0)
		{
			$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::ID);
			if($site === null)
				$site = 1;
		}
		
		if($cfg['enable_modrewrite'])
			return self::_get_modrewrite_url('posts_'.$fid.'_'.$tid.'_'.$site);
		
		$url = new BS_URL();
		$url->set(BS_URL_ACTION,'posts');
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		$url->set(BS_URL_SITE,$site);
		$url->set_absolute(true);
		$url->set_separator($separator);
		return $url->to_url();
	}
	
	/**
	 * Builds the following mod-rewrite-URL:
	 * <code>FWS_Path::outer()$prefix$sid.html</code>
	 *
	 * @param string $prefix the prefix
	 * @return string the URL
	 */
	private static function _get_modrewrite_url($prefix)
	{
		static $sessionid_add = null;
		if($sessionid_add === null)
		{
			if(self::needs_session_id())
			{
				$user = FWS_Props::get()->user();
				$sessionid_add = '_s'.$user->get_session_id();
			}
			else
				$sessionid_add = '';
		}
		
		return FWS_Path::outer().$prefix.$sessionid_add.'.html';
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
			// urldecode the value because we will encode it anyway and we assume that it is already
			// encoded, if necessary. Otherwise we would get problems with splitting by &
			$url->set($k,urldecode($v));
		}
	}
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::set_append_extern_vars(true);
		
		parent::__construct();
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
				BS_URL_MS_MODS,BS_URL_UN
			);
		}

		return in_array($param,$params);
	}
}
?>
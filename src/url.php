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
final class BS_URL extends PLIB_URL
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->set_action_param(BS_URL_ACTION);
		$this->set_append_extern_vars(true);
		$this->set_constants_prefix('BS_');
	}

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
				BS_URL_MS_MODS
			);
		}

		return in_array($param,$params);
	}
	
	/**
	 * Builds a standalone-URL for the given type
	 *
	 * @param string $type the type: acp or front
	 * @param string $module the module-name
	 * @param string $additional additional parameters starting with <var>$separator</var>
	 * @param string $separator the separator of the links
	 * @param boolean $absolute use PLIB_Path::outer() (=absolute) or PLIB_Path::inner()?
	 * @return string the generated URL
	 */
	public function get_standalone_url($type,$module,$additional = '',$separator = '&amp;',
		$absolute = false)
	{
		return $this->get_file_url(
			'standalone.php',
			$separator.BS_URL_PAGE.'='.$type.$separator.BS_URL_ACTION.'='.$module.$additional,
			$separator,
			$absolute
		);
	}
	
	/**
	 * Builds the components for the URL of the current module in the ACP. This may be
	 * usefull for example if you want to use a formular via GET and therefore have to
	 * know all GET-parameters
	 *
	 * @return array an associative array with all components
	 */
	public function get_acpmod_comps()
	{
		return array(
			'page' => 'content',
			BS_URL_SID => $this->user->get_session_id(),
			'loc' => $this->input->get_var('loc','get',PLIB_Input::IDENTIFIER)
		);
	}
	
	/**
	 * Builds an URL for a ACP-module
	 *
	 * @param mixed $module the module. (0 = current)
	 * @param string $additional additional parameters, starting with $separator
	 * @param string $separator the separator of the parameters (default = &amp;amp;)
	 * @return string the URL
	 */
	public function get_acpmod_url($module = 0,$additional = '',$separator = '&amp;')
	{
		$this->_init();

		// collect infos
		if($module === 0)
		{
			$action_param = $this->input->get_var('loc','get',PLIB_Input::IDENTIFIER);
			if($action_param == null)
				$action = '';
			else
				$action = 'loc='.$action_param;
		}
		else
			$action = 'loc='.$module;

		$session_id = '&amp;'.BS_URL_SID.'='.$this->user->get_session_id();
		
		// build parameters
		$parameters = $action;
		if($separator == '&')
			$parameters .= str_replace('&amp;','&',$session_id);
		else
			$parameters .= $session_id;
		$parameters .= $separator.'page=content'.$additional;

		// build link
		$php_self = 'admin.php';
		if($parameters == '')
			$url = $php_self;
		else if($separator == '&' && $parameters[0] == $separator)
			$url = $php_self.'?'.PLIB_String::substr($parameters,1);
		else if($separator == '&amp;' && PLIB_String::substr($parameters,0,5) == '&amp;')
			$url = $php_self.'?'.PLIB_String::substr($parameters,5);
		else
			$url = $php_self.'?'.$parameters;

		return $url;
	}

	/**
	 * @param string $additional additional parameters
	 * @param string $separator the separator to use
	 * @return string the url to the admin-area
	 */
	public function get_admin_url($additional = '',$separator = '&amp;')
	{
		$this->_init();
		
		// append sid in all cases
		if($this->_session_id != '')
			$sid = str_replace('&amp;',$separator,$this->_session_id);
		else
			$sid = $separator.BS_URL_SID.'='.$this->user->get_session_id();
		
		if($additional == '')
			return PLIB_Path::inner().'admin.php?'.PLIB_String::substr($sid,5);

		return PLIB_Path::inner().'admin.php?'.$additional.$sid;
	}
	
	/**
	 * Generates an absolute URL to the frontend
	 * 
	 * @param string $additional additional parameters you would like to add
	 * @param string $separator the separator to use
	 * @param boolean $use_sid append the sid if necessary?
	 * @return string the absolute URL
	 */
	public function get_frontend_url($additional = '',$separator = '&amp;',$use_sid = true)
	{
		$this->_init();
		
		$file = $this->functions->get_board_file(false);
		if($separator == '&')
			$file = str_replace('&amp;','&',$file);
		
		if(!$use_sid)
		{
			$old_sid = $this->_session_id;
			$this->_session_id = '';
		}
		
		$url = $this->get_file_url($file,$additional,$separator,true);
		
		if(!$use_sid)
			$this->_session_id = $old_sid;
		
		return $url;
	}
	
	/**
	 * Builds an URL for the given file (for example for the inc-directory)
	 * Note that this method does NOT append the external vars. Therefore you should
	 * always create a link to a standalone-file!
	 * 
	 * @param string $file the file (starting at PLIB_Path::inner())
	 * @param string $additional additional parameters
	 * @param string $separator the separator for the parameters (default = &amp;)
	 * @param boolean $absolute use PLIB_Path::outer() (=absolute) or PLIB_Path::inner()?
	 * @return string the url
	 */
	public function get_file_url($file,$additional = '',$separator = '&amp;',$absolute = false)
	{
		$this->_init();
		
		// Note that we don't append the external vars here because this leads always to
		// a standalone file!
		if($separator == '&')
			$parameters = str_replace('&amp;','&',$this->_session_id);
		else
			$parameters = $this->_session_id;
		$parameters .= $additional;
		
		$first_sep = PLIB_String::strpos($file,'?') !== false ? $separator : '?';
		$base = $absolute ? PLIB_Path::outer() : PLIB_Path::inner();
		if($parameters == '')
			$url = $base.$file;
		else if($separator == '&' && PLIB_String::substr($parameters,0,1) == $separator)
			$url = $base.$file.$first_sep.PLIB_String::substr($parameters,1);
		else if($separator == '&amp;' && PLIB_String::substr($parameters,0,5) == '&amp;')
			$url = $base.$file.$first_sep.PLIB_String::substr($parameters,5);
		else
			$url = $base.$file.$first_sep.$parameters;

		return $url;
	}
	
	/**
	 * builds the URL for the portal
	 * may build a mod-rewrite-URL
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public function get_portal_url($separator = '&amp;')
	{
		if($this->cfg['enable_modrewrite'])
		{
			static $sessionid_add = null;
			if($sessionid_add === null)
			{
				if($this->use_session_id())
					$sessionid_add = '_s'.$this->user->get_session_id();
				else
					$sessionid_add = '';
			}
			
			return PLIB_Path::outer().'portal'.$sessionid_add.'.html';
		}
		
		return $this->get_url('portal','',$separator);
	}
	
	/**
	 * builds the URL for the forum-index
	 * may build a mod-rewrite-URL
	 * 
	 * @param string $separator the separator to use for the parameters
	 * @return string the URL
	 */
	public function get_forums_url($separator = '&amp;')
	{
		if($this->cfg['enable_modrewrite'])
		{
			static $sessionid_add = null;
			if($sessionid_add === null)
			{
				if($this->use_session_id())
					$sessionid_add = '_s'.$this->user->get_session_id();
				else
					$sessionid_add = '';
			}
			
			return PLIB_Path::outer().'forums'.$sessionid_add.'.html';
		}
		
		return $this->get_url('forums','',$separator);
	}
	
	/**
	 * builds the URL for the topics-view
	 * may build a mod-rewrite-URL
	 * 
	 * @param int $fid the forum-id
	 * @param string $separator the separator to use for the parameters
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @return string the URL
	 */
	public function get_topics_url($fid,$separator = '&amp;',$site = 0)
	{
		if($site === 0)
		{
			$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::ID);
			if($site === null)
				$site = 1;
		}
		
		if($this->cfg['enable_modrewrite'])
		{
			static $sessionid_add = null;
			if($sessionid_add === null)
			{
				if($this->use_session_id())
					$sessionid_add = '_s'.$this->user->get_session_id();
				else
					$sessionid_add = '';
			}
			
			return PLIB_Path::outer().'topics_'.$fid.'_'.$site.$sessionid_add.'.html';
		}
		
		return $this->get_url(
			'topics',$separator.BS_URL_FID.'='.$fid.$separator.BS_URL_SITE.'='.$site,$separator
		);
	}
	
	/**
	 * builds the URL for the posts-view
	 * may build a mod-rewrite-URL
	 * 
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param string $separator the separator to use for the parameters
	 * @param int $site you can specify the BS_URL_SITE parameter-value, if you like
	 * @return string the URL
	 */
	public function get_posts_url($fid,$tid,$separator = '&amp;',$site = 0)
	{
		if($site === 0)
		{
			$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::ID);
			if($site === null)
				$site = 1;
		}
		
		if($this->cfg['enable_modrewrite'])
		{
			static $sessionid_add = null;
			if($sessionid_add === null)
			{
				if($this->use_session_id())
					$sessionid_add = '_s'.$this->user->get_session_id();
				else
					$sessionid_add = '';
			}
			
			return PLIB_Path::outer().'posts_'.$fid.'_'.$tid.'_'.$site.$sessionid_add.'.html';
		}
		
		return $this->get_url(
			'posts',
			'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.'&amp;'.BS_URL_SITE.'='.$site,
			$separator
		);
	}

	/**
	 * @return array an array of the form <code>array(<param_name>,<session_id>)</code>
	 */
	public function get_splitted_session_id()
	{
		$this->_init();
		
		if($this->_session_id != '')
			return array(BS_URL_SID,$this->user->get_session_id());

		return 0;
	}
}
?>
<?php
/**
 * Contains the helper-class for step3
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Helper-methods for the step3
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_3_Helper extends FWS_UtilBase
{
	/**
	 * Collects all values to check
	 *
	 * @return array the values
	 */
	public static function collect_vals()
	{
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$db = FWS_Props::get()->db();
		
		$host = $user->get_session_data('host','');
		$login = $user->get_session_data('login','');
		$password = $user->get_session_data('password','');
		$database = $user->get_session_data('database','');
		$install_type = $user->get_session_data('install_type','full');
		$admin_login = $user->get_session_data('admin_login','admin');
		$admin_pw = $user->get_session_data('admin_pw','admin');
		$admin_email = $user->get_session_data('admin_email','');
		$def_board_url = FWS_Path::outer();
		$board_url = $user->get_session_data('board_url',
			FWS_FileUtils::ensure_no_trailing_slash($def_board_url));

		$check = array();
		$check['php'] = phpversion();
		$mysql_version = $db->get_server_version();
		if(!$mysql_version)
			$mysql_version = mysql_get_client_info();
		$check['mysql'] = $mysql_version;
		$check['gd'] = FWS_PHPConfig::get_gd_version();
		$check['chmod_cache'] = FWS_FileUtils::is_writable('cache');
		$check['chmod_config'] = FWS_FileUtils::is_writable('config');
		$check['chmod_dba'] = FWS_FileUtils::is_writable('dba');
		$check['chmod_uploads'] = FWS_FileUtils::is_writable('uploads');
		$check['chmod_dbabackups'] = FWS_FileUtils::is_writable('dba/backups');
		$check['chmod_smileys'] = FWS_FileUtils::is_writable('images/smileys');
		$check['chmod_avatars'] = FWS_FileUtils::is_writable('images/avatars');
		
		$check['mysql_connect'] = 0;
		$check['mysql_select_db'] = 0;
	
		if($host != '' && $login != '' && $database != '')
		{
			$check['mysql_connect'] = mysql_connect($host,$login,$password);
			$check['mysql_select_db'] = mysql_select_db($database) ? 1 : 0;
			if($check['mysql_connect'])
				mysql_close($check['mysql_connect']);
		}
		
		if($install_type == 'full')
		{
			$check['admin_login'] = $admin_login != '';
			$check['admin_pw'] = $admin_pw != '';
			$check['admin_email'] = FWS_StringHelper::is_valid_email($admin_email);
		}
		
		$check['board_url'] = FWS_String::substr($board_url,0,7) == "http://" &&
			$board_url[FWS_String::strlen($board_url) - 1] != '/';
		
		$check['chmod_themes'] = self::_check_chmod_themes();
		
		return $check;
	}
	
	/**
	 * Checks all given values and returns an array with all error-messages
	 *
	 * @param array $values all values to check
	 * @param array $status will contain the status for all values (0 = failed)
	 * @return array errors
	 */
	public static function check($values,&$status)
	{
		$locale = FWS_Props::get()->locale();
		
		// any errors?
		$errors = array();
		foreach($values as $key => $value)
		{
			if($key != 'gd' && $key != 'chmod_themes' && !$value)
				$errors[] = $locale->lang('error_'.$key);
			$status[$key] = $value;
		}
		
		$status['gd'] = FWS_PHPConfig::is_gd2_installed();
		
		if($values['php'] < '5.2.0')
		{
			$errors[] = $locale->lang('error_php');
			$status['php'] = false;
		}
		if($values['mysql'] < 3)
		{
			$errors[] = $locale->lang('error_mysql');
			$status['mysql'] = false;
		}
		
		// special case
		if($values['chmod_themes'] > 0)
			$errors[] = $locale->lang('error_chmod_themes_'.$values['chmod_themes']);
		$status['chmod_themes'] = $values['chmod_themes'] == 0;
		
		return $errors;
	}
	
	/**
	 * checks the CHMOD-attributes of all templates and the style.css in all themes
	 * 
	 * @return int 0 if successfull or the error-code
	 * 
	 * 	error-codes:
	 * 		1 => themes/default/style.css,
	 * 		2 => a template in themes/default/templates
	 */
	private static function _check_chmod_themes()
	{
		// check all themes
		foreach(FWS_FileUtils::get_dir_content('themes',false,false) as $theme)
		{
			if(is_dir('themes/'.$theme) && $theme[0] != '.')
			{
				// check css-file
				if(!FWS_FileUtils::is_writable('themes/'.$theme.'/basic.css'))
					return 1;
				
				// check templates
				foreach(FWS_FileUtils::get_dir_content('themes/'.$theme.'/templates',false,false) as $tpl)
				{
					if(preg_match('/\.htm$/',$tpl) && $tpl != 'index.htm' &&
							!FWS_FileUtils::is_writable('themes/'.$theme.'/templates/'.$tpl))
						return 2;
				}
			}
		}
		
		return 0;
	}
}
?>
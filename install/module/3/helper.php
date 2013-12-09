<?php
/**
 * Contains the helper-class for step3
 * 
 * @package			Boardsolution
 * @subpackage	install.module
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
			$mysql_version = $db->get_client_version();
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
			$check['mysql_connect'] = @mysql_connect($host,$login,
				stripslashes(html_entity_decode($password, ENT_QUOTES, BS_HTML_CHARSET)));
			$check['mysql_select_db'] = @mysql_select_db($database) ? 1 : 0;
			if($check['mysql_connect'])
				mysql_close($check['mysql_connect']);
		}
		
		if($install_type == 'full')
		{
			$check['admin_login'] = $admin_login != '';
			$check['admin_pw'] = $admin_pw != '';
			$check['admin_email'] = FWS_StringHelper::is_valid_email($admin_email);
		}
		
		$check['board_url'] = self::check_and_change_url($board_url);
		
		return $check;
	}

	/**
	 * Checks the board-url and deletes the end-slash
	 *
	 * @param string $board_url the board-url to check / change
	 * @return boolean true if valid
	 */
	private static function check_and_change_url($board_url)
	{
		$user = FWS_Props::get()->user();
		
		$url = FWS_FileUtils::ensure_no_trailing_slash($board_url);		
		$user->set_session_data('board_url', $url);
		
		return preg_match('/^(http|https):\/\//i', $url);
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
		
		return $errors;
	}
}
?>
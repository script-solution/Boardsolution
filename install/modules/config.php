<?php
/**
 * Contains the config module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The config-module
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_Install_config extends BS_Install
{
	public function run()
	{
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();

		// validate values
		$gd_installed = extension_loaded('gd') && function_exists('imagecreate');
		
		$tpl->set_template('step_config.htm');
		
		$configs = array();
		
		$configs[] = $functions->get_config_status(
			'PHP-Version:',$this->_check['php'] >= '4.1.0',0,0,$this->_check['php']
		);
		$configs[] = $functions->get_config_status(
			'MySQL-Version:',$this->_check['mysql'] >= 3,0,0,$this->_check['mysql']
		);
		$configs[] = $functions->get_config_status(
			'GD-Library:',$gd_installed,$locale->lang("available"),$locale->lang("notavailable"),0,
			$locale->lang('gd_description'),'warning'
		);
		
		$configs[] = array('type' => 'separator');
		
		$configs[] = $functions->get_config_status(
			'cache/:',$this->_check['chmod_cache'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'config/:',$this->_check['chmod_config'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'dbbackup/:',$this->_check['chmod_dbaaccess'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'uploads/:',$this->_check['chmod_uploads'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'dbbackup/backups/:',$this->_check['chmod_dbbackup'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'images/smileys/:',$this->_check['chmod_smileys'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'images/avatars/:',$this->_check['chmod_avatars'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'config/community.php:',$this->_check['chmod_config_community'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'config/userdef.php:',$this->_check['chmod_config_userdef'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		$configs[] = $functions->get_config_status(
			'themes/ *:',$this->_check['chmod_themes']['success'],$locale->lang('writable'),$locale->lang('notwritable')
		);
		
		$configs[] = array('type' => 'separator');
		
	 	$configs[] = $functions->get_config_input(
			"MySQL - Host:","host",$this->_check['mysql_connect'],"",40,40
		);
		$configs[] = $functions->get_config_input(
			"MySQL - Login:","login",$this->_check['mysql_connect'],"",40,40
		);
		$configs[] = $functions->get_config_input(
			"MySQL - ".$locale->lang("password").":","password",$this->_check['mysql_connect'],"",40,40
		);
		$configs[] = $functions->get_config_input(
			"MySQL - ".$locale->lang("database").":","database",$this->_check['mysql_select_db'],"",40,40
		);
		
		$configs[] = array('type' => 'separator');
		
		if($functions->get_session_var('install_type') == 'full')
		{
			$configs[] = $functions->get_config_input(
				$locale->lang("admin_login").":",'admin_login',$this->_check['admin_login']
			);
			$configs[] = $functions->get_config_input(
				$locale->lang("admin_pw").":",'admin_pw',$this->_check['admin_pw']
			);
			$configs[] = $functions->get_config_input(
				$locale->lang("admin_email").":",'admin_email',$this->_check['admin_email'],'',30,255
			);
		}
		
		$configs[] = $functions->get_config_input(
			$locale->lang('board_url').':','board_url',$this->_check['board_url'],'',40,255,
			$locale->lang('board_url_desc')
		);
		
		$tpl->add_variables(array(
			'show_table_prefix' => false,
			'title' => $locale->lang('step_config')
		));
		$tpl->add_array('configs',$configs);
		echo $tpl->parse_template();
	}
	
	public function check_inputs(&$check)
	{
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();

		$check['php'] = phpversion();
		$mysql_version = @mysql_get_server_info();
		if(!$mysql_version)
			$mysql_version = mysql_get_client_info();
		$check['mysql'] = $mysql_version;
		$check['chmod_cache'] = $functions->check_chmod('cache');
		$check['chmod_config'] = $functions->check_chmod('config');
		$check['chmod_dbaaccess'] = $functions->check_chmod('dbbackup');
		$check['chmod_uploads'] = $functions->check_chmod('uploads');
		$check['chmod_dbbackup'] = $functions->check_chmod('dbbackup/backups');
		$check['chmod_smileys'] = $functions->check_chmod('images/smileys');
		$check['chmod_avatars'] = $functions->check_chmod('images/avatars');
		$check['chmod_config_community'] = $functions->check_chmod('config/community.php');
		$check['chmod_config_userdef'] = $functions->check_chmod('config/userdef.php');
		$check['chmod_themes'] = $functions->check_chmod_themes();
		
		$check['mysql_connect'] = 0;
		$check['mysql_select_db'] = 0;
		$host = $functions->get_session_var('host');
		$login = $functions->get_session_var('login');
		$password = $functions->get_session_var('password');
		$database = $functions->get_session_var('database');
	
		if($host != '' && $login != '' && $database != '')
		{
			$check['mysql_connect'] = mysql_connect($host,$login,$password);
			$check['mysql_select_db'] = mysql_select_db($database) ? 1 : 0;
		}
		
		if($functions->get_session_var('install_type') == 'full')
		{
			$admin_login = $functions->get_session_var('admin_login');
			$admin_pw = $functions->get_session_var('admin_pw');
			$admin_email = $functions->get_session_var('admin_email');
			
			$check['admin_login'] = $admin_login != '';
			$check['admin_pw'] = $admin_pw != '';
			$check['admin_email'] = PLIB_StringHelper::is_valid_email($admin_email);
		}
		
		$board_url = $functions->get_session_var('board_url');
		$check['board_url'] = PLIB_String::substr($board_url,0,7) == "http://" && $board_url[PLIB_String::strlen($board_url) - 1] != '/';
		
		// any errors?
		$errors = array();
		foreach($check as $key => $value)
		{
			if(is_array($value))
			{
				if(!$value['success'])
					$errors[] = $locale->lang('error')[$key].'<br />'
						.$locale->lang('error')[$key.'_codes'][$value['error_code']];
			}
			else if(!$value)
				$errors[] = $locale->lang('error')[$key];
		}
		
		return array(count($errors) == 0,$errors);
	}
}
?>
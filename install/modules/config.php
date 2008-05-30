<?php
/**
 * Contains the config module for the installation
 * 
 * @version			$Id: config.php 543 2008-04-10 07:32:51Z nasmussen $
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
		// validate values
		$gd_installed = extension_loaded('gd') && function_exists('imagecreate');
		
		$this->tpl->set_template('step_config.htm');
		
		$configs = array();
		
		$configs[] = $this->functions->get_config_status(
			'PHP-Version:',$this->_check['php'] >= '4.1.0',0,0,$this->_check['php']
		);
		$configs[] = $this->functions->get_config_status(
			'MySQL-Version:',$this->_check['mysql'] >= 3,0,0,$this->_check['mysql']
		);
		$configs[] = $this->functions->get_config_status(
			'GD-Library:',$gd_installed,$this->locale->lang("available"),$this->locale->lang("notavailable"),0,
			$this->locale->lang('gd_description'),'warning'
		);
		
		$configs[] = array('type' => 'separator');
		
		$configs[] = $this->functions->get_config_status(
			'cache/:',$this->_check['chmod_cache'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'config/:',$this->_check['chmod_config'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'dbbackup/:',$this->_check['chmod_dbaaccess'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'uploads/:',$this->_check['chmod_uploads'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'dbbackup/backups/:',$this->_check['chmod_dbbackup'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'images/smileys/:',$this->_check['chmod_smileys'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'images/avatars/:',$this->_check['chmod_avatars'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'config/community.php:',$this->_check['chmod_config_community'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'config/userdef.php:',$this->_check['chmod_config_userdef'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		$configs[] = $this->functions->get_config_status(
			'themes/ *:',$this->_check['chmod_themes']['success'],$this->locale->lang('writable'),$this->locale->lang('notwritable')
		);
		
		$configs[] = array('type' => 'separator');
		
	 	$configs[] = $this->functions->get_config_input(
			"MySQL - Host:","host",$this->_check['mysql_connect'],"",40,40
		);
		$configs[] = $this->functions->get_config_input(
			"MySQL - Login:","login",$this->_check['mysql_connect'],"",40,40
		);
		$configs[] = $this->functions->get_config_input(
			"MySQL - ".$this->locale->lang("password").":","password",$this->_check['mysql_connect'],"",40,40
		);
		$configs[] = $this->functions->get_config_input(
			"MySQL - ".$this->locale->lang("database").":","database",$this->_check['mysql_select_db'],"",40,40
		);
		
		$configs[] = array('type' => 'separator');
		
		if($this->functions->get_session_var('install_type') == 'full')
		{
			$configs[] = $this->functions->get_config_input(
				$this->locale->lang("admin_login").":",'admin_login',$this->_check['admin_login']
			);
			$configs[] = $this->functions->get_config_input(
				$this->locale->lang("admin_pw").":",'admin_pw',$this->_check['admin_pw']
			);
			$configs[] = $this->functions->get_config_input(
				$this->locale->lang("admin_email").":",'admin_email',$this->_check['admin_email'],'',30,255
			);
		}
		
		$configs[] = $this->functions->get_config_input(
			$this->locale->lang('board_url').':','board_url',$this->_check['board_url'],'',40,255,
			$this->locale->lang('board_url_desc')
		);
		
		$this->tpl->add_variables(array(
			'show_table_prefix' => false,
			'title' => $this->locale->lang('step_config')
		));
		$this->tpl->add_array('configs',$configs);
		echo $this->tpl->parse_template();
	}
	
	public function check_inputs(&$check)
	{
		$check['php'] = phpversion();
		$mysql_version = @mysql_get_server_info();
		if(!$mysql_version)
			$mysql_version = mysql_get_client_info();
		$check['mysql'] = $mysql_version;
		$check['chmod_cache'] = $this->functions->check_chmod('cache');
		$check['chmod_config'] = $this->functions->check_chmod('config');
		$check['chmod_dbaaccess'] = $this->functions->check_chmod('dbbackup');
		$check['chmod_uploads'] = $this->functions->check_chmod('uploads');
		$check['chmod_dbbackup'] = $this->functions->check_chmod('dbbackup/backups');
		$check['chmod_smileys'] = $this->functions->check_chmod('images/smileys');
		$check['chmod_avatars'] = $this->functions->check_chmod('images/avatars');
		$check['chmod_config_community'] = $this->functions->check_chmod('config/community.php');
		$check['chmod_config_userdef'] = $this->functions->check_chmod('config/userdef.php');
		$check['chmod_themes'] = $this->functions->check_chmod_themes();
		
		$check['mysql_connect'] = 0;
		$check['mysql_select_db'] = 0;
		$host = $this->functions->get_session_var('host');
		$login = $this->functions->get_session_var('login');
		$password = $this->functions->get_session_var('password');
		$database = $this->functions->get_session_var('database');
	
		if($host != '' && $login != '' && $database != '')
		{
			$check['mysql_connect'] = mysql_connect($host,$login,$password);
			$check['mysql_select_db'] = mysql_select_db($database) ? 1 : 0;
		}
		
		if($this->functions->get_session_var('install_type') == 'full')
		{
			$admin_login = $this->functions->get_session_var('admin_login');
			$admin_pw = $this->functions->get_session_var('admin_pw');
			$admin_email = $this->functions->get_session_var('admin_email');
			
			$check['admin_login'] = $admin_login != '';
			$check['admin_pw'] = $admin_pw != '';
			$check['admin_email'] = PLIB_StringHelper::is_valid_email($admin_email);
		}
		
		$board_url = $this->functions->get_session_var('board_url');
		$check['board_url'] = PLIB_String::substr($board_url,0,7) == "http://" && $board_url[PLIB_String::strlen($board_url) - 1] != '/';
		
		// any errors?
		$errors = array();
		foreach($check as $key => $value)
		{
			if(is_array($value))
			{
				if(!$value['success'])
					$errors[] = $this->locale->lang('error')[$key].'<br />'
						.$this->locale->lang('error')[$key.'_codes'][$value['error_code']];
			}
			else if(!$value)
				$errors[] = $this->locale->lang('error')[$key];
		}
		
		return array(count($errors) == 0,$errors);
	}
}
?>
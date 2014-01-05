<?php
/**
 * Contains the config module for the installation
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
 * The config-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_3 extends BS_Install_Module
{
	/**
	 * @see FWS_Module::init()
	 *
	 * @param BS_Install_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		$renderer = $doc->use_default_renderer();
		$this->check_session();
		$renderer->add_action(3,'forward');
		$renderer->get_action_performer()->perform_action_by_id(3);
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		// validate values
		$status = array();
		$values = BS_Install_Module_3_Helper::collect_vals();
		$errors = BS_Install_Module_3_Helper::check($values,$status);
		
		$configs = array();
		
		$configs[] = $this->get_status(
			'PHP-Version',$status['php'],0,0,$values['php']
		);
		$configs[] = $this->get_status(
			'MySQL-Version',($values['mysql']===true) ? false : $status['mysql'],0,0,
							($values['mysql']===true) ? $locale->lang('mysql_version_to_check') : $values['mysql']
		);
		$configs[] = $this->get_status(
			'GD-Library',$status['gd'],0,0,$values['gd'],$locale->lang('gd_description'),'warning'
		);
		
		$configs[] = array('type' => 'separator','desc' => $locale->lang('step3_chmod'));
		
		$configs[] = $this->get_status(
			'cache/',$status['chmod_cache'],$locale->lang('writable'),
			$locale->lang('notwritable')
		);
		$configs[] = $this->get_status(
			'config/',$status['chmod_config'],$locale->lang('writable'),
			$locale->lang('notwritable')
		);
		$configs[] = $this->get_status(
			'dba/',$status['chmod_dba'],$locale->lang('writable'),
			$locale->lang('notwritable')
		);
		$configs[] = $this->get_status(
			'uploads/',$status['chmod_uploads'],$locale->lang('writable'),
			$locale->lang('notwritable')
		);
		$configs[] = $this->get_status(
			'dba/backups/',$status['chmod_dbabackups'],$locale->lang('writable'),
			$locale->lang('notwritable')
		);
		$configs[] = $this->get_status(
			'images/smileys/',$status['chmod_smileys'],$locale->lang('writable'),
			$locale->lang('notwritable')
		);
		$configs[] = $this->get_status(
			'images/avatars/',$status['chmod_avatars'],$locale->lang('writable'),
			$locale->lang('notwritable')
		);
		
		$configs[] = array('type' => 'separator','desc' => $locale->lang('step3_db'));
		
	 	$configs[] = $this->get_input(
			"MySQL-Host","host",$status['mysql_connect'],"",40,40
		);
		$configs[] = $this->get_input(
			"MySQL-Login","login",$status['mysql_connect'],"",40,40
		);
		$configs[] = $this->get_input(
			"MySQL-".$locale->lang("password"),"password",$status['mysql_connect'],"",40,40
		);
		$configs[] = $this->get_input(
			"MySQL-".$locale->lang("database"),"database",$status['mysql_select_db'],"",40,40
		);
		
		if($user->get_session_data('install_type','full') == 'full')
		{
			$configs[] = array('type' => 'separator','desc' => $locale->lang('step3_other_full'));
			
			$configs[] = $this->get_input(
				$locale->lang("admin_login"),'admin_login',$status['admin_login']
			);
			$configs[] = $this->get_input(
				$locale->lang("admin_pw"),'admin_pw',$status['admin_pw']
			);
			$configs[] = $this->get_input(
				$locale->lang("admin_email"),'admin_email',$status['admin_email'],'',30,255
			);
		}
		else
			$configs[] = array('type' => 'separator','desc' => $locale->lang('step3_other_update'));
		
		$board_url = FWS_Path::outer();
		$configs[] = $this->get_input(
			$locale->lang('board_url'),'board_url',$status['board_url'],
			FWS_FileUtils::ensure_no_trailing_slash($board_url),40,255,
			$locale->lang('board_url_desc')
		);
		
		$tpl->add_variables(array(
			'show_table_prefix' => false,
			'title' => $locale->lang('step_config')
		));
		$tpl->add_variable_ref('configs',$configs);
	}
}
?>
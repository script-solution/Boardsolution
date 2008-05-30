<?php
/**
 * Contains the index module for the ACP
 * 
 * @version			$Id: module_index.php 771 2008-05-25 13:48:03Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The index-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_index extends BS_ACP_Module
{
	public function run()
	{
		// check cookie-domain
		if($this->cfg['cookie_domain'] == '')
			$this->msgs->add_warning($this->locale->lang('cookie_domain_empty'));
		else if(preg_match('/^[a-z]+:\/\//i',$this->cfg['cookie_domain']))
			$this->msgs->add_warning($this->locale->lang('cookie_domain_invalid'));
		
		// check board-url
		$host = $this->input->get_var('HTTP_HOST','server');
		if(!preg_match('/^https?:\/\/'.preg_quote($host,'/').'/',$this->cfg['board_url']))
			$this->msgs->add_warning($this->locale->lang('board_url_invalid'));
		
		// check if all required files and folders are writable
		$writable_items = array(
			'cache',
			'images/smileys',
			'images/avatars',
			'uploads',
			'dba/backups',
			'dba/backups/backups.txt'
		);
		foreach($this->cache->get_cache('themes') as $theme)
		{
			$writable_items[] = 'themes/'.$theme['theme_folder'].'/basic.css';
			$tplfolder = 'themes/'.$theme['theme_folder'].'/templates';
			if(is_dir($tplfolder))
			{
				$tpls = PLIB_FileUtils::get_dir_content($tplfolder);
				foreach($tpls as $tpl)
				{
					if(is_file($tplfolder.'/'.$tpl))
						$writable_items[] = $tplfolder.'/'.$tpl;
				}
			}
		}
		
		$not_writable = array();
		foreach($writable_items as $item)
		{
			if(!PLIB_FileUtils::is_writable(PLIB_Path::inner().$item))
				$not_writable[] = $item;
		}
		
		if(count($not_writable) > 0)
		{
			$list = '<ul>'."\n";
			foreach($not_writable as $nw)
				$list .= '	<li>'.$nw.'</li>'."\n";
			$list .= '</ul>'."\n";
			$this->msgs->add_warning(sprintf($this->locale->lang('paths_not_writable'),$list));
		}
		
		
		// versions
		$gd_version = PLIB_PHPConfig::get_gd_version();
		$this->tpl->add_variables(array(
			'php_version' => PHP_VERSION,
			'mysql_version' => $this->_get_mysql_version(),
			'gd_version' => $gd_version == '0' ? $this->locale->lang('notavailable') : $gd_version,
			'bs_version' => BS_VERSION
		));

		$tasks = array();

		// not activated user
		if($this->cfg['account_activation'] == 'admin')
		{
			$nau_num = BS_DAO::get_user()->get_user_count(0,-1);
			if($nau_num > 0)
				$nau_num = '<span style="font-weight: bold; color: #FF0000;">'.$nau_num.'</span>';
			$uactivate_url = $this->url->get_acpmod_url('useractivation');
			$tasks[] = array(
				'name' => $this->locale->lang('not_activated_user'),
				'detail' => sprintf(
					$this->locale->lang('user_waiting_for_activation'),$nau_num,$uactivate_url
				)
			);
		}
		
		// not activated links
		if($this->cfg['linklist_activate_links'] == 1)
		{
			$nal_num = BS_DAO::get_links()->get_count(0);
			if($nal_num > 0)
				$nal_num = '<span style="font-weight: bold; color: #FF0000;">'.$nal_num.'</span>';
			$lactivate_url = $this->url->get_acpmod_url('linklist');
			$tasks[] = array(
				'name' => $this->locale->lang('not_activated_links'),
				'detail' => sprintf(
					$this->locale->lang('links_waiting_for_activation'),$nal_num,$lactivate_url
				)
			);
		}
		
		$this->tpl->add_array('tasks',$tasks);
		
		// online user
		$online_user = array();
		$locations = $this->sessions->get_user_at_location('all');
		foreach($locations as $data)
		{
			if(!$this->user->is_admin() && $data['ghost_mode'] == 1 && $this->cfg['allow_ghost_mode'] == 1)
			{
				$user_name = '<i>'.$this->locale->lang('hidden_user').'</i>';
				$location = '<i>'.$this->locale->lang('notavailable').'</i>';
			}
			else
			{
				$loc = new BS_Location($data['location']);
				$location = $loc->decode(false);
				if($data['bot_name'] != '')
				{
					$user_name = $data['bot_name'];
					if($data['duplicates'] > 0)
						$user_name .= ' ('.($data['duplicates'] + 1).'x)';
				}
				else if($data['user_id'] == 0)
					$user_name = $this->locale->lang('guest');
				else
				{
					$ucolor = $this->auth->get_user_color($data['user_id'],$data['user_group']);
					$name = '<span style="color: #'.$ucolor.'">'.$data['user_name'].'</span>';
					$user_name = BS_ACP_Utils::get_instance()->get_userlink($data['user_id'],$name);
					if($data['duplicates'] > 0)
						$user_name .= ' ('.($data['duplicates'] + 1).'x)';
				}
			}

			$a_user_agent = PLIB_StringHelper::get_limited_string($data['user_agent'],35);
			if($a_user_agent['complete'] != '')
			{
				$user_agent = '<span title="'.$a_user_agent['complete'].'">';
				$user_agent .= $a_user_agent['displayed'].'</span>';
			}
			else
				$user_agent = $a_user_agent['displayed'];

			$online_user[] = array(
				'name' => $user_name,
				'location' => $location,
				'ip' => $data['user_ip'],
				'agent' => $user_agent,
				'date' => PLIB_Date::get_date($data['date'])
			);
		}
		
		$this->tpl->add_array('online_user',$online_user);

		// statistics
		$stats = $this->functions->get_stats();
		$dbsize = $this->_get_db_size();
		if($dbsize >= 0)
		{
			$stats['database_size'] = PLIB_StringHelper::get_formated_data_size(
				$dbsize,$this->locale->get_thousands_separator(),$this->locale->get_dec_separator()
			);
		}
		else
			$stats['database_size'] = $this->locale->lang('notavailable');
		
		$stats['upload_dir_size'] = $this->_get_dir_size(PLIB_Path::inner().'uploads');
		$stats['avatar_dir_size'] = $this->_get_dir_size(PLIB_Path::inner().'images/avatars');
		$stats['smiley_dir_size'] = $this->_get_dir_size(PLIB_Path::inner().'images/smileys');

		$this->tpl->add_variables(array(
			'safe_mode' => $this->_get_php_flag('safe_mode'),
			'open_basedir' => @ini_get('open_basedir'),
			'max_execution_time' => @ini_get('max_execution_time'),
			'memory_limit' => @ini_get('memory_limit'),
			'error_reporting' => @ini_get('error_reporting'),
			'display_errors' => $this->_get_php_flag('display_errors'),
			'register_globals' => $this->_get_php_flag('register_globals'),
			'magic_quotes_gpc' => $this->_get_php_flag('magic_quotes_gpc'),
			'upload_max_filesize' => @ini_get('upload_max_filesize'),
			'file_uploads' => $this->_get_php_flag('file_uploads'),
			'stats' => $stats,
			'phpinfo_url' => $this->url->get_acpmod_url('phpinfo')
		));
	}

	/**
	 * determines if the setting is activated
	 *
	 * @param string $flag the php-flag
	 * @return string yes or no
	 */
	private function _get_php_flag($flag)
	{
		return BS_ACP_Utils::get_instance()->get_yesno(PLIB_PHPConfig::is_enabled($flag));
	}

	/**
	 * determines the size of the database
	 *
	 * @return int the size or -1 if not valid
	 */
	private function _get_db_size()
	{
		$dbsize = 0;
		$version = $this->_get_mysql_version();

		$tables = array();
		$constants = get_defined_constants();
		foreach($constants as $c => $v)
		{
			if(PLIB_String::substr($c,0,6) == 'BS_TB_')
				$tables[$v] = true;
		}

		if(preg_match("/^(3\\.23|4\\.|5\\.)/",$version))
		{
			$qry = $this->db->sql_qry('SHOW TABLE STATUS FROM `'.BS_MYSQL_DATABASE.'`');
			if($qry !== false)
			{
				while($data = $this->db->sql_fetch_assoc($qry))
				{
					$engine = isset($data['Engine']) ? $data['Engine'] : $data['Type'];
					if($engine != 'MRG_MyISAM' && isset($tables[$data['Name']]))
						$dbsize += $data['Data_length'] + $data['Index_length'];
				}
				$this->db->sql_free($qry);
			}
			else
				$dbsize = -1;
		}

		return $dbsize;
	}

	/**
	 * determines the mysql-version
	 *
	 * @return string the version
	 */
	private function _get_mysql_version()
	{
		static $version = null;
		if($version == null)
			$version = $this->db->get_server_version();

		return $version;
	}

	/**
	 * determines the size of the given directory
	 *
	 * @param string $dir the directory
	 * @return string the size of the directory in bytes (formated)
	 */
	private function _get_dir_size($dir)
	{
		$size = PLIB_FileUtils::get_dir_size($dir,false);
		return PLIB_StringHelper::get_formated_data_size(
			$size,$this->locale->get_thousands_separator(),$this->locale->get_dec_separator()
		);
	}

	public function get_location()
	{
		return array();
	}
}
?>
<?php
/**
 * Contains the base-SQL-class.
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
 * The base SQL-class. The update- and full-installation will inherit from this class
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Install_Module_5_SQL_Base extends FWS_Object
{
	/**
	 * The logger
	 *
	 * @var string
	 */
	private $_log;
	
	/**
	 * Starts the install-/update-process
	 */
	public final function start()
	{
		$user = FWS_Props::get()->user();

		$prefix = $user->get_session_data('table_prefix','bs_');
		$install_type = $user->get_session_data('install_type','full');
		
		$this->add_to_log('Creating "config/mysql.php"...');
		if(FWS_FileUtils::write('config/mysql.php',$this->_get_mysql_config()))
		{
			$this->add_to_log_success();
			
			$this->add_to_log('Creating "dba/access.php"...');
			$content = '<?php'."\n";
			$content .= 'define(\'BS_DBA_USERNAME\',\''.FWS_StringHelper::generate_random_key(6).'\');'."\n";
			$content .= 'define(\'BS_DBA_PASSWORD\',\''.FWS_StringHelper::generate_random_key(10).'\');'."\n";
			$content .= '?>';
			if(FWS_FileUtils::write('dba/access.php',$content))
			{
				$this->add_to_log_success();
						
				// now execute the db-instructions
				$this->run();
			}
			else
				$this->add_to_log_failed();
		}
		else
			$this->add_to_log_failed();
	}
	
	/**
	 * Should perform all necessary operations
	 * The sub-classes will overwrite this method
	 */
	protected abstract function run();
	
	/**
	 * @return string the log-content
	 */
	public final function get_log()
	{
		return $this->_log;
	}

	/**
	 * adds the given message to log
	 * 
	 * @param string $text the text to add
	 * @param string $float left or right
	 * @param string $color the text-color
	 * @param boolean $line_ending add a line-ending?
	 */
	protected function add_to_log($text,$float = 'left',$color = '#000000',$line_ending = false)
	{
		$this->_log .= '<span style="float: '.$float.'; color: '.$color.';">'.$text.'</span>';
		if($line_ending)
			$this->_log .= '<br />'."\n";
	}
	
	/**
	 * adds a success-message to the log
	 */
	protected function add_to_log_success()
	{
		$this->add_to_log('OK','right','#008000',true);
	}
	
	/**
	 * adds a failed-message to the log
	 */
	protected function add_to_log_failed()
	{
		$this->add_to_log('Failed','right','#FF0000',true);
	}
	
	/**
	 * Creates the content of the config/mysql.php and returns it
	 *
	 * @return string the file-content
	 */
	private function _get_mysql_config()
	{
		$user = FWS_Props::get()->user();

		$host = $user->get_session_data('host');
		$login = $user->get_session_data('login');
		$password = $user->get_session_data('password');
		$database = $user->get_session_data('database');
		$prefix = $user->get_session_data('table_prefix');
		
		$content = '<?php'."\n";
		$content .= '##########################################'."\n";
		$content .= '###### Generated MySQL-Config-File #######'."\n";
		$content .= '##########################################'."\n";
		$content .= 'define(\'BS_MYSQL_HOST\',\''.$host.'\');'."\n";
		$content .= 'define(\'BS_MYSQL_LOGIN\',\''.$login.'\');'."\n";
		$content .= 'define(\'BS_MYSQL_PASSWORD\',\''.html_entity_decode($password, ENT_QUOTES, BS_HTML_CHARSET).'\');'."\n";
		$content .= 'define(\'BS_MYSQL_DATABASE\',\''.$database.'\');'."\n";
		$content .= '##########################################'."\n";
		
		$content .= "\n";
		$content .= '############## MySQL-Tables ############'."\n";
		foreach(BS_Install_Module_5_Helper::get_tables() as $constant => $value)
			$content .= 'define(\''.$constant.'\',\''.$value.'\');'."\n";
		$content .= '##########################################'."\n";
		$content .= '?>';
		return $content;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
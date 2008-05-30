<?php
/**
 * Contains the base-SQL-class.
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base SQL-class. The update- and full-installation will inherit from this class
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_InstallSQL
{
	/**
	 * The logger
	 *
	 * @var string
	 */
	var $_log;
	
	/**
	 * Constructor
	 * 
	 * @param object $base the base-object
	 */
	public function BS_InstallSQL(&$base)
	{
		BS_add_objects_to_class($base,$this);
	}
	
	/**
	 * Starts the install-/update-process
	 *
	 */
	public function start()
	{
		$prefix = $this->functions->get_session_var('table_prefix');
		$install_type = $this->functions->get_session_var('install_type');
		$board_url = $this->functions->get_session_var('board_url');
		
		$this->add_to_log('Creating "config/mysql.php"...');
		if($fp = @fopen('config/mysql.php','w'))
		{
			$content = $this->_get_mysql_config();
			flock($fp,LOCK_EX);
			fwrite($fp,$content);
			flock($fp,LOCK_UN);
			fclose($fp);
			
			$this->add_to_log_success();
			
			$this->add_to_log('Modifying "config/community.php"...');
		
			$com_content = implode('',file('config/community.php'));
			if($com_handle = @fopen('config/community.php','a'))
			{
				flock($com_handle,LOCK_EX);
				ftruncate($com_handle,0);
				$com_content = preg_replace(
					'/define\(\'BS_TB_USER\',\'([^\']*)\'\);/i',
					'define(\'BS_TB_USER\',\''.$prefix.'user\');',
					$com_content
				);
				fwrite($com_handle,$com_content);
				flock($com_handle,LOCK_UN);
				fclose($com_handle);
				
				$this->add_to_log_success();
				
				$this->add_to_log('Modifying "config/userdef.php"...');
					
				$cfg_content = implode('',file('config/userdef.php'));
				if($cfg_handle = @fopen('config/userdef.php','a'))
				{
					flock($cfg_handle,LOCK_EX);
					ftruncate($cfg_handle,0);
					$cfg_content = preg_replace(
						'/define\(\'BS_FOLDER_URL\',\'([^\']*)\'\);/i',
						'define(\'BS_FOLDER_URL\',\''.$board_url.'\');',
						$cfg_content
					);
					fwrite($cfg_handle,$cfg_content);
					flock($cfg_handle,LOCK_UN);
					fclose($cfg_handle);
					
					$this->add_to_log_success();
					
					$this->add_to_log('Creating "dbbackup/access.php"...');
					if($access_handle = @fopen('dbbackup/access.php','w'))
					{
						$content = '<?php'."\n";
						$content .= 'define(\'BS_DBA_USERNAME\',\''.PLIB_StringHelper::generate_random_key(6).'\');'."\n";
						$content .= 'define(\'BS_DBA_PASSWORD\',\''.PLIB_StringHelper::generate_random_key(10).'\');'."\n";
						$content .= '?>';
						flock($access_handle,LOCK_EX);
						fwrite($access_handle,$content);
						flock($access_handle,LOCK_UN);
						fclose($access_handle);
						
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
			else
				$this->add_to_log_failed();
		}
		else
			$this->add_to_log_failed();
	}
	
	/**
	 * Should perform all necessary operations
	 * The sub-classes will overwrite this method
	 *
	 */
	public function run()
	{
		
	}
	
	/**
	 * @return string the log-content
	 */
	public function get_log()
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
	public function add_to_log($text,$float = 'left',$color = '#000000',$line_ending = false)
	{
		$this->_log .= '<span style="float: '.$float.'; color: '.$color.';">'.$text.'</span>';
		if($line_ending)
			$this->_log .= '<br />'."\n";
	}
	
	/**
	 * adds a success-message to the log
	 *
	 */
	public function add_to_log_success()
	{
		$this->add_to_log('OK','right','#008000',true);
	}
	
	/**
	 * adds a failed-message to the log
	 *
	 */
	public function add_to_log_failed()
	{
		$this->add_to_log('Failed','right','#FF0000',true);
	}
	
	/**
	 * Creates the content of the config/mysql.php and returns it
	 *
	 * @return string the file-content
	 */
	public function _get_mysql_config()
	{
		$host = $this->functions->get_session_var('host');
		$login = $this->functions->get_session_var('login');
		$password = $this->functions->get_session_var('password');
		$database = $this->functions->get_session_var('database');
		$prefix = $this->functions->get_session_var('table_prefix');
		
		$content = '<?php'."\r\n";
		$content .= '##########################################'."\r\n";
		$content .= '###### Generated MySQL-Config-File #######'."\r\n";
		$content .= '##########################################'."\r\n";
		$content .= 'define(\'BS_MYSQL_HOST\',\''.$host.'\');'."\r\n";
		$content .= 'define(\'BS_MYSQL_LOGIN\',\''.$login.'\');'."\r\n";
		$content .= 'define(\'BS_MYSQL_PASSWORD\',\''.$password.'\');'."\r\n";
		$content .= 'define(\'BS_MYSQL_DATABASE\',\''.$database.'\');'."\r\n";
		$content .= '##########################################'."\r\n";
	
		$tables = array(
			'BS_TB_ACP_ACCESS' => 'acp_access',
			'BS_TB_ACTIVATION' => 'activation',
			'BS_TB_ATTACHMENTS' => 'attachments',
			'BS_TB_AVATARS' => 'avatars',
			'BS_TB_BANS' => 'banlist',
			'BS_TB_BOTS' => 'bots',
			'BS_TB_CACHE' => 'cache',
			'BS_TB_CHANGE_EMAIL' => 'change_email',
			'BS_TB_CHANGE_PW' => 'change_pw',
			'BS_TB_DESIGN' => 'config',
			'BS_TB_EVENTS' => 'events',
			'BS_TB_FORUMS' => 'forums',
			'BS_TB_INTERN' => 'intern',
			'BS_TB_LANGS' => 'languages',
			'BS_TB_LINKS' => 'links',
			'BS_TB_LOG_ERRORS' => 'log_errors',
			'BS_TB_LOG_IPS' => 'log_ips',
			'BS_TB_MODS' => 'moderators',
			'BS_TB_PMS' => 'pms',
			'BS_TB_POLL' => 'polls',
			'BS_TB_POLL_VOTES' => 'poll_votes',
			'BS_TB_POSTS' => 'posts',
			'BS_TB_PROFILES' => 'profiles',
			'BS_TB_RANKS' => 'user_ranks',
			'BS_TB_SEARCH' => 'search',
			'BS_TB_SESSIONS' => 'sessions',
			'BS_TB_SMILEYS' => 'smileys',
			'BS_TB_SUBSCR' => 'subscriptions',
			'BS_TB_TASKS' => 'tasks',
			'BS_TB_THEMES' => 'themes',
			'BS_TB_THREADS' => 'topics',
			'BS_TB_USER_BANS' => 'user_bans',
			'BS_TB_USER_FIELDS' => 'user_fields',
			'BS_TB_USER_GROUPS' => 'user_groups'
		);
		
		$content .= "\r\n";
		$content .= '############## MySQL-Tables ############'."\r\n";
		foreach($tables as $constant => $value)
			$content .= 'define(\''.$constant.'\',\''.$prefix.$value.'\');'."\r\n";
		$content .= '##########################################'."\r\n";
		$content .= '?>';
		return $content;
	}
}
?>

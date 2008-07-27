<?php
/**
 * Contains the install-functions
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Some additional functions for the installation
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_InstallFunctions extends BS_Functions
{
	/**
	 * constructor
	 * 
	 * @param object $base the base-object
	 */
	public function BS_InstallFunctions(&$base)
	{
		BS_Functions::BS_Functions($base);
	}
	
	/**
	 * Sets the value of the session-variable with given name to the value
	 * 
	 * @param string $name the name of the variable
	 * @param mixed $value the new value
	 */
	public function set_session_var($name,$value)
	{
		$_SESSION['BS11_install'][$name] = $value;
	}
	
	/**
	 * Returns the value of the session-variable with given name
	 * 
	 * @param string $name the name of the variable
	 * @return mixed the value
	 */
	public function get_session_var($name)
	{
		if(!isset($_SESSION['BS11_install'][$name]))
			return null;
		
		return $_SESSION['BS11_install'][$name];
	}
	
	/**
	 * transfers the submitted form-data from the current step to session
	 *
	 */
	public function transfer_to_session()
	{
		$input = PLIB_Props::get()->input();

		if(!isset($_SESSION['BS11_install']))
			$this->_init_session();
		
		$step_submit = $input->get_var('step_submit','post',PLIB_Input::INTEGER);
		if($step_submit === null)
			return;
		
		switch($step_submit)
		{
			case 1:
				$install_type = $input->correct_var(
					'install_type','post',PLIB_Input::STRING,array('full','update'),'full'
				);
				$this->set_session_var('install_type',$install_type);
				break;
			
			case 2:
				$this->set_session_var('host',$input->get_var('host','post',PLIB_Input::STRING));
				$this->set_session_var('login',$input->get_var('login','post',PLIB_Input::STRING));
				$this->set_session_var('password',$input->get_var('password','post',PLIB_Input::STRING));
				$this->set_session_var('database',$input->get_var('database','post',PLIB_Input::STRING));
				
				$this->set_session_var('admin_login',$input->get_var('admin_login','post',PLIB_Input::STRING));
				$this->set_session_var('admin_pw',$input->get_var('admin_pw','post',PLIB_Input::STRING));
				$this->set_session_var('admin_email',$input->get_var('admin_email','post',PLIB_Input::STRING));
				$this->set_session_var('board_url',$input->get_var('board_url','post',PLIB_Input::STRING));
				break;
			
			case 3:
				$this->set_session_var('table_prefix',$input->get_var('table_prefix','post',PLIB_Input::STRING));
				break;
		}
	}
	
	/**
	 * Inits the session-vars
	 *
	 */
	public function _init_session()
	{
		$_SESSION['BS11_install'] = array(
			'host' => '',
			'login' => '',
			'password' => '',
			'database' => '',
			'admin_login' => '',
			'admin_pw' => '',
			'admin_email' => '',
			'board_url' => $this->get_calculated_path(),
			'install_type' => 'full',
			'table_prefix' => 'bs_'
		);
	}
	
	/**
	 * Clears the session
	 */
	public function clear_session()
	{
		unset($_SESSION['BS11_install']);
		session_destroy();
	}
	
	/**
	 * Prepares the text for storing in the db
	 * 
	 * @param string $text the text
	 * @return string the text to store in the db
	 */
	public function reformat_text($text)
	{
		$text = PLIB_StringHelper::htmlspecialchars_back($text);
		$text = htmlspecialchars($text,ENT_QUOTES);
		return addslashes($text);
	}

	/**
	 * tries to determine the path
	 *
	 * @return string the path
	 */
	public function get_calculated_path()
	{
		$input = PLIB_Props::get()->input();

		$host = $input->get_var('HTTP_HOST','server',PLIB_Input::STRING);
		$phpself = $input->get_var('PHP_SELF','server',PLIB_Input::STRING);
		$path = $host.dirname($phpself);
		if(PLIB_String::substr($path,0,7) != 'http://')
			$path = 'http://'.$path;
		
		// remove trailing slash
		if($path[count($path) - 1] == '/')
			$path = PLIB_String::substr($path,0,-1);
		
		return $path;
	}

	/**
	 * checks wether the given directory / file is writable
	 * will try to set the chmod
	 * 
	 * @param string $path the file or directory to check
	 * @return boolean true if the path is writable
	 */
	public function check_chmod($path)
	{
		if($path[PLIB_String::strlen($path) - 1] == '/')
			$path = PLIB_String::substr($path,0,-1);
		
		// if the file / dir does not exist it can't have a valid chmod
		if(!file_exists($path))
			return false;
		
		if(is_dir($path))
		{
			$tmp = uniqid(mt_rand()).'.tmp';
			$fp = @fopen($path.'/'.$tmp,'a');
			if($fp === false)
			{
				// attempt to change the chmod
				@chmod($path,0777);
				// now check again if we create a file in the directory
				$fp = @fopen($path.'/'.$tmp,'a');
				if($fp === false)
					return false;
			}
			
			// file has been created, so clean up
			fclose($fp);
			@unlink($path.'/'.$tmp);
			return true;
		}
		
		// try to open the file for writing
		$fp = @fopen($path,'a');
		if($fp === false)
		{
			@chmod($path,0666);
			$fp = @fopen($path,'a');
			if($fp === false)
				return false;
		}
		
		// file is writable so cleanup
		fclose($fp);
		
		return true;
	}
	
	/**
	 * checks the CHMOD-attributes of all templates and the style.css in all themes
	 * 
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(
	 * 			'success' => <bool>,
	 * 			'error_code' => <code>
	 *		)
	 * 	</code>
	 * 
	 * 	the error-code:
	 * 		1 => themes/default/style.css,
	 * 		2 => a template in themes/default/templates,
	 * 		3 => themes/default/templates not readable
	 */
	public function check_chmod_themes()
	{
		// check css-files
		if(!$this->check_chmod('themes/default/style.css'))
			return array('success' => false,'error_code' => 1);
		
		// check templates in the default theme
		if($handle = @opendir('themes/default/templates'))
		{
			while($file = readdir($handle))
			{
				if($file == '.' || $file == '..')
					continue;
				
				if(!$this->check_chmod('themes/default/templates/'.$file))
					return array('success' => false,'error_code' => 2);
			}
			closedir($handle);
			
			return array('success' => true,'error_code' => 0);
		}
		
		return array('success' => false,'error_code' => 3);
	}

	/**
	 * displays the navigation
	 * 
	 * @param string $loc the location: top, bottom
	 */
	public function display_navigation($loc)
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();

		$show_refresh = false;
	
		switch($this->step)
		{
			case 2:
			case 3:
				$show_refresh = true;
		}
		
		$phpself = $input->get_var('PHP_SELF','server',PLIB_Input::STRING);
		$tpl->set_template('navigation.htm');
		$tpl->add_variables(array(
			'loc' => $loc,
			'show_refresh' => $show_refresh,
			'back_url' => $phpself.'?step='.($this->step - 1)
				.'&amp;lang='.$this->lang_name,
			'back_disabled' => $this->step == 0 || $this->step > 4 ? ' disabled="disabled"' : '',
			'forward_url' => $phpself.'?step='.$this->step
				.'&amp;forward=1&amp;lang='.$this->lang_name
		));
		echo $tpl->parse_template();
	}

	/**
	 * Builds an output table
	 * 
	 * @param string $title the title of the row
	 * @param boolean $check is this row valid?
	 * @param mixed $in_ok the text to display if the row is valid
	 * @param mixed $in_nok the text to display if the row is NOT valid
	 * @param mixed $title_out the text to display at the right side
	 * @param string $description the description of the field
	 * @param string $failed_img the failed-image
	 */
	public function get_config_status($title,$check,$in_ok = 0,$in_nok = 0,$title_out = 0,
		$description = '',$failed_img = 'failed')
	{
		$locale = PLIB_Props::get()->locale();

		$ok = ($in_ok === 0) ? $locale->lang('ok') : $in_ok;
		$notok = ($in_nok === 0) ? $locale->lang('notok') : $in_nok;
		
		if($description != '')
			$title .= '<br /><span style="font-size: 7pt; font-weight: normal;">'.$description.'</span>';
		
		return array(
			'type' => 'status',
			'title' => $title,
			'status' => ($title_out === 0) ? ($check ? $ok : $notok) : $title_out,
			'image' => $check ? 'ok' : $failed_img
		);
	}

	/**
	 * Builds a config-table
	 * 
	 * @param string $title the title of the config
	 * @param string $name the name of the field in the post-vars
	 * @param boolean $cond the condition to check this setting
	 * @param string $default the default value of the config-field
	 * @param int $size the size of the input-field
	 * @param int $maxlength the max length of the input field
	 * @param string $description the description of the field
	 */
	public function get_config_input($title,$name,$cond,$default = "admin",$size = 20,$maxlength = 20,
		$description = '')
	{
		if($description != '')
			$title .= '<div class="bs_desc">'.$description.'</div>';
		
		return array(
			'type' => 'input',
			'title' => $title,
			'name' => $name,
			'size' => $size,
			'maxlength' => $maxlength,
			'value' => $this->get_session_var($name) !== null ? $this->get_session_var($name) : $default,
			'image' => $cond ? 'ok' : 'failed'
		);
	}
}
?>
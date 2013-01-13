<?php
/**
 * Contains the module for the install-script
 * 
 * @package			Boardsolution
 * @subpackage	install.src
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
 * The module-base class for all install-modules
 * 
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Install_Module extends FWS_Module
{
	/**
	 * Checks wether the session contains the necessary data to continue.
	 * If not the user will be redirected to step 2
	 * 
	 * @return boolean true if everything is ok
	 */
	protected function check_session()
	{
		$user = FWS_Props::get()->user();
		if($user->get_session_data('install_type') === false)
		{
			$this->_to_step2();
			return false;
		}
		return true;
	}
	
	/**
	 * Connects to the database. If this fails it redirects the user to step 2 (the session
	 * has probably expired).
	 */
	protected function connect_to_db()
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		if(!$this->check_session())
			return;
		
		$host = $user->get_session_data('host','');
		$login = $user->get_session_data('login','');
		$pw = $user->get_session_data('password','');
		$dbname = $user->get_session_data('database','');
		try
		{
			$db->connect($host,$login,$pw);
			$db->select_database($dbname);
		}
		catch(FWS_DB_Exception_DBSelectFailed $ex)
		{
			$this->_to_step2();
		}
		catch(FWS_DB_Exception_ConnectionFailed $ex)
		{
			$this->_to_step2();
		}
	}
	
	/**
	 * Redirects the user to step 2
	 */
	private function _to_step2()
	{
		$doc = FWS_Props::get()->doc();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		
		$msgs->add_error($locale->lang('error_session_expired'));
		$url = new FWS_URL();
		$url->set_file('install.php');
		$url->set('action',2);
		$msgs->add_link($locale->lang('back'),$url->to_url());
		$doc->request_redirect($url,5);
		$this->set_error();
	}
	
	/**
	 * Builds the template-values for an input-field
	 * 
	 * @param string $title the title of the config
	 * @param string $name the name of the field in the post-vars
	 * @param boolean $cond the condition to check this setting
	 * @param string $default the default value of the config-field
	 * @param int $size the size of the input-field
	 * @param int $maxlength the max length of the input field
	 * @param string $description the description of the field
	 * @return array the field
	 */
	protected function get_input($title,$name,$cond,$default = "admin",$size = 20,$maxlength = 20,
		$description = '')
	{
		$user = FWS_Props::get()->user();
		
		return array(
			'type' => 'input',
			'title' => $title,
			'description' => $description,
			'name' => $name,
			'size' => $size,
			'maxlength' => $maxlength,
			'value' => $user->get_session_data($name) !== false ? $user->get_session_data($name) : $default,
			'image' => $cond ? 'ok' : 'failed'
		);
	}
	
	/**
	 * Builds the template-values for a status-field
	 * 
	 * @param string $title the title of the row
	 * @param boolean $status is this row valid?
	 * @param mixed $in_ok the text to display if the row is valid
	 * @param mixed $in_nok the text to display if the row is NOT valid
	 * @param mixed $title_out the text to display at the right side
	 * @param string $description the description of the field
	 * @param string $failed_img the failed-image
	 * @return array the field
	 */
	protected function get_status($title,$status,$in_ok = 0,$in_nok = 0,$title_out = 0,
		$description = '',$failed_img = 'failed')
	{
		$locale = FWS_Props::get()->locale();

		$ok = ($in_ok === 0) ? $locale->lang('ok') : $in_ok;
		$notok = ($in_nok === 0) ? $locale->lang('notok') : $in_nok;
		
		return array(
			'type' => 'status',
			'title' => $title,
			'description' => $description,
			'status' => ($title_out === 0) ? ($status ? $ok : $notok) : $title_out,
			'image' => $status ? 'ok' : $failed_img
		);
	}
}
?>
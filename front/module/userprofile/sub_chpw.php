<?php
/**
 * Contains the chpw-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The chpw submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_chpw extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_CHANGE_USER_PW => 'chguserpw'
		);
	}
	
	public function run()
	{
		// has the user the permission to change user/pw
		if(BS_ENABLE_EXPORT)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR);
			return;
		}

		$max_changes_notice = '';
		if($this->cfg['profile_max_user_changes'] > 0)
		{
			$left = max(0,$this->cfg['profile_max_user_changes'] - $this->user->get_profile_val('username_changes'));
			$max_changes_notice = sprintf($this->locale->lang('max_username_changes_notice'),$left);
		}

		$this->tpl->set_template('inc_pw_complexity_js.htm');
		$js_script = $this->tpl->parse_template();
		
		$this->tpl->add_variables(array(
			'js_script' => $js_script
		));
		
		$this->_request_formular();
		$this->tpl->add_variables(array(
			'user_name_size' => max(30,$this->cfg['profile_max_user_len']),
			'user_name_maxlength' => $this->cfg['profile_max_user_len'],
			'password_size' => max(30,$this->cfg['profile_max_pw_len']),
			'password_maxlength' => $this->cfg['profile_max_pw_len'],
			'target_url' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=chpw'),
			'action_type' => BS_ACTION_CHANGE_USER_PW,
			'enable_username_change' => $this->cfg['profile_max_user_changes'] != 0,
			'max_changes_notice' => $max_changes_notice,
			'user_name' => $this->user->get_profile_val('user_name')
		));
	}
	
	public function get_location()
	{
		$title = $this->cfg['profile_max_user_changes'] != 0 ? 'user_n_pw_change' : 'pw_change';
		return array(
			$this->locale->lang($title) => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=chpw')
		);
	}
}
?>
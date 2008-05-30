<?php
/**
 * Contains the change-user-pw-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The change-user-pw-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_chguserpw extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// this is not allowed if the community has been exported
		if(BS_ENABLE_EXPORT)
			return 'The community is exported';

		// is the user loggedin?
		if(!$this->user->is_loggedin())
			return 'You are a guest';

		$user_name = $this->input->get_var('user_name','post',PLIB_Input::STRING);
		$new_password = $this->input->get_var('new_password','post',PLIB_Input::STRING);
		$new_password_conf = $this->input->get_var('new_password_conf','post',PLIB_Input::STRING);
		$current_password = $this->input->get_var('current_password','post',PLIB_Input::STRING);

		$change_username = false;
		$change_password = false;

		// check if the username is valid
		if($user_name != $this->user->get_profile_val('user_name') && $this->cfg['profile_max_user_changes'] != 0)
		{
			// is the username empty?
			if(trim($user_name) == '')
				return 'registeruserleer';

			if(!BS_UserUtils::get_instance()->check_username($user_name))
				return 'usernamenotallowed';

			if($this->functions->is_banned('user',$user_name))
				return 'usernamenotallowed';

			if(BS_DAO::get_user()->name_exists($user_name,$this->user->get_user_id()))
				return 'registeruservorhanden';

			$len = PLIB_String::strlen($user_name);
			if($len < $this->cfg['profile_min_user_len'] && $len > $this->cfg['profile_max_user_len'])
				return sprintf($this->locale->lang('error_wronguserlen'),
											 $this->cfg['profile_min_user_len'],
											 $this->cfg['profile_max_user_len']);

			if($this->cfg['profile_max_user_changes'] > 0 &&
				 $this->user->get_profile_val('username_changes') >= $this->cfg['profile_max_user_changes'])
				return 'max_username_changes';

			$change_username = true;
		}

		// does the user want to change the password?
		if($new_password != '' || $new_password_conf != '')
		{
			if($new_password != $new_password_conf)
				return 'pwchangefailed';

			if(md5($current_password) != $this->user->get_profile_val('user_pw'))
				return 'pwchangefailed';

			$change_password = true;
		}

		// nothing to do?
		if(!$change_username && !$change_password)
			return '';

		$password = md5($new_password);

		// build the query
		if($change_username)
		{
			if($this->cfg['profile_max_user_changes'] > 0)
			{
				BS_DAO::get_profile()->update_user_by_id(
					array('username_changes' => array('username_changes + 1')),$this->user->get_user_id()
				);
			}
		}
		
		if(!$change_username)
			$user_name = '';
		if(!$change_password)
			$password = '';
		
		BS_DAO::get_user()->update($this->user->get_user_id(),$user_name,'',$password);

		// if this user is a moderator, we have to change the user-name there, too.
		$moderators = $this->cache->get_cache('moderators');
		$mod_rows = $moderators->get_elements_with(array('user_id' => $this->user->get_user_id()));
		$mod_len = count($mod_rows);
		if($mod_len > 0)
		{
			foreach(array_keys($mod_rows) as $key)
				$moderators->set_element_field($key,'user_name',$user_name);
			$this->cache->store('moderators');
		}

		if($change_username)
		{
			$this->user->set_profile_val('user_name',$user_name);
			$this->cookies->set_cookie('user',$user_name);
		}

		if($change_password)
		{
			$this->user->set_profile_val('user_pw',$password);
			$this->cookies->set_cookie('pw',$password);
		}
		
		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),$this->url->get_url(0,'&'.BS_URL_LOC.'=chpw','&')
		);

		return '';
	}
}
?>
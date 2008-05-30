<?php
/**
 * Contains the avatars-userprofile-submodule
 * 
 * @version			$Id: sub_avatars.php 765 2008-05-24 21:14:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The avatars submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_avatars extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_UPLOAD_AVATAR => 'uploadavatar',
			BS_ACTION_USE_AVATAR => 'useavatar',
			BS_ACTION_DELETE_AVATAR => 'deleteavatars',
			BS_ACTION_REMOVE_AVATAR => 'removeavatar'
		);
	}
	
	public function run()
	{
		// has the user the permission to configure the avatars?
		if(!$this->user->is_loggedin() || $this->cfg['enable_avatars'] == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}

		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		if($site == null || $site < 1)
			$site = 1;

		if(($delete = $this->input->get_var('del','post')) != null && PLIB_Array_Utils::is_integer($delete))
		{
			$id_str = implode(',',$delete);
			$loc = '&amp;'.BS_URL_LOC.'=avatars';
			$site_param = '&amp;'.BS_URL_SITE.'='.$site;
			$yes_url = $this->url->get_url(0,$loc.'&amp;'.BS_URL_AT.'='.BS_ACTION_DELETE_AVATAR
																.'&amp;'.BS_URL_DEL.'='.$id_str.$site_param,'&amp;',true);
			$no_url = $this->url->get_url(0,$loc.$site_param);
			$target = $this->url->get_url(
				'redirect',
				'&amp;'.BS_URL_LOC.'=del_avatars&amp;'.BS_URL_ID.'='.$id_str.'&amp;'.BS_URL_SITE.'='.$site
			);
			
			$names = array();
			foreach(BS_DAO::get_avatars()->get_by_ids($delete) as $data)
				$names[] = $data['av_pfad'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_avatars_message'),$namelist),
				$yes_url,$no_url,$target
			);
		}

		$num = BS_DAO::get_avatars()->get_count_for_user($this->user->get_user_id());
		$pagination = new BS_Pagination(BS_AVATARS_PER_PAGE,$num);
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site
			),
			'num' => $num
		));

		$avatars = array();
		$avlist = BS_DAO::get_avatars()->get_list_for_user(
			$this->user->get_user_id(),$pagination->get_start(),BS_AVATARS_PER_PAGE
		);
		foreach($avlist as $index => $data)
		{
			$use_url = $this->url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_AT.'='.BS_ACTION_USE_AVATAR
					.'&amp;'.BS_URL_ID.'='.$data['id'].'&amp;'.BS_URL_SITE.'='.$site,'&amp;',true
			);
			if($data['user'] == 0)
				$delete = $this->locale->lang('notavailable');
			else
			{
				$delete = '<input type="checkbox" id="avatar_'.$index.'" name="del[]"';
				$delete .= ' value="'.$data['id'].'" onclick="this.checked = this.checked ? false : true;" />';
			}
			
			$avatars[] = array(
				'user_name' => ($data['user'] == 0) ? $this->locale->lang('administrator') : $data['user_name'],
				'delete' => $delete,
				'avatar_path' => PLIB_Path::inner().'images/avatars/'.$data['av_pfad'],
				'display_path' => PLIB_String::substr($data['av_pfad'],0,25).((PLIB_String::strlen($data['av_pfad']) > 25) ? '...' : ''),
				'use_url' => $use_url
			);
		}

		$this->tpl->add_array('avatars',$avatars,false);

		$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'={d}');
		$this->functions->add_pagination($pagination,$url);

		$current_avatar = BS_UserUtils::get_instance()->get_profile_avatar(
			$this->user->get_profile_val('avatar'),$this->user->get_user_id()
		);
		if($current_avatar != $this->locale->lang('nopictureavailable'))
		{
			$url = $this->url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_AT.'='.BS_ACTION_REMOVE_AVATAR
					.'&amp;'.BS_URL_SITE.'='.$site,'&amp;',true
			);
			$delete_avatar = '<br /><br /><a href="'.$url.'">'.$this->locale->lang('remove_avatar').'</a>';
		}
		else
			$delete_avatar = '';

		$cfg = $this->cfg;
		$this->tpl->add_array('CFG',$cfg,false);
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site),
			'action_type' => BS_ACTION_UPLOAD_AVATAR,
			'current_avatar' => $current_avatar,
			'delete_avatar' => $delete_avatar
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('avatars') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=avatars')
		);
	}
}
?>
<?php
/**
 * Contains the avatars-userprofile-submodule
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The avatars submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_avatars extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_UPLOAD_AVATAR,'uploadavatar');
		$renderer->add_action(BS_ACTION_USE_AVATAR,'useavatar');
		$renderer->add_action(BS_ACTION_DELETE_AVATAR,'deleteavatars');
		$renderer->add_action(BS_ACTION_REMOVE_AVATAR,'removeavatar');

		$renderer->add_breadcrumb($locale->lang('avatars'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();

		// has the user the permission to configure the avatars?
		if(!$user->is_loggedin() || $cfg['enable_avatars'] == 0)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		if($site == null || $site < 1)
			$site = 1;

		if(($delete = $input->get_var('del','post')) != null && FWS_Array_Utils::is_integer($delete))
		{
			$id_str = implode(',',$delete);
			$url = BS_URL::get_sub_url('userprofile','avatars');
			$url->set(BS_URL_SITE,$site);
			$no_url = $url->to_url();
			
			$url->set(BS_URL_AT,BS_ACTION_DELETE_AVATAR);
			$url->set(BS_URL_DEL,$id_str);
			$url->set_sid_policy(BS_URL::SID_FORCE);
			
			$target = BS_URL::get_mod_url('redirect');
			$target->set(BS_URL_LOC,'del_avatars');
			$target->set(BS_URL_ID,$id_str);
			$target->set(BS_URL_SITE,$site);
			
			$names = array();
			foreach(BS_DAO::get_avatars()->get_by_ids($delete) as $data)
				$names[] = $data['av_pfad'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_avatars_message'),$namelist),
				$url->to_url(),$no_url,$target->to_url()
			);
		}

		$num = BS_DAO::get_avatars()->get_count_for_user($user->get_user_id());
		$pagination = new BS_Pagination(BS_AVATARS_PER_PAGE,$num);
		
		$url = BS_URL::get_sub_url('userprofile','avatars');
		$url->set(BS_URL_SITE,$site);
		
		$tpl->add_variables(array(
			'target_url' => $url->to_url(),
			'num' => $num
		));

		$url->set(BS_URL_AT,BS_ACTION_USE_AVATAR);
		$url->set_sid_policy(BS_URL::SID_FORCE);
		
		$avatars = array();
		$avlist = BS_DAO::get_avatars()->get_list_for_user(
			$user->get_user_id(),$pagination->get_start(),BS_AVATARS_PER_PAGE
		);
		foreach($avlist as $index => $data)
		{
			if($data['user'] == 0)
				$delete = $locale->lang('notavailable');
			else
			{
				$delete = '<input type="checkbox" id="avatar_'.$index.'" name="del[]"';
				$delete .= ' value="'.$data['id'].'" onclick="this.checked = this.checked ? false : true;" />';
			}
			
			$avatars[] = array(
				'user_name' => ($data['user'] == 0) ? $locale->lang('administrator') : $data['user_name'],
				'delete' => $delete,
				'avatar_path' => FWS_Path::client_app().'images/avatars/'.$data['av_pfad'],
				'display_path' => FWS_String::substr($data['av_pfad'],0,25).((FWS_String::strlen($data['av_pfad']) > 25) ? '...' : ''),
				'use_url' => $url->set(BS_URL_ID,$data['id'])->to_url()
			);
		}

		$tpl->add_variable_ref('avatars',$avatars);

		$pagination->populate_tpl(BS_URL::get_sub_url());

		$current_avatar = BS_UserUtils::get_profile_avatar(
			(int)$user->get_profile_val('avatar'),$user->get_user_id()
		);
		if($current_avatar != $locale->lang('nopictureavailable'))
		{
			$url->set(BS_URL_AT,BS_ACTION_REMOVE_AVATAR);
			$delete_avatar = '<br /><br /><a href="'.$url->to_url().'">'.$locale->lang('remove_avatar').'</a>';
		}
		else
			$delete_avatar = '';

		$tpl->add_variable_ref('CFG',$cfg);
		$tpl->add_variables(array(
			'target_url' => $url->remove(BS_URL_AT)->to_url(),
			'action_type' => BS_ACTION_UPLOAD_AVATAR,
			'current_avatar' => $current_avatar,
			'delete_avatar' => $delete_avatar
		));
	}
}
?>
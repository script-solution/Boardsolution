<?php
/**
 * Contains the avatars-userprofile-submodule
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_UPLOAD_AVATAR,'uploadavatar');
		$renderer->add_action(BS_ACTION_USE_AVATAR,'useavatar');
		$renderer->add_action(BS_ACTION_DELETE_AVATAR,'deleteavatars');
		$renderer->add_action(BS_ACTION_REMOVE_AVATAR,'removeavatar');

		$renderer->add_breadcrumb($locale->lang('avatars'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=avatars'));
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
		$url = FWS_Props::get()->url();
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
			$loc = '&amp;'.BS_URL_LOC.'=avatars';
			$site_param = '&amp;'.BS_URL_SITE.'='.$site;
			$yes_url = $url->get_url(0,$loc.'&amp;'.BS_URL_AT.'='.BS_ACTION_DELETE_AVATAR
																.'&amp;'.BS_URL_DEL.'='.$id_str.$site_param,'&amp;',true);
			$no_url = $url->get_url(0,$loc.$site_param);
			$target = $url->get_url(
				'redirect',
				'&amp;'.BS_URL_LOC.'=del_avatars&amp;'.BS_URL_ID.'='.$id_str.'&amp;'.BS_URL_SITE.'='.$site
			);
			
			$names = array();
			foreach(BS_DAO::get_avatars()->get_by_ids($delete) as $data)
				$names[] = $data['av_pfad'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_avatars_message'),$namelist),
				$yes_url,$no_url,$target
			);
		}

		$num = BS_DAO::get_avatars()->get_count_for_user($user->get_user_id());
		$pagination = new BS_Pagination(BS_AVATARS_PER_PAGE,$num);
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site
			),
			'num' => $num
		));

		$avatars = array();
		$avlist = BS_DAO::get_avatars()->get_list_for_user(
			$user->get_user_id(),$pagination->get_start(),BS_AVATARS_PER_PAGE
		);
		foreach($avlist as $index => $data)
		{
			$use_url = $url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_AT.'='.BS_ACTION_USE_AVATAR
					.'&amp;'.BS_URL_ID.'='.$data['id'].'&amp;'.BS_URL_SITE.'='.$site,'&amp;',true
			);
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
				'use_url' => $use_url
			);
		}

		$tpl->add_array('avatars',$avatars,false);

		$murl = $url->get_url('userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'={d}');
		$functions->add_pagination($pagination,$murl);

		$current_avatar = BS_UserUtils::get_instance()->get_profile_avatar(
			$user->get_profile_val('avatar'),$user->get_user_id()
		);
		if($current_avatar != $locale->lang('nopictureavailable'))
		{
			$murl = $url->get_url(
				'userprofile','&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_AT.'='.BS_ACTION_REMOVE_AVATAR
					.'&amp;'.BS_URL_SITE.'='.$site,'&amp;',true
			);
			$delete_avatar = '<br /><br /><a href="'.$murl.'">'.$locale->lang('remove_avatar').'</a>';
		}
		else
			$delete_avatar = '';

		$tpl->add_array('CFG',$cfg,false);
		$tpl->add_variables(array(
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=avatars&amp;'.BS_URL_SITE.'='.$site),
			'action_type' => BS_ACTION_UPLOAD_AVATAR,
			'current_avatar' => $current_avatar,
			'delete_avatar' => $delete_avatar
		));
	}
}
?>
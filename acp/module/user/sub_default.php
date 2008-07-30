<?php
/**
 * Contains the default-submodule for user
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_user_default extends BS_ACP_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_USER_DELETE,'delete');
		$renderer->add_action(BS_ACP_ACTION_USER_BAN,'ban');
		$renderer->add_action(BS_ACP_ACTION_USER_UNBAN,'unban');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();
		$cache = PLIB_Props::get()->cache();
		$auth = PLIB_Props::get()->auth();
		$user = PLIB_Props::get()->user();
		$url = PLIB_Props::get()->url();

		// reset search?
		if($input->get_var('reset','get',PLIB_Input::INTEGER) == 1)
		{
			$user->delete_session_data('user_search_ids');
			$user->delete_session_data('user_search_params');
		}
		
		// show delete-message
		$type = $input->get_var('action_type','post',PLIB_Input::STRING);
		if(($delete = $input->get_var('delete','post')) != null && $type != 'none')
		{
			// grab get-parameter
			$site = $input->get_var('site','get',PLIB_Input::INTEGER);
			$order = $input->get_var('order','get',PLIB_Input::STRING);
			$ad = $input->get_var('ad','get',PLIB_Input::STRING);
			
			$ids = implode(',',$delete);
			$base_url = $url->get_acpmod_url(0,'&order='.$order.'&ad='.$ad.'&site='.$site,'&');
			if($type == 'block')
			{
				$yes_url = $base_url.'&action=default&at='.BS_ACP_ACTION_USER_BAN.'&ids='.$ids;
				$message = $locale->lang('block_accounts');
			}
			else if($type == 'unblock')
			{
				$yes_url = $base_url.'&action=default&at='.BS_ACP_ACTION_USER_UNBAN.'&ids='.$ids;
				$message = $locale->lang('unblock_accounts');
			}
			else if(!BS_ENABLE_EXPORT)
			{
				$yes_url = $base_url.'&action=default&at='.BS_ACP_ACTION_USER_DELETE.'&ids='.$ids;
				$message = $locale->lang('delete_accounts');
			}
			
			$no_url = $base_url.'&amp;action=results';
			
			$names = array();
			foreach(BS_DAO::get_user()->get_users_by_ids($delete,1,-1) as $data)
				$names[] = $data['user_name'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(sprintf($message,$namelist),$yes_url,$no_url);
		}
		
		$order_vals = array('user','experience','group','blocked','regdate');
		$order = $input->correct_var('order','get',PLIB_Input::STRING,$order_vals,'experience');
		$ad = $input->correct_var('ad','get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		
		$ids = $user->get_session_data('user_search_ids');
		
		$tpl->add_variables(array(
			'is_searching' => $ids !== false,
			'new_search_url' => $url->get_acpmod_url(0,'&amp;action=search'),
			'change_search_url' => $url->get_acpmod_url(0,'&amp;action=search&amp;use_sess=1')
		));
		
		// use the ids or show all?
		if($ids === false || !PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			$ids = false;
		
		switch($order)
		{
			case 'user':
				$sql_order = 'u.`'.BS_EXPORT_USER_NAME.'`';
				break;
			case 'blocked':
				$sql_order = 'p.banned';
				break;
			case 'group':
				$sql_order = 'p.user_group';
				break;
			case 'regdate':
				$sql_order = 'p.registerdate';
				break;
			default:
				$sql_order = 'p.exppoints';
				break;
		}
		
		if($ids === false)
			$num = BS_DAO::get_user()->get_user_count(1);
		else
			$num = count($ids);
		
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		if($ids === false)
		{
			$userlist = BS_DAO::get_profile()->get_users(
				$sql_order,$ad,$pagination->get_start(),$end,1,-1
			);
		}
		else
		{
			$userlist = BS_DAO::get_profile()->get_users_by_ids(
				$ids,$sql_order,$ad,$pagination->get_start(),$end,1,-1
			);
		}
		
		$site = $input->get_var('site','get',PLIB_Input::INTEGER);
		$baseurl = $url->get_acpmod_url(0);
		
		$ad_images = '<a href="'.$baseurl.'&amp;order='.$order.'&amp;ad=ASC">';
		$ad_images .= '<img src="acp/images/asc.gif" alt="ASC" /></a>'."\n";
		$ad_images .= ' <a href="'.$baseurl.'&amp;order='.$order.'&amp;ad=DESC">';
		$ad_images .= '<img src="acp/images/desc.gif" alt="DESC" /></a>'."\n";

		$user_sort = ($order == 'user') ? $ad_images : '';
		$blocked_sort = ($order == 'blocked') ? $ad_images : '';
		$experience_sort = ($order == 'experience') ? $ad_images : '';
		$group_sort = ($order == 'group') ? $ad_images : '';
		$registered_sort = ($order == 'regdate') ? $ad_images : '';

		$group_options = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
				$group_options[$gdata['id']] = $gdata['group_title'];
		}

		$tpl->add_variables(array(
			'baseurl' => $baseurl,
			'target_url' => $baseurl.'&amp;order='.$order.'&amp;ad='.$ad.'&amp;site='.$site,
			'order' => $order,
			'ad' => $ad,
			'user_sort' => $user_sort,
			'blocked_sort' => $blocked_sort,
			'experience_sort' => $experience_sort,
			'registered_sort' => $registered_sort,
			'group_sort' => $group_sort
		));

		$users = array();
		foreach($userlist as $data)
		{
			$user_experience = sprintf(
				$locale->lang('user_experience'),$data['posts'],$data['exppoints']
			);
			
			$edit_url = $url->get_acpmod_url(
				0,'&amp;order='.$order.'&amp;site='.$site.'&amp;ad='.$ad.'&amp;action=edit&amp;id='.$data['id']
			);
			
			$users[] = array(
				'register_date' => PLIB_Date::get_date($data['registerdate'],false),
				'user_experience' => $user_experience,
				'group_combo' => $auth->get_usergroup_list($data['user_group'],false,false,true),
				'edit_url' => $edit_url,
				'id' => $data['id'],
				'user_name' => BS_ACP_Utils::get_instance()->get_userlink($data['id'],$data['user_name']),
				'is_blocked' => BS_ACP_Utils::get_instance()->get_yesno($data['banned'],true,false)
			);
		}

		$tpl->add_variables(array(
			'user' => $users,
			'not_export' => !BS_ENABLE_EXPORT
		));

		$murl = $baseurl.'&amp;order='.$order.'&amp;ad='.$ad.'&amp;site={d}';
		$functions->add_pagination($pagination,$murl);
	}
}
?>
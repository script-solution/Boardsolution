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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
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
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$cache = FWS_Props::get()->cache();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		// reset search?
		if($input->get_var('reset','get',FWS_Input::INTEGER) == 1)
		{
			$user->delete_session_data('user_search_ids');
			$user->delete_session_data('user_search_params');
		}
		
		// show delete-message
		$type = $input->get_var('action_type','post',FWS_Input::STRING);
		if(($delete = $input->get_var('delete','post')) != null && $type != 'none')
		{
			// grab get-parameter
			$site = $input->get_var('site','get',FWS_Input::INTEGER);
			$order = $input->get_var('order','get',FWS_Input::STRING);
			$ad = $input->get_var('ad','get',FWS_Input::STRING);
			$ids = implode(',',$delete);
			$com = BS_Community_Manager::get_instance();
			
			$url = BS_URL::get_acpsub_url();
			$url->set('order',$order);
			$url->set('ad',$ad);
			$url->set('site',$site);
			$url->set('ids',$ids);
			
			if($type == 'block')
			{
				$yes_url = $url->set('at',BS_ACP_ACTION_USER_BAN)->to_url();
				$message = $locale->lang('block_accounts');
			}
			else if($type == 'unblock')
			{
				$yes_url = $url->set('at',BS_ACP_ACTION_USER_UNBAN)->to_url();
				$message = $locale->lang('unblock_accounts');
			}
			else if($com->is_user_management_enabled())
			{
				$yes_url = $url->set('at',BS_ACP_ACTION_USER_DELETE);
				if($type == 'deleteanon')
					$yes_url = $url->set('anonymous',1);
				$yes_url = $yes_url->to_url();
				$message = $locale->lang($type == 'deleteanon' ? 'delete_anon_accounts' : 'delete_accounts');
			}
			
			$nurl = clone $url;
			$nurl->remove('at');
			$nurl->set('action','results');
			$no_url = $nurl->to_url();
			
			$names = array();
			foreach(BS_DAO::get_user()->get_users_by_ids($delete,1,-1) as $data)
				$names[] = $data['user_name'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(sprintf($message,$namelist),$yes_url,$no_url);
		}
		
		$order_vals = array('user','experience','group','blocked','regdate');
		$order = $input->correct_var('order','get',FWS_Input::STRING,$order_vals,'experience');
		$ad = $input->correct_var('ad','get',FWS_Input::STRING,array('ASC','DESC'),'DESC');
		
		$ids = $user->get_session_data('user_search_ids');
		
		$tpl->add_variables(array(
			'is_searching' => $ids !== false,
			'new_search_url' => BS_URL::build_acpsub_url(0,'search'),
			'change_search_url' => BS_URL::get_acpsub_url(0,'search')->set('use_sess',1)->to_url()
		));
		
		// use the ids or show all?
		if($ids === false || !FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
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
		
		$site = $input->get_var('site','get',FWS_Input::INTEGER);
		$baseurl = BS_URL::get_acpmod_url();
		$baseurl->set('order',$order);
		
		$ad_images = '<a href="'.$baseurl->set('ad','ASC')->to_url().'">';
		$ad_images .= '<img src="acp/images/asc.gif" alt="ASC" /></a>'."\n";
		$ad_images .= ' <a href="'.$baseurl->set('ad','DESC')->to_url().'">';
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

		$baseurl->set('ad',$ad);
		$baseurl->set('site',$site);
		
		$tpl->add_variables(array(
			'baseurl' => BS_URL::build_acpmod_url(),
			'target_url' => $baseurl->to_url(),
			'order' => $order,
			'ad' => $ad,
			'user_sort' => $user_sort,
			'blocked_sort' => $blocked_sort,
			'experience_sort' => $experience_sort,
			'registered_sort' => $registered_sort,
			'group_sort' => $group_sort
		));

		$eurl = BS_URL::get_acpsub_url(0,'edit');
		$eurl->set('order',$order);
		$eurl->set('ad',$ad);
		$eurl->set('site',$site);
		
		$users = array();
		foreach($userlist as $data)
		{
			$user_experience = sprintf(
				$locale->lang('user_experience'),$data['posts'],$data['exppoints']
			);
			
			$users[] = array(
				'register_date' => FWS_Date::get_date($data['registerdate'],false),
				'user_experience' => $user_experience,
				'group_combo' => $auth->get_usergroup_list($data['user_group'],false,false,true),
				'edit_url' => $eurl->set('id',$data['id'])->to_url(),
				'id' => $data['id'],
				'user_name' => BS_ACP_Utils::get_userlink($data['id'],$data['user_name']),
				'is_blocked' => BS_ACP_Utils::get_yesno($data['banned'],true,false)
			);
		}

		$tpl->add_variables(array(
			'user' => $users,
			'comman_enabled' => BS_Community_Manager::get_instance()->is_user_management_enabled()
		));

		$pagination->populate_tpl($baseurl);
	}
}
?>
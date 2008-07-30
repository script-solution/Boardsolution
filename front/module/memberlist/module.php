<?php
/**
 * Contains the memberlist-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The memberlist-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_memberlist extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($cfg['enable_memberlist'] == 1 &&
			$auth->has_global_permission('view_memberlist'));
		$renderer->add_breadcrumb($locale->lang('memberlist'),$url->get_url('memberlist'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$functions = PLIB_Props::get()->functions();
		$cfg = PLIB_Props::get()->cfg();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$auth = PLIB_Props::get()->auth();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		// change search-display-state?
		if($input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clapsearch')
			$functions->clap_area('memberlist_search');

		$allowed_order_vals = array('name','lastlogin','posts','user_group','register');
		$order = $input->correct_var(
			BS_URL_ORDER,'get',PLIB_Input::STRING,$allowed_order_vals,'register'
		);
		
		$ad = $input->correct_var(BS_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		
		$allowed_limit_vals = array(5,10,15,30,50,$cfg['members_per_page']);
		$limit = $input->correct_var(
			BS_URL_LIMIT,'get',PLIB_Input::INTEGER,$allowed_limit_vals,$cfg['members_per_page']
		);

		$s_name = $input->get_var(BS_URL_MS_NAME,'get',PLIB_Input::STRING);
		$s_email = $input->get_var(BS_URL_MS_EMAIL,'get',PLIB_Input::STRING);
		$s_group = $input->get_var(BS_URL_MS_GROUP,'get');
		$s_from_posts = $input->get_var(BS_URL_MS_FROM_POSTS,'get',PLIB_Input::INTEGER);
		$s_to_posts = $input->get_var(BS_URL_MS_TO_POSTS,'get',PLIB_Input::INTEGER);
		$s_from_points = $input->get_var(BS_URL_MS_FROM_POINTS,'get',PLIB_Input::INTEGER);
		$s_to_points = $input->get_var(BS_URL_MS_TO_POINTS,'get',PLIB_Input::INTEGER);
		$s_from_reg = PLIB_StringHelper::get_clean_date(
			$input->get_var(BS_URL_MS_FROM_REG,'get',PLIB_Input::STRING)
		);
		$s_to_reg = PLIB_StringHelper::get_clean_date(
			$input->get_var(BS_URL_MS_TO_REG,'get',PLIB_Input::STRING)
		);
		$s_from_lastlogin = PLIB_StringHelper::get_clean_date(
			$input->get_var(BS_URL_MS_FROM_LASTLOGIN,'get',PLIB_Input::STRING)
		);
		$s_to_lastlogin = PLIB_StringHelper::get_clean_date(
			$input->get_var(BS_URL_MS_TO_LASTLOGIN,'get',PLIB_Input::STRING)
		);
		$s_mods = $input->get_var(BS_URL_MS_MODS,'get',PLIB_Input::INT_BOOL);
		
		// build the URL
		$baseurl = $url->get_url('memberlist','&amp;');
		$baseurl .= BS_URL_MS_NAME.'='.$s_name.'&amp;'.BS_URL_MS_EMAIL.'='.$s_email.'&amp;';

		$groups = $cache->get_cache('user_groups');
		foreach($groups as $gdata)
		{
			if(($s_group == null || in_array($gdata['id'],$s_group)) &&
				 $gdata['id'] != BS_STATUS_GUEST && $gdata['is_visible'] == 1)
				$baseurl .= BS_URL_MS_GROUP.urlencode('[]').'='.$gdata['id'].'&amp;';
		}

		$baseurl .= BS_URL_MS_FROM_POSTS.'='.$s_from_posts.'&amp;';
		$baseurl .= BS_URL_MS_TO_POSTS.'='.$s_to_posts.'&amp;';
		$baseurl .= BS_URL_MS_FROM_POINTS.'='.$s_from_points.'&amp;';
		$baseurl .= BS_URL_MS_TO_POINTS.'='.$s_to_points.'&amp;';
		$baseurl .= BS_URL_MS_FROM_REG.'='.$s_from_reg.'&amp;';
		$baseurl .= BS_URL_MS_TO_REG.'='.$s_to_reg.'&amp;';
		$baseurl .= BS_URL_MS_FROM_LASTLOGIN.'='.$s_from_lastlogin.'&amp;';
		$baseurl .= BS_URL_MS_TO_LASTLOGIN.'='.$s_to_lastlogin.'&amp;';
		$baseurl .= BS_URL_MS_MODS.'='.$s_mods.'&amp;';

		$name_col_width = ($cfg['enable_pms'] == 1) ? 20 : 25;

		// build where-statement
		$where = ' WHERE p.active = 1 AND p.banned = 0';
		if($s_name)
			$where .= ' AND u.`'.BS_EXPORT_USER_NAME."` LIKE '%".str_replace('*','%',$s_name)."%'";

		if($s_email)
		{
			$where .= ' AND p.email_display_mode != \'hide\'';
			$where .= ' AND u.`'.BS_EXPORT_USER_EMAIL."` LIKE '%".str_replace('*','%',$s_email)."%'";
		}

		if($s_group != null && PLIB_Array_Utils::is_integer($s_group) && count($s_group) > 0)
		{
			$c = 0;
			$where .= ' AND (';
			$groups = $cache->get_cache('user_groups');
			foreach($s_group as $gid)
			{
				// check if the group is allowed
				$gdata = $groups->get_element($gid);
				if($gdata !== null && $gid != BS_STATUS_GUEST && $gdata['is_visible'] == 1)
				{
					$where .= 'FIND_IN_SET('.$gid.',p.user_group) > 0 OR ';
					$c++;
				}
			}
			
			if($c > 0)
			{
				$where = PLIB_String::substr($where,0,-4);
				$where .= ')';
			}
			else
				$where = PLIB_String::substr($where,0,-6);
		}
		// in this case we don't want to find any user
		else if($s_group != null)
			$where .= ' AND p.user_group = -1';

		// moderators
		if($s_mods)
		{
			$c = 0;
			$where .= ' AND (';
			foreach($cache->get_cache('user_groups') as $gdata)
			{
				if($gdata['is_super_mod'] == 1)
				{
					$where .= 'FIND_IN_SET('.$gdata['id'].',p.user_group) > 0 OR ';
					$c++;
				}
			}
			
			$uids = array();
			foreach($cache->get_cache('moderators') as $intern)
				$uids[] = $intern['user_id'];
			
			if(count($uids) > 0)
			{
				$where .= 'p.id IN ('.implode(',',$uids).') OR ';
				$c++;
			}
			
			if($c > 0)
			{
				$where = PLIB_String::substr($where,0,-4);
				$where .= ')';
			}
			else
				$where = PLIB_String::substr($where,0,-6);
		}
		
		$where .= PLIB_StringHelper::build_int_range_sql('p.posts',$s_from_posts,$s_to_posts);
		$where .= PLIB_StringHelper::build_int_range_sql('p.exppoints',$s_from_points,$s_to_points);

		$where .= PLIB_StringHelper::build_date_range_sql('p.registerdate',$s_from_reg,$s_to_reg);
		$where .= PLIB_StringHelper::build_date_range_sql('p.lastlogin',$s_from_lastlogin,$s_to_lastlogin);

		// check how many entries exist
		$num = BS_DAO::get_user()->get_custom_search_user_count($where);
		
		$colspan = 7;
		if($cfg['enable_pms'] == 0)
			$colspan--;
		if($cfg['enable_post_count'] == 0)
			$colspan--;

		$pagination = new BS_Pagination($limit,$num);
		$user = array();
		
		if($num > 0)
		{
			switch($order)
			{
				case 'lastlogin':
				case 'posts':
				case 'user_group':
					$order_sql = 'p.'.$order;
					break;

				case 'register':
					$order_sql = 'p.registerdate';
					break;

				default:
					$order_sql = 'user_name';
			}

			$userlist = BS_DAO::get_profile()->get_users_by_custom_search(
				$where,$order_sql,$ad,$pagination->get_start(),$limit
			);
			foreach($userlist as $data)
			{
				if($data['allow_board_emails'] == 1 && $cfg['enable_emails'] == 1 &&
						$data['user_email'] != '')
				{
					$email = '<a class="bs_button" style="float: none;" title="';
					$email .= sprintf($locale->lang('send_mail_to_user'),$data['user_name']);
					$email .= '" href="'.$url->get_url('new_mail','&amp;'.BS_URL_ID.'='.$data['id']).'">';
					$email .= $locale->lang('email').'</a>';
				}
				else
				{
					$user_email = BS_UserUtils::get_instance()->get_displayed_email(
						$data['user_email'],$data['email_display_mode'],true
					);
					
					if($data['email_display_mode'] == 'jumble')
					{
						$lhs = new PLIB_HTML_LimitedString($user_email,25);
						$email = '<span title="'.strip_tags($user_email).'">';
						$email .= $lhs->get().'</span>';
					}
					else if($data['email_display_mode'] == 'default')
					{
						$lhs = new PLIB_HTML_LimitedString($user_email,25);
						$email = '<span title="'.$data['user_email'].'">'.$lhs->get().'</span>';
					}
					else
						$email = $user_email;
				}

				if($data['lastlogin'] != 0)
					$lastlogin = PLIB_Date::get_date($data['lastlogin']);
				else
					$lastlogin = $locale->lang('notavailable');
				
				$user[] = array(
					'user_id' => $data['id'],
					'allow_pms' => $data['allow_pms'],
					'name_col_width' => $name_col_width,
					'user_name' => BS_UserUtils::get_instance()->get_link(
						$data['id'],$data['user_name'],$data['user_group']
					),
					'email' => $email,
					'posts' => $data['posts'],
					'lastlogin' => $lastlogin,
					'register_date' => PLIB_Date::get_date($data['registerdate']),
					'user_group' => $auth->get_groupname((int)$data['user_group']),
					'send_pm_title' => sprintf($locale->lang('send_pm_to_user'),$data['user_name'])
				);
			}
		}
		
		$tpl->add_array('user',$user,false);
		
		// display page-split
		$purl = $baseurl.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad.'&amp;'.BS_URL_SITE.'={d}';
		$functions->add_pagination($pagination,$purl);
		
		$tpl->add_variables(array(
			'name_col' => $functions->get_order_column(
				$locale->lang('name'),'name','ASC',$order,$baseurl
			),
			'posts_col' => $functions->get_order_column(
				$locale->lang('posts'),'posts','DESC',$order,$baseurl
			),
			'lastlogin_col' => $functions->get_order_column(
				$locale->lang('lastlogin'),'lastlogin','DESC',$order,$baseurl
			),
			'register_col' => $functions->get_order_column(
				$locale->lang('Registered'),'register','DESC',$order,$baseurl
			),
			'usergroup_col' => $functions->get_order_column(
				$locale->lang('group'),'user_group','ASC',$order,$baseurl
			),
			'name_col_width' => $name_col_width,
			'pms_enabled' => $cfg['enable_pms'] == 1,
			'colspan' => $colspan,
			'enable_post_count' => $cfg['enable_post_count'] == 1
		));

		// display search-form
		$hidden_fields = array();
		$hidden_fields[BS_URL_ACTION] = 'memberlist';
		if(($sid = $url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$hidden_fields = array_merge($hidden_fields,$url->get_extern_vars());
		
		$user_group_options = array();
		$selected_groups = array();
		foreach($cache->get_cache('user_groups') as $data)
		{
			if($data['id'] != BS_STATUS_GUEST && $data['is_visible'] == 1)
			{
				if($s_group == null || in_array($data['id'],$s_group))
					$selected_groups[] = $data['id'];

				$user_group_options[$data['id']] = $data['group_title'];
			}
		}

		$clap_url = $url->get_url(0,'&amp;'.BS_URL_LOC.'=clapsearch');
		$clap_data = $functions->get_clap_data('membersearch',$clap_url,'block');
		
		$form = $this->request_formular(false,false);
		$tpl->add_variables(array(
			'action_param' => BS_URL_ACTION,
			'clap_image' => $clap_data['link'],
			'search_row_params' => $clap_data['divparams'],
			'hidden_fields' => $hidden_fields,
			'search_target' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'name_param' => BS_URL_MS_NAME,
			'email_param' => BS_URL_MS_EMAIL,
			'group_param' => BS_URL_MS_GROUP,
			'from_posts_param' => BS_URL_MS_FROM_POSTS,
			'to_posts_param' => BS_URL_MS_TO_POSTS,
			'from_points_param' => BS_URL_MS_FROM_POINTS,
			'to_points_param' => BS_URL_MS_TO_POINTS,
			'from_reg_param' => BS_URL_MS_FROM_REG,
			'to_reg_param' => BS_URL_MS_TO_REG,
			'from_lastlogin_param' => BS_URL_MS_FROM_LASTLOGIN,
			'to_lastlogin_param' => BS_URL_MS_TO_LASTLOGIN,
			'mod_param' => BS_URL_MS_MODS,
			'mod_value' => $s_mods,
			'order_param' => BS_URL_ORDER,
			'ad_param' => BS_URL_AD,
			'mps_param' => BS_URL_LIMIT,
			'name_value' => stripslashes($s_name),
			'email_value' => stripslashes($s_email),
			'from_posts_value' => $s_from_posts,
			'to_posts_value' => $s_to_posts,
			'from_points_value' => $s_from_points,
			'to_points_value' => $s_to_points,
			'from_reg_value' => $s_from_reg,
			'to_reg_value' => $s_to_reg,
			'from_lastlogin_value' => $s_from_lastlogin,
			'to_lastlogin_value' => $s_to_lastlogin,
			'user_group_combo' => $form->get_combobox(
				BS_URL_MS_GROUP.'[]',$user_group_options,$selected_groups,true,count($user_group_options)
			),
			'reset_url' => $url->get_url(0,'&amp;'.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad),
			'enable_post_count' => $cfg['enable_post_count'] == 1
		));
	}
}
?>
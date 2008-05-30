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
	public function run()
	{
		// change search-display-state?
		if($this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clapsearch')
			$this->functions->clap_area('memberlist_search');

		$allowed_order_vals = array('name','lastlogin','posts','user_group','register');
		$order = $this->input->correct_var(
			BS_URL_ORDER,'get',PLIB_Input::STRING,$allowed_order_vals,'register'
		);
		
		$ad = $this->input->correct_var(BS_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		
		$allowed_limit_vals = array(5,10,15,30,50,$this->cfg['members_per_page']);
		$limit = $this->input->correct_var(
			BS_URL_LIMIT,'get',PLIB_Input::INTEGER,$allowed_limit_vals,$this->cfg['members_per_page']
		);

		$s_name = $this->input->get_var(BS_URL_MS_NAME,'get',PLIB_Input::STRING);
		$s_email = $this->input->get_var(BS_URL_MS_EMAIL,'get',PLIB_Input::STRING);
		$s_group = $this->input->get_var(BS_URL_MS_GROUP,'get');
		$s_from_posts = $this->input->get_var(BS_URL_MS_FROM_POSTS,'get',PLIB_Input::INTEGER);
		$s_to_posts = $this->input->get_var(BS_URL_MS_TO_POSTS,'get',PLIB_Input::INTEGER);
		$s_from_points = $this->input->get_var(BS_URL_MS_FROM_POINTS,'get',PLIB_Input::INTEGER);
		$s_to_points = $this->input->get_var(BS_URL_MS_TO_POINTS,'get',PLIB_Input::INTEGER);
		$s_from_reg = PLIB_StringHelper::get_clean_date(
			$this->input->get_var(BS_URL_MS_FROM_REG,'get',PLIB_Input::STRING)
		);
		$s_to_reg = PLIB_StringHelper::get_clean_date(
			$this->input->get_var(BS_URL_MS_TO_REG,'get',PLIB_Input::STRING)
		);
		$s_from_lastlogin = PLIB_StringHelper::get_clean_date(
			$this->input->get_var(BS_URL_MS_FROM_LASTLOGIN,'get',PLIB_Input::STRING)
		);
		$s_to_lastlogin = PLIB_StringHelper::get_clean_date(
			$this->input->get_var(BS_URL_MS_TO_LASTLOGIN,'get',PLIB_Input::STRING)
		);
		$s_mods = $this->input->get_var(BS_URL_MS_MODS,'get',PLIB_Input::INT_BOOL);
		
		// build the URL
		$url = $this->url->get_url('memberlist','&amp;');
		$url .= BS_URL_MS_NAME.'='.$s_name.'&amp;'.BS_URL_MS_EMAIL.'='.$s_email.'&amp;';

		$groups = $this->cache->get_cache('user_groups');
		foreach($groups as $gdata)
		{
			if(($s_group == null || in_array($gdata['id'],$s_group)) &&
				 $gdata['id'] != BS_STATUS_GUEST && $gdata['is_visible'] == 1)
				$url .= BS_URL_MS_GROUP.urlencode('[]').'='.$gdata['id'].'&amp;';
		}

		$url .= BS_URL_MS_FROM_POSTS.'='.$s_from_posts.'&amp;';
		$url .= BS_URL_MS_TO_POSTS.'='.$s_to_posts.'&amp;';
		$url .= BS_URL_MS_FROM_POINTS.'='.$s_from_points.'&amp;';
		$url .= BS_URL_MS_TO_POINTS.'='.$s_to_points.'&amp;';
		$url .= BS_URL_MS_FROM_REG.'='.$s_from_reg.'&amp;';
		$url .= BS_URL_MS_TO_REG.'='.$s_to_reg.'&amp;';
		$url .= BS_URL_MS_FROM_LASTLOGIN.'='.$s_from_lastlogin.'&amp;';
		$url .= BS_URL_MS_TO_LASTLOGIN.'='.$s_to_lastlogin.'&amp;';
		$url .= BS_URL_MS_MODS.'='.$s_mods.'&amp;';

		$name_col_width = ($this->cfg['enable_pms'] == 1) ? 20 : 25;

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
			$groups = $this->cache->get_cache('user_groups');
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
			foreach($this->cache->get_cache('user_groups') as $gdata)
			{
				if($gdata['is_super_mod'] == 1)
				{
					$where .= 'FIND_IN_SET('.$gdata['id'].',p.user_group) > 0 OR ';
					$c++;
				}
			}
			
			$uids = array();
			foreach($this->cache->get_cache('moderators') as $intern)
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
		if($this->cfg['enable_pms'] == 0)
			$colspan--;
		if($this->cfg['enable_post_count'] == 0)
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
				if($data['allow_board_emails'] == 1 && $this->cfg['enable_emails'] == 1 &&
						$data['user_email'] != '')
				{
					$email = '<a class="bs_button" style="float: none;" title="';
					$email .= sprintf($this->locale->lang('send_mail_to_user'),$data['user_name']);
					$email .= '" href="'.$this->url->get_url('new_mail','&amp;'.BS_URL_ID.'='.$data['id']).'">';
					$email .= $this->locale->lang('email').'</a>';
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
					$lastlogin = $this->locale->lang('notavailable');
				
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
					'user_group' => $this->auth->get_groupname((int)$data['user_group']),
					'send_pm_title' => sprintf($this->locale->lang('send_pm_to_user'),$data['user_name'])
				);
			}
		}
		
		$this->tpl->add_array('user',$user,false);
		
		// display page-split
		$purl = $url.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad.'&amp;'.BS_URL_SITE.'={d}';
		$this->functions->add_pagination($pagination,$purl);
		
		$this->tpl->add_variables(array(
			'name_col' => $this->functions->get_order_column(
				$this->locale->lang('name'),'name','ASC',$order,$url
			),
			'posts_col' => $this->functions->get_order_column(
				$this->locale->lang('posts'),'posts','DESC',$order,$url
			),
			'lastlogin_col' => $this->functions->get_order_column(
				$this->locale->lang('lastlogin'),'lastlogin','DESC',$order,$url
			),
			'register_col' => $this->functions->get_order_column(
				$this->locale->lang('registered'),'register','DESC',$order,$url
			),
			'usergroup_col' => $this->functions->get_order_column(
				$this->locale->lang('group'),'user_group','ASC',$order,$url
			),
			'name_col_width' => $name_col_width,
			'pms_enabled' => $this->cfg['enable_pms'] == 1,
			'colspan' => $colspan,
			'enable_post_count' => $this->cfg['enable_post_count'] == 1
		));

		// display search-form
		$hidden_fields = array();
		$hidden_fields[BS_URL_ACTION] = 'memberlist';
		if(($sid = $this->url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$hidden_fields = array_merge($hidden_fields,$this->url->get_extern_vars());
		
		$user_group_options = array();
		$selected_groups = array();
		foreach($this->cache->get_cache('user_groups') as $data)
		{
			if($data['id'] != BS_STATUS_GUEST && $data['is_visible'] == 1)
			{
				if($s_group == null || in_array($data['id'],$s_group))
					$selected_groups[] = $data['id'];

				$user_group_options[$data['id']] = $data['group_title'];
			}
		}

		$url = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=clapsearch');
		$clap_data = $this->functions->get_clap_data('membersearch',$url,'block');
		
		$form = $this->_request_formular(false,false);
		$this->tpl->add_variables(array(
			'action_param' => BS_URL_ACTION,
			'clap_image' => $clap_data['link'],
			'search_row_params' => $clap_data['divparams'],
			'hidden_fields' => $hidden_fields,
			'search_target' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
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
			'reset_url' => $this->url->get_url(0,'&amp;'.BS_URL_ORDER.'='.$order.'&amp;'.BS_URL_AD.'='.$ad),
			'enable_post_count' => $this->cfg['enable_post_count'] == 1
		));
	}

	public function get_location()
	{
		return array($this->locale->lang('memberlist') => $this->url->get_url('memberlist'));
	}

	public function has_access()
	{
		return $this->cfg['enable_memberlist'] == 1 &&
			$this->auth->has_global_permission('view_memberlist');
	}
}
?>
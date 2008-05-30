<?php
/**
 * Contains the userdetails-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The userdetails-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_userdetails extends BS_Front_Module
{
	public function run()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);

		// check wether the parameter is valid
		if($id == null)
		{
			$this->_report_error();
			return;
		}
	
		$user_data = BS_DAO::get_profile()->get_user_by_id($id);
	
		// check wether the user has been found
		if($user_data === false)
		{
			$this->_report_error();
			return;
		}
		
		$cfields = BS_AddField_Manager::get_instance();
		$fields = $cfields->get_fields_at(BS_UF_LOC_USER_DETAILS);
		$field_list = array();
	
		// determine the number of displayed fields
		$num_displayed_fields = 0;
		foreach($fields as $field)
		{
			$fdata = $field->get_data();
			$val = $user_data['add_'.$fdata->get_name()];
			if($field->is_empty($val))
			{
				if(!$fdata->display_empty())
					continue;
				
				$field_value = $this->locale->lang('notavailable');
			}
			else
				$field_value = $field->get_display($val,'bs_main','bs_main');
			
			$num_displayed_fields++;
			
			$field_list[] = array(
				'field_name' => $field->get_title(),
				'field_value' => $field_value
			);
		}
	
		$rank_data = $this->functions->get_rank_data($user_data['exppoints']);
		
		$this->tpl->add_array('add_fields',$field_list);
	
		if($this->cfg['enable_avatars'] == 1)
		{
			$width = 45;
			$colspan = ' colspan="2"';
			$this->tpl->add_variables(array(
				'rowspan' => 2 + $num_displayed_fields,
				'enable_avatars' => true,
				'avatar' => BS_UserUtils::get_instance()->get_profile_avatar(
					$user_data['avatar'],$user_data['id']
				)
			));
		}
		else
		{
			$width = 80;
			$colspan = '';
			$this->tpl->add_variables(array(
				'rowspan' => 0,
				'enable_avatars' => false,
				'avatar' => ''
			));
		}
	
		$email = BS_UserUtils::get_instance()->get_displayed_email(
			$user_data['user_email'],$user_data['email_display_mode'],true
		);
	
		$this->tpl->add_variables(array(
			'width' => $width,
			'user_name' => $user_data['user_name'],
			'user_email' => $email
		));
		
		// determine signature
		if($user_data['signatur'] != '')
		{
			$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode','sig');
			$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys','sig');
			$bbcode = new BS_BBCode_Parser($user_data['signatur'],'sig',$enable_bbcode,
				$enable_smileys);
			$signature = $bbcode->get_message_for_output();
			BS_PostingUtils::get_instance()->add_default_font($signature,$user_data['default_font']);
		}
		else
			$signature = $this->locale->lang('notavailable');
	
		// gather statistics
		if($user_data['registerdate'] < (time() - 86400))
		{
			$posts_per_day = round(
				$user_data['posts'] / ((time() - $user_data['registerdate']) / 3600 / 24),2
			);
		}
		else
			$posts_per_day = $this->locale->lang('notavailable');
	
		// grab the last posts from the database
		$denied = array();
		if($this->cfg['hide_denied_forums'] == 1)
			$denied = BS_ForumUtils::get_instance()->get_denied_forums(false);
		
		$last_posts = false;
		if(BS_USER_DETAILS_TOPIC_COUNT > 0)
		{
			$postlist = BS_DAO::get_posts()->get_last_posts_of_user(
				$user_data['id'],$denied,BS_USER_DETAILS_TOPIC_COUNT
			);
			$last_posts = array();
			foreach($postlist as $data)
			{
				$last_posts[] = array(
					'date' => PLIB_Date::get_date($data['post_time']),
					'forum_path' => BS_ForumUtils::get_instance()->get_forum_path($data['rubrikid'],false),
					'topic_url' => $this->url->get_url(
						'redirect','&amp;'.BS_URL_LOC.'=show_post&amp;'.BS_URL_ID.'='.$data['id']
					),
					'topic_name' => $data['name']
				);
			}
		}
	
		// collect the options (PM / Email)
		$options = '';
		
		$options .= '<a class="bs_button" style="float: left;" href="';
	  $options .= $this->url->get_url('user_locations').'">';
	  $location = $this->sessions->get_user_location($user_data['id']);
	  if($location != '' && ($user_data['ghost_mode'] == 0 || $this->cfg['allow_ghost_mode'] == 0 ||
	  		$this->user->is_admin()))
	  {
	  	$loc = '';
	  	if($this->auth->has_global_permission('view_online_locations'))
	  	{
	  		$lobj = new BS_Location($location);
				$loc = $lobj->decode(false);
	  	}
	  
	  	$options .= '<span title="'.$loc.'" style="color: #008000;">';
	  	$options .= $this->locale->lang('status_online').'</span>';
	  }
	  else
	  	$options .= '<span style="color: #CC0000;">'.$this->locale->lang('status_offline').'</span>';
	  $options .= '</a>';
		
		if(($this->cfg['display_denied_options'] || $this->user->is_loggedin()) && $this->cfg['enable_pms'] == 1 &&
			$user_data['allow_pms'] == 1)
		{
			$options .= '<a class="bs_button" style="float: left;" title="'.$user_data['user_name'];
			$options .= ' '.$this->locale->lang('sendapm').'" href="';
			$options .= $this->url->get_url('userprofile',
				'&amp;'.BS_URL_LOC.'=pmcompose&amp;'.BS_URL_ID.'='.$user_data['id']);
			$options .= '">'.$this->locale->lang('pm_short').'</a>';
		}
	
		if(($this->cfg['display_denied_options'] || $this->auth->has_global_permission('send_mails')) &&
			 $user_data['allow_board_emails'] == 1 && $this->cfg['enable_emails'] == 1 &&
			 $user_data['user_email'] != '')
		{
			$options .= '<a class="bs_button" style="float: left;" title="';
			$options .= sprintf($this->locale->lang('send_mail_to_user'),$user_data['user_name']);
			$options .= '" href="';
			$options .= $this->url->get_url('new_mail','&amp;'.BS_URL_ID.'='.$user_data['id']).'">';
			$options .= $this->locale->lang('email').'</a>';
		}
	
		if($options == ' ')
			$options = $this->locale->lang('notavailable');

		$user_stats = '';
		if($this->cfg['post_stats_type'] != 'disabled')
		{
			$user_stats = BS_PostingUtils::get_instance()->get_experience_diagram(
				$user_data['exppoints'],$rank_data,$user_data['id']
			);
		}
		
		// display the template
		$pid = '&amp;'.BS_URL_PID.'='.$user_data['id'];
		
		if($user_data['lastlogin'] > 0)
			$lastlogin = PLIB_Date::get_date($user_data['lastlogin']);
		else
			$lastlogin = $this->locale->lang('notavailable');
		
		$this->tpl->add_variables(array(
			'width' => $width,
			'colspan' => $colspan,
			'user_rank' => $rank_data['rank'],
			'experience_diagram' => $user_stats,
			'lastlogin' => $lastlogin,
			'posts_per_day' => $posts_per_day,
			'register_date' => PLIB_Date::get_date($user_data['registerdate'],false),
			'user_groups' => $this->auth->get_usergroup_list($user_data['user_group']),
			'signature' => $signature,
			'last_posts' => $last_posts,
			'search_for_user_posts_url' => $this->url->get_url('search','&amp;'.BS_URL_MODE.'=user_posts'.$pid),
			'search_for_user_topics_url' => $this->url->get_url('search','&amp;'.BS_URL_MODE.'=user_topics'.$pid),
			'options' => $options,
			'enable_search' => ($this->cfg['display_denied_options'] || $this->auth->has_global_permission('view_search')) &&
													$this->cfg['enable_search'],
			'enable_diagram' => $this->cfg['post_stats_type'] != 'disabled' && $this->cfg['enable_post_count'] == 1,
			'enable_user_ranks' => $this->cfg['enable_user_ranks'],
			'enable_post_count' => $this->cfg['enable_post_count'],
			'enable_signatures' => $this->cfg['enable_signatures']
		));
		$this->tpl->add_array('user_data',$user_data);
	}
	
	public function get_location()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		return array(
			$this->locale->lang('userdetails') => $this->url->get_url('userdetails','&amp;'.BS_URL_ID.'='.$id)
		);
	}
	
	public function has_access()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		return $this->user->get_user_id() == $id || $this->auth->has_global_permission('view_userdetails');
	}
}
?>
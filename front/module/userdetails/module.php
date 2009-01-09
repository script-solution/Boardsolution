<?php
/**
 * Contains the userdetails-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The userdetails-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_userdetails extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$renderer->set_has_access($user->get_user_id() == $id ||
			$auth->has_global_permission('view_userdetails'));
		
		$url = BS_URL::get_mod_url('userdetails');
		$url->set(BS_URL_ID,$id);
		$renderer->add_breadcrumb($locale->lang('userdetails'),$url->to_url());
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
		$cfg = FWS_Props::get()->cfg();
		$sessions = FWS_Props::get()->sessions();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);

		// check wether the parameter is valid
		if($id == null)
		{
			$this->report_error();
			return;
		}
	
		$user_data = BS_DAO::get_profile()->get_user_by_id($id);
	
		// check wether the user has been found
		if($user_data === false)
		{
			$this->report_error();
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
				
				$field_value = $locale->lang('notavailable');
			}
			else
				$field_value = $field->get_display($val,'bs_main','bs_main');
			
			$num_displayed_fields++;
			
			$field_list[] = array(
				'field_name' => $field->get_title(),
				'field_value' => $field_value
			);
		}
	
		$rank_data = $functions->get_rank_data($user_data['exppoints']);
		
		$tpl->add_variable_ref('add_fields',$field_list);
	
		if($cfg['enable_avatars'] == 1)
		{
			$width = 45;
			$colspan = ' colspan="2"';
			$tpl->add_variables(array(
				'rowspan' => 2 + $num_displayed_fields,
				'enable_avatars' => true,
				'avatar' => BS_UserUtils::get_profile_avatar(
					$user_data['avatar'],$user_data['id']
				)
			));
		}
		else
		{
			$width = 80;
			$colspan = '';
			$tpl->add_variables(array(
				'rowspan' => 0,
				'enable_avatars' => false,
				'avatar' => ''
			));
		}
	
		$email = BS_UserUtils::get_displayed_email(
			$user_data['user_email'],$user_data['email_display_mode'],true
		);
	
		$tpl->add_variables(array(
			'width' => $width,
			'user_name' => $user_data['user_name'],
			'user_email' => $email
		));
		
		// determine signature
		if($user_data['signatur'] != '')
		{
			$enable_bbcode = BS_PostingUtils::get_message_option('enable_bbcode','sig');
			$enable_smileys = BS_PostingUtils::get_message_option('enable_smileys','sig');
			$bbcode = new BS_BBCode_Parser($user_data['signatur'],'sig',$enable_bbcode,
				$enable_smileys);
			$signature = $bbcode->get_message_for_output();
			BS_PostingUtils::add_default_font($signature,$user_data['default_font']);
		}
		else
			$signature = $locale->lang('notavailable');
	
		// gather statistics
		if($user_data['registerdate'] < (time() - 86400))
		{
			$posts_per_day = round(
				$user_data['posts'] / ((time() - $user_data['registerdate']) / 3600 / 24),2
			);
		}
		else
			$posts_per_day = $locale->lang('notavailable');
	
		// grab the last posts from the database
		$denied = array();
		if($cfg['hide_denied_forums'] == 1)
			$denied = BS_ForumUtils::get_denied_forums(false);
		
		$last_posts = false;
		if(BS_USER_DETAILS_TOPIC_COUNT > 0)
		{
			$rurl = BS_URL::get_mod_url('redirect');
			$rurl->set(BS_URL_LOC,'show_post');
			$postlist = BS_DAO::get_posts()->get_last_posts_of_user(
				$user_data['id'],$denied,BS_USER_DETAILS_TOPIC_COUNT
			);
			$last_posts = array();
			foreach($postlist as $data)
			{
				$last_posts[] = array(
					'date' => FWS_Date::get_date($data['post_time']),
					'forum_path' => BS_ForumUtils::get_forum_path($data['rubrikid'],false),
					'topic_url' => $rurl->set(BS_URL_ID,$data['id'])->to_url(),
					'topic_name' => $data['name']
				);
			}
		}
	
		// collect the options (PM / Email)
		$options = '';
		
		$options .= '<a class="bs_button" style="float: left;" href="';
	  $options .= BS_URL::build_mod_url('user_locations').'">';
	  $location = $sessions->get_user_location($user_data['id']);
	  if($location != '' && ($user_data['ghost_mode'] == 0 || $cfg['allow_ghost_mode'] == 0 ||
	  		$user->is_admin()))
	  {
	  	$loc = '';
	  	if($auth->has_global_permission('view_online_locations'))
	  	{
	  		$lobj = new BS_Location($location);
				$loc = $lobj->decode(false);
	  	}
	  
	  	$options .= '<span title="'.$loc.'" style="color: #008000;">';
	  	$options .= $locale->lang('status_online').'</span>';
	  }
	  else
	  	$options .= '<span style="color: #CC0000;">'.$locale->lang('status_offline').'</span>';
	  $options .= '</a>';
		
		if(($cfg['display_denied_options'] || $user->is_loggedin()) && $cfg['enable_pms'] == 1 &&
			$user_data['allow_pms'] == 1)
		{
			$url = BS_URL::get_sub_url('userprofile','pmcompose');
			$url->set(BS_URL_ID,$user_data['id']);
			$options .= '<a class="bs_button" style="float: left;" title="';
			$options .= sprintf($locale->lang('send_pm_to_user'),$user_data['user_name']);
			$options .= '" href="'.$url->to_url().'">'.$locale->lang('pm_short').'</a>';
		}
	
		if(($cfg['display_denied_options'] || $auth->has_global_permission('send_mails')) &&
			 $user_data['allow_board_emails'] == 1 && $cfg['enable_emails'] == 1 &&
			 $user_data['user_email'] != '')
		{
			$url = BS_URL::get_mod_url('new_mail');
			$url->set(BS_URL_ID,$user_data['id']);
			$options .= '<a class="bs_button" style="float: left;" title="';
			$options .= sprintf($locale->lang('send_mail_to_user'),$user_data['user_name']);
			$options .= '" href="'.$url->to_url().'">';
			$options .= $locale->lang('email').'</a>';
		}
	
		if($options == ' ')
			$options = $locale->lang('notavailable');

		$user_stats = '';
		if($cfg['post_stats_type'] != 'disabled')
		{
			$user_stats = BS_PostingUtils::get_experience_diagram(
				$user_data['exppoints'],$rank_data,$user_data['id']
			);
		}
		
		// display the template
		if($user_data['lastlogin'] > 0)
			$lastlogin = FWS_Date::get_date($user_data['lastlogin']);
		else
			$lastlogin = $locale->lang('notavailable');
		
		$searchurl = BS_URL::get_mod_url('search');
		$searchurl->set(BS_URL_PID,$user_data['id']);
		
		$tpl->add_variables(array(
			'width' => $width,
			'colspan' => $colspan,
			'user_rank' => $rank_data['rank'],
			'experience_diagram' => $user_stats,
			'lastlogin' => $lastlogin,
			'posts_per_day' => $posts_per_day,
			'register_date' => FWS_Date::get_date($user_data['registerdate'],false),
			'user_groups' => $auth->get_usergroup_list($user_data['user_group']),
			'signature' => $signature,
			'last_posts' => $last_posts,
			'search_for_user_posts_url' => $searchurl->set(BS_URL_MODE,'user_posts')->to_url(),
			'search_for_user_topics_url' => $searchurl->set(BS_URL_MODE,'user_topics')->to_url(),
			'options' => $options,
			'enable_search' => ($cfg['display_denied_options'] || $auth->has_global_permission('view_search')) &&
													$cfg['enable_search'],
			'enable_diagram' => $cfg['post_stats_type'] != 'disabled' && $cfg['enable_post_count'] == 1,
			'enable_user_ranks' => $cfg['enable_user_ranks'],
			'enable_post_count' => $cfg['enable_post_count'],
			'enable_signatures' => $cfg['enable_signatures']
		));
		$tpl->add_variable_ref('user_data',$user_data);
	}
}
?>
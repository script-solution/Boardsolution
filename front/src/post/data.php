<?php
/**
 * Contains the post-data class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.post
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Represents one post. Contains the data and many methods to build the values that should be
 * displayed on the posts-page.
 *
 * @package			Boardsolution
 * @subpackage	front.src.post
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Post_Data extends FWS_Object
{
	/**
	 * A cache for profile-data. As soon as we need a value for the profile it will be created
	 * and stored here. The next time we'll simply use this value.
	 * 
	 * @var array
	 */
	private static $_profiles = array();
	
	/**
	 * The post-number
	 *
	 * @var int
	 */
	private $_no;
	
	/**
	 * The posts-container
	 *
	 * @var BS_Front_Post_Container
	 */
	private $_container;
	
	/**
	 * The data of the post. Contents:
	 * <code>
	 * 	array(
	 * 		// posts-table
	 * 		bid,								// the post-id
	 * 		rubrikid,
	 * 		threadid,
	 * 		post_user,
	 * 		post_time,
	 * 		text,								// the text to display (ok, some things have still to be changed..)
	 * 		post_an_user,				// guest-name
	 * 		post_an_mail, 			// guest-email
	 * 		use_smileys,
	 * 		use_bbcode,
	 * 		ip_adresse,
	 * 		edit_lock,					// wether the post is locked for editing of "normal" users
	 * 		edited_times,
	 * 		edited_date,
	 * 		edited_user,
	 * 		text_posted,				// the posted text
	 * 
	 * 		// avatar-table
	 * 		av_pfad,						// the avatar-path
	 * 		aowner,							// owner of the avatar
	 * 		
	 * 		// user-table (edited_user)
	 * 		edited_user_name,
	 * 		edited_user_group,
	 * 
	 * 		// user-table (post_user)
	 * 		user,								// the username for post_user
	 * 		email,							// the email for post_user
	 * 		
	 * 		// profile-table (post_user)
	 * 		bsignature,					// the signature (in display-format)
	 * 		id,									// the id of the user
	 * 		avatar,							// avatar-id
	 * 		registerdate,
	 * 		posts,							// number of posts
	 * 		exppoints,					// experience-points
	 * 		logins,							// number of logins
	 * 		lastlogin,					// timestamp of last login
	 * 		active,
	 * 		banned,
	 * 		default_font,				// the default font
	 * 		allow_pms,					// pms enabled?
	 * 		ghost_mode,
	 * 		attach_signature,
	 * 		allow_board_emails,
	 * 		user_group,
	 * 		email_display_mode,
	 * 		signature_posted,
	 * 	)
	 * </code>
	 *
	 * @var array
	 */
	private $_data;
	
	/**
	 * Constructor
	 *
	 * @param int $no the number
	 * @param BS_Front_Post_Container $container the post-container
	 * @param array $data the data of the post
	 */
	public function __construct($no,$container,$data)
	{
		if(!FWS_Helper::is_integer($no) || $no < 0)
			FWS_Helper::def_error('intge0','no',$no);
		if(!($container instanceof BS_Front_Post_Container))
			FWS_Helper::def_error('instance','container','BS_Front_Post_Container',$container);
		if(!is_array($data))
			FWS_Helper::def_error('array','data',$data);
		
		$this->_no = $no;
		$this->_container = $container;
		$this->_data = $data;
	}
	
	/**
	 * @return boolean wether this is the last post
	 */
	public function is_last_post()
	{
		return $this->_no == $this->_container->get_post_count() - 1;
	}
	
	/**
	 * Calculates the post-number
	 *
	 * @return int the post-number
	 */
	public function get_post_number()
	{
		$cfg = FWS_Props::get()->cfg();

		$page = $this->_container->get_pagination()->get_page();
		return (($page - 1) * $cfg['posts_per_page']) + $this->_no + 1;
	}
	
	/**
	 * Builds the CSS-class for this post
	 *
	 * @param string $name the name: left, bar or main
	 * @return string the CSS-class
	 */
	public function get_css_class($name)
	{
		$var = (($this->_no % 2) == 1) ? 1 : 2;
		return 'bs_posts_'.$name.'_'.$var;
	}
	
	/**
	 * @param string $name the name of the field
	 * @return string the value of the field
	 */
	public function get_field($name)
	{
		if(!isset($this->_data[$name]))
			FWS_Helper::error('The field "'.$name.'" doesn\'t exist!');
		
		return $this->_data[$name];
	}
	
	/**
	 * @param string $hl the value for BS_URL_HL (null = not set)
	 * @return string the URL to this post
	 */
	public function get_post_url($hl = null)
	{
		static $url = null;
		if($url === null)
		{
			$url = BS_URL::get_mod_url('redirect');
			$url->set(BS_URL_LOC,'show_post');
		}
		
		$url->set(BS_URL_ID,$this->get_field('bid'));
		if($hl !== null)
			$url->set(BS_URL_HL,$hl);
		return $url->to_url();
	}
	
	/**
	 * @param boolean $link wether it should be a link
	 * @return string the username of the post-author
	 */
	public function get_username($link = true)
	{
		if($this->_data['post_user'] != 0)
		{
			if($link)
			{
				return BS_UserUtils::get_link(
		  		$this->_data['post_user'],$this->_data['user'],$this->_data['user_group']
		  	);
			}
			
			return $this->_data['user'];
		}
		
		return $this->_data['post_an_user'];
	}
	
	/**
	 * @return string the guest-email to display
	 */
	public function get_guest_email()
	{
		$locale = FWS_Props::get()->locale();

		if($this->_data['post_an_mail'] != '')
    	return $this->_data['post_an_mail'];
    else
    	return $locale->lang('notavailable');
	}
	
	/**
	 * @return string the main-user-group in the corresponding color
	 */
	public function get_user_group()
	{
		$auth = FWS_Props::get()->auth();

		return $auth->get_groupname((int)$this->_data['user_group']);
	}
	
	/**
	 * @return string the user-statistics
	 */
	public function get_user_stats()
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();

		if(isset(self::$_profiles[$this->_data['id']]['stats']))
			return self::$_profiles[$this->_data['id']]['stats'];
		
		$stats = '';
		if($this->_data['post_user'] != 0 && $cfg['enable_post_count'] == 1)
		{
			$rank = $this->get_rank();
			$stats .= BS_PostingUtils::get_experience_diagram(
				$this->_data['exppoints'],$rank,$this->_data['post_user']
			);
			$stats .= '<br />';

    	if($cfg['enable_user_ranks'] == 1)
    		$stats .= '<i>'.$rank['rank'].'</i> '.$locale->lang('with').' ';
    	$stats .= $this->_data['exppoints'].' '.$locale->lang('points').', ';
    	$stats .= $this->_data['posts'].' '.$locale->lang('posts');
    }
    
    self::$_profiles[$this->_data['id']]['stats'] = $stats;
    return $stats;
	}
	
	/**
	 * @return string the ip-address of the user
	 */
	public function get_user_ip()
	{
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();

		if(isset(self::$_profiles[$this->_data['id']]['userstatus']))
			return self::$_profiles[$this->_data['id']]['userstatus'];
		
		if($auth->has_global_permission('view_user_ip') && $this->_data['ip_adresse'] != '')
			$status = $this->_data['ip_adresse'];
		else
			$status = $locale->lang('notavailable');
		
		self::$_profiles[$this->_data['id']]['userstatus'] = $status;
		return $status;
	}
	
	/**
	 * @return string The status of the user
	 */
	public function get_user_status()
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();

		if($cfg['enable_user_ranks'] == 1 && $this->_data['post_user'] > 0)
			return '';
		
		$group_id = $this->_data['post_user'] > 0 ? (int)$this->_data['user_group'] : BS_STATUS_GUEST;
		return $auth->get_colored_groupname($group_id);
	}
	
	/**
	 * @return string the register-date of the posting-user
	 */
	public function get_register_date()
	{
		if(isset(self::$_profiles[$this->_data['id']]['regdate']))
			return self::$_profiles[$this->_data['id']]['regdate'];
		
		$date = FWS_Date::get_date($this->_data['registerdate']);
		self::$_profiles[$this->_data['id']]['regdate'] = $date;
		return $date;
	}
	
	/**
	 * @return array the data of the rank of the posting-user
	 */
	public function get_rank()
	{
		$functions = FWS_Props::get()->functions();

		if(isset(self::$_profiles[$this->_data['id']]['rank']))
			return self::$_profiles[$this->_data['id']]['rank'];
		
		$rank = $functions->get_rank_data($this->_data['exppoints']);
		self::$_profiles[$this->_data['id']]['rank'] = $rank;
		return $rank;
	}
	
	/**
	 * @return string the title for the rank / usergroup
	 */
	public function get_rank_title()
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();

		if($cfg['enable_user_ranks'] == 1 && $this->_data['post_user'] > 0)
			return $locale->lang('rank');
		
		return $locale->lang('user_group');
	}
	
	/**
	 * @return string the rank-images to display for the posting-user
	 */
	public function get_rank_images()
	{
		$cache = FWS_Props::get()->cache();
		$functions = FWS_Props::get()->functions();

		if(isset(self::$_profiles[$this->_data['id']]['rankimg']))
			return self::$_profiles[$this->_data['id']]['rankimg'];
		
		if($this->_data['post_user'] > 0)
		{
			$rank = $this->get_rank();
			$rank_num = $cache->get_cache('user_ranks')->get_element_count();
			$images = $functions->get_rank_images(
			 	$rank_num,$rank['pos'],$this->_data['id'],$this->_data['user_group']
			);
		}
		else
			$images = '';
		
		self::$_profiles[$this->_data['id']]['rankimg'] = $images;
		return $images;
	}
	
	/**
	 * @return boolean wether the avatar should be displayed
	 */
	public function show_avatar()
	{
		return $this->_data['post_user'] != 0 && $this->get_avatar() != '';
	}
	
	/**
	 * @return string the avatar to display for the posting-user
	 */
	public function get_avatar()
	{
		$cfg = FWS_Props::get()->cfg();

		if(isset(self::$_profiles[$this->_data['id']]['avatar']))
			return self::$_profiles[$this->_data['id']]['avatar'];
		
		$avatar = '';
		if($cfg['enable_avatars'] == 1)
			$avatar = BS_UserUtils::get_avatar_path($this->_data);
		self::$_profiles[$this->_data['id']]['avatar'] = $avatar;
		return $avatar;
	}
	
	/**
	 * @return array the additional-fields to display
	 */
	public function get_additional_fields()
	{
		$locale = FWS_Props::get()->locale();

		$fields = array();
	  if($this->_data['post_user'] != 0)
	  {
	  	$left_class = $this->get_css_class('left');
	  	foreach($this->_container->get_additional_fields() as $field)
	    {
	    	$fdata = $field->get_data();
				$val = $this->_data['add_'.$fdata->get_name()];
				if($field->is_empty($val))
				{
					if(!$fdata->display_empty())
						continue;

					$field_value = $locale->lang('notavailable');
				}
				else
					$field_value = $field->get_display($val,$left_class,$left_class,30);

				$fields[$fdata->get_name()] = array(
					'field_name' => $field->get_title(),
					'field_value' => $field_value
				);
			}
	  }
	  
	  return $fields;
	}
	
	/**
	 * Returns the text of this post
	 * 
	 * @param boolean $attachments do you want to show the attachments?
	 * @param boolean $signatures do you want to show the signature?
	 * @param boolean $edit_notice do you want to show the edited-notice?
	 * @param boolean $wordwrap_codes do you want to perform a wordwrap in code-sections?
	 * @return string the post-text
	 */
	public function get_post_text($attachments = true,$signatures = true,$edit_notice = true,
		$wordwrap_codes = false)
	{
		$attlist = $attachments ? $this->_container->get_attachments() : array();
		$keywords = $this->_container->get_highlight_keywords();
		return BS_PostingUtils::get_post_text(
	  	$this->_data,$keywords === false ? null : $keywords,$attachments,$signatures,$edit_notice,
	  	$attlist,$wordwrap_codes
	  );
	}
	
	/**
	 * @return string the post-buttons to display
	 */
	public function get_post_buttons()
	{
		$auth = FWS_Props::get()->auth();
		$forums = FWS_Props::get()->forums();
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		static $cache = null;
		if($cache === null)
		{
			$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
			$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
			$site = $this->_container->get_pagination()->get_page();
			
			$dpurl = BS_URL::get_mod_url('delete_post');
    	$dpurl->set(BS_URL_FID,$fid);
    	$dpurl->set(BS_URL_TID,$tid);
    	
    	$epurl = BS_URL::get_mod_url('edit_post');
    	$epurl->copy_params($dpurl,array(BS_URL_FID,BS_URL_TID));
    	$epurl->set(BS_URL_SITE,$site);
    	
    	$qurl = BS_URL::get_mod_url('new_post');
    	$qurl->copy_params($epurl,array(BS_URL_FID,BS_URL_TID,BS_URL_SITE));
    	
    	$ajaxqurl = BS_URL::get_standalone_url('ajax_quote','&');
    	$ajaxqurl->set(BS_URL_ID,'__ID__');
	    		
    	$cache = array(
				'can_reply'					=> $auth->has_current_forum_perm(BS_MODE_REPLY),
				'fclosed'						=> $forums->forum_is_closed($this->_data['rubrikid']),
				'fid'								=> $fid,
				'tid'								=> $tid,
				'action'						=> $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING),
				'site'							=> $site,
				'total_pages'				=> $this->_container->get_pagination()->get_page_count(),
				'total_posts'				=> $this->_container->get_post_count(),
				'topic'							=> BS_Front_TopicFactory::get_current_topic(),
				'posts_order'				=> BS_PostingUtils::get_posts_order(),
				'is_admin'					=> $user->is_admin(),
    		'delete_post_url'		=> $dpurl,
    		'edit_post_url'			=> $epurl,
    		'quote_url'					=> $qurl,
    		'ajax_qurl'					=> $ajaxqurl->to_url()
			);
		}
		
		$btns = '';
	  if($cache['action'] == 'posts' && ($cache['is_admin'] || !$cache['fclosed']))
	  {
	    // delete
	    if(($this->_no == 0 && $cache['site'] == 1 && $cache['posts_order'] == 'ASC') ||
	    	 ($this->_no == $cache['total_posts'] - 1 && $cache['site'] == $cache['total_pages'] &&
	    	 	$cache['posts_order'] == 'DESC'))
	    {
	    	// delete topic
	    	if($cfg['display_denied_options'] ||
	    		 $auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS,$this->_data['post_user']))
	    	{
	    		$murl = BS_URL::get_mod_url('delete_topics');
	    		$murl->set(BS_URL_FID,$cache['fid']);
	    		$murl->set(BS_URL_ID,$cache['tid']);
		    	$btns .= '<a class="bs_button_big" title="'.$locale->lang('delete_topic');
					$btns .= '" href="'.$murl->to_url().'">'.$locale->lang('delete_topic').'</a>';
	    	}
	    }
	    else
	    {
	    	// delete post
	    	if($cfg['display_denied_options'] ||
	    		 ($auth->has_current_forum_perm(BS_MODE_DELETE_POSTS,$this->_data['post_user']) &&
	    		  ($cache['is_admin'] || $cache['topic']['thread_closed'] == 0)))
	    	{
	    		$murl = $cache['delete_post_url'];
	    		$murl->set(BS_URL_ID,$this->_data['bid']);
		    	$btns .= '<a title="'.$locale->lang('deletepost').'" class="bs_button" href="';
		    	$btns .= $murl->to_url().'">'.$locale->lang('delete').'</a>';
	    	}
	    }
	
	    // edit post
	    if($cfg['display_denied_options'] ||
	    	 ($auth->has_current_forum_perm(BS_MODE_EDIT_POST,$this->_data['post_user']) &&
	    	  ($cache['is_admin'] || $cache['topic']['thread_closed'] == 0)))
	    {
	    	$murl = $cache['edit_post_url'];
    		$murl->set(BS_URL_ID,$this->_data['bid']);
		    $btns .= '<a class="bs_button" title="'.$locale->lang('editpost');
				$btns .= '" href="'.$murl->to_url().'">'.$locale->lang('edit').'</a>';
	    }
	
	  	// quote button
	    if(($cfg['display_denied_options'] || $cache['can_reply']) &&
	    	$cache['topic']['comallow'] == 1 &&
	    	($cache['is_admin'] || $cache['topic']['thread_closed'] == 0))
	    {
	    	$murl = $cache['quote_url'];
	    	$murl->set(BS_URL_PID,$this->_data['bid']);
	      $btns .= '<a id="quote_link_'.$this->_data['bid'].'" class="bs_button"';
	      $btns .= ' title="'.$locale->lang('quotethispost').'"';
				$btns .= ' href="'.$murl->to_url().'" onclick="toggleQuote(\''.$cache['ajax_qurl'].'\',';
				$btns .= $this->_data['bid'].'); return false;">'.$locale->lang('quote').'+</a>';
	    }
	  }
	  
	  return $btns;
	}
	
	/**
	 * @return string the profile-buttons to display
	 */
	public function get_profile_buttons()
	{
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$sessions = FWS_Props::get()->sessions();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();

		static $cache = null;
		if($cache === null)
		{
			$cache = array(
				'view_online'				=> $auth->has_global_permission('view_online_locations'),
				'send_mails'				=> $auth->has_global_permission('send_mails'),
				'is_admin'					=> $user->is_admin(),
				'is_loggedin'				=> $user->is_loggedin(),
				'mail_url'					=> BS_URL::get_mod_url('new_mail'),
				'pm_url'						=> BS_URL::get_sub_url('userprofile','pmcompose'),
				'user_loc_url'			=> BS_URL::build_mod_url('user_locations')
			);
		}
		
		$btns = '';
	
	  $btns .= '<a class="bs_button" style="float: left;" href="'.$cache['user_loc_url'].'">';
	  $location = $sessions->get_user_location($this->_data['post_user']);
		if($this->_data['post_user'] > 0 && $location != '' && ($this->_data['ghost_mode'] == 0 ||
				$cfg['allow_ghost_mode'] == 0 || $cache['is_admin']))
		{
			$loc = '';
			if($cache['view_online'])
			{
				$lobj = new BS_Location($location);
				$loc = $lobj->decode(false);
			}
	
	  	$btns .= '<span title="'.$loc.'" style="color: #008000;">';
	  	$btns .= $locale->lang('status_online').'</span>';
	  }
	  else
	  	$btns .= '<span style="color: #CC0000;">'.$locale->lang('status_offline').'</span>';
	  $btns .= '</a>';
	
		// email button
	  if($cfg['enable_emails'] == 1 && FWS_StringHelper::is_valid_email($this->_data['email']) &&
			 $this->_data['allow_board_emails'] == 1 &&
			 ($cfg['display_denied_options'] || $cache['send_mails']))
	  {
	  	$murl = $cache['mail_url'];
	  	$murl->set(BS_URL_ID,$this->_data['post_user']);
	  	$btns .= '<a class="bs_button" style="float: left;" title="';
	  	$btns .= sprintf($locale->lang('send_mail_to_user'),$this->_data['user']);
	  	$btns .= '" href="'.$murl->to_url().'">'.$locale->lang('email').'</a>';
	  }
	
	  // pm button
	  if($cfg['enable_pms'] == 1 && $this->_data['post_user'] != 0 &&
	  	 $this->_data['allow_pms'] == 1 && ($cfg['display_denied_options'] || $cache['is_loggedin']))
	  {
	  	$murl = $cache['pm_url'];
	  	$murl->set(BS_URL_ID,$this->_data['post_user']);
	    $btns .= '<a class="bs_button" style="float: left;" title="';
			$btns .= sprintf($locale->lang('send_pm_to_user'),$this->_data['user']).'"';
	    $btns .= ' href="'.$murl->to_url().'">'.$locale->lang('pm_short').'</a>';
	  }
	  
	  return $btns;
	}
	
	/**
	 * @return boolean wether this post is unread
	 */
	public function is_unread()
	{
		$unread = FWS_Props::get()->unread();

		if($unread->is_unread_thread($this->_data['threadid']))
		{
			$first_unread = $unread->get_first_unread_post($this->_data['threadid']);
	    return $this->_data['bid'] >= $first_unread;
		}
		return false;
	}
	
	/**
	 * @return string the image that should be displayed for the post (indicates read or unread)
	 */
	public function get_post_image()
	{
		$unread = FWS_Props::get()->unread();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		// determine post unread image
	  if($unread->is_unread_thread($this->_data['threadid']))
	  {
			$first_unread = $unread->get_first_unread_post($this->_data['threadid']);
	    if($this->_data['bid'] >= $first_unread)
	    {
	    	$image = $user->get_theme_item_path('images/unread/post_unread.gif');
	      $img = '<img alt="'.$locale->lang('newentry').'" title="';
	      $img .= $locale->lang('newentry').'"';
	      $img .= ' src="'.$image.'" />';
	    }
	    else
	    {
	    	$image = $user->get_theme_item_path('images/unread/post_read.gif');
	      $img = '<img alt="'.$locale->lang('nonewentry').'" title="';
	      $img .= $locale->lang('nonewentry').'"';
	      $img .= ' src="'.$image.'" />';
	    }
	  }
	  else
	  {
	    $image = $user->get_theme_item_path('images/unread/post_read.gif');
	    $img = '<img alt="'.$locale->lang('nonewentry').'" title="';
	    $img .= $locale->lang('nonewentry').'"';
	    $img .= ' src="'.$image.'" />';
	  }
	  
	  return $img;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
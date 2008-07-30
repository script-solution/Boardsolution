<?php
/**
 * Contains the redirect-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The redirect-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_redirect extends BS_Front_Module
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$doc = FWS_Props::get()->doc();
		$cfg = FWS_Props::get()->cfg();
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$loc = $input->get_var(BS_URL_LOC,'get',FWS_Input::STRING);

		switch($loc)
		{
			// PMs: naviate back and forward
			case 'pm_navigate':
				$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
				$mode = $input->correct_var(
					BS_URL_MODE,'get',FWS_Input::STRING,array('back','forward'),'back'
				);
				$location = $input->correct_var(
					BS_URL_KW,'get',FWS_Input::STRING,array('inbox','outbox'),'inbox'
				);
				
				if($id == null || !$user->is_loggedin())
				{
					$this->report_error();
					return;
				}
				
				if($mode == 'back')
					$pmid = BS_DAO::get_pms()->get_prev_pm_id_of_user($id,$user->get_user_id(),$location);
				else
					$pmid = BS_DAO::get_pms()->get_next_pm_id_of_user($id,$user->get_user_id(),$location);
				
				if($pmid === false)
				{
					$this->report_error(
						FWS_Document_Messages::ERROR,$locale->lang('pm_navigation_failed_'.$mode)
					);
					return;
				}
				
				$doc->redirect(
					BS_URL::get_url('userprofile','&'.BS_URL_LOC.'=pmdetails&'.BS_URL_ID.'='.$pmid,'&')
				);
				break;
			
			// show topic
			case 'show_topic':
				$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
				if($tid == null)
				{
					$this->report_error();
					return;
				}
				
				$tdata = BS_DAO::get_topics()->get_by_id($tid);
				if($tdata === false)
				{
					$this->report_error();
					return;
				}
				
				$doc->redirect(
					BS_URL::get_url('posts','&'.BS_URL_FID.'='.$tdata['rubrikid'].'&'.BS_URL_TID.'='.$tid,'&')
				);
				break;
			
			// show post
			case 'show_post':
				$pid = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
				$hl = $input->get_var(BS_URL_HL,'get',FWS_Input::STRING);
				if($pid == null)
				{
					$this->report_error();
					return;
				}
				
				$pdata = BS_DAO::get_posts()->get_post_by_id($pid);
				if($pdata === false)
				{
					$this->report_error();
					return;
				}
				
				$hl_add = '';
				if($hl !== null)
				{
					$hl = stripslashes(FWS_StringHelper::htmlspecialchars_back($hl));
					$hl_add = '&'.BS_URL_HL.'='.urlencode($hl);
				}
				
				$post_index = 0;
				$page = 1;
				$postlist = BS_DAO::get_posts()->get_all_from_topic(
					$pdata['rubrikid'],$pdata['threadid'],'id',BS_PostingUtils::get_instance()->get_posts_order()
				);
				foreach($postlist as $data)
				{
					if($data['id'] == $pid)
					{
						$murl = BS_URL::get_url(
							'posts','&'.BS_URL_FID.'='.$pdata['rubrikid']
							.'&'.BS_URL_TID.'='.$pdata['threadid'].'&'.BS_URL_SITE.'='.$page.$hl_add,'&'
						);
						$doc->redirect($murl.'#b_'.$pid);
					}
		
					$post_index++;
					if(($post_index % $cfg['posts_per_page']) == 0)
						$page++;
				}
				break;
			
			// redirect to the corresponding posts-action
			// TODO is this still used?
			case 'posts_action':
				$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
	
				// check the selected posts
				$posts = $input->get_var('selected_posts','post');
				if(!FWS_Array_Utils::is_integer($posts))
				{
					$this->report_error();
					return;
				}
	
				$type = $input->correct_var('posts_action','post',FWS_Input::STRING,
					array('delete_posts','split_posts'),'delete_posts');
				$murl = BS_URL::get_url(
					$type,'&'.BS_URL_FID.'='.$fid.'&'.BS_URL_TID.'='.$tid.'&'.BS_URL_ID.'='.implode(',',$posts),'&'
				);
				$doc->redirect($murl);
				break;

			// redirect to the corresponding topic-action
			case 'topic_action':
				// check the selected topics
				$selected_topics = $input->get_var('selected_topics','post');
				if(!FWS_Array_Utils::is_integer($selected_topics))
				{
					$this->report_error();
					return;
				}
	
				$ids = implode(',',$selected_topics);
	
				// build the url
				$murl = '';
				$topic_action = $input->get_var('topic_action','post',FWS_Input::STRING);
				switch($topic_action)
				{
					case 'edit':
						$murl = BS_URL::get_url(
							'edit_topic','&'.BS_URL_FID.'='.$fid.'&'.BS_URL_ID.'='.$ids,'&'
						);
						break;
					case 'open':
						$murl = BS_URL::get_url(
							'openclose_topics','&'.BS_URL_MODE.'=open&'.BS_URL_FID.'='.$fid.'&'.BS_URL_ID.'='.$ids,'&'
						);
						break;
					case 'close':
						$murl = BS_URL::get_url(
							'openclose_topics','&'.BS_URL_MODE.'=close&'.BS_URL_FID.'='.$fid.'&'.BS_URL_ID.'='.$ids,'&'
						);
						break;
					case 'delete':
						$murl = BS_URL::get_url(
							'delete_topics','&'.BS_URL_FID.'='.$fid.'&'.BS_URL_ID.'='.$ids,'&'
						);
						break;
					case 'move':
						$murl = BS_URL::get_url(
							'move_topics','&'.BS_URL_FID.'='.$fid.'&'.BS_URL_ID.'='.$ids,'&'
						);
						break;
					case 'mark_read':
						$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
						$action_type = BS_URL_AT.'='.BS_ACTION_CHANGE_READ_STATUS;
						$fid_param = ($fid != null) ? '&'.BS_URL_FID.'='.$fid : '';
						$murl = BS_URL::get_url(
							0,'&'.$action_type.'&'.BS_URL_LOC.'=read&'.BS_URL_MODE.'=topics'
							.$fid_param.'&'.BS_URL_ID.'='.$ids.'&'.BS_URL_SITE.'='.$site,'&',true
						);
						break;
					case 'mark_unread':
						$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
						$action_type = BS_URL_AT.'='.BS_ACTION_CHANGE_READ_STATUS;
						$fid_param = ($fid != null) ? '&'.BS_URL_FID.'='.$fid : '';
						$murl = BS_URL::get_url(
							0,'&'.$action_type.'&'.BS_URL_LOC.'=unread&'.BS_URL_MODE.'=topics'
							.$fid_param.'&'.BS_URL_ID.'='.$ids.'&'.BS_URL_SITE.'='.$site,'&',true
						);
						break;
				}
	
				// invalid mode?
				if($murl == '')
				{
					$this->report_error();
					return;
				}
	
				$doc->redirect($murl);
				break;
	
			// delete-messages in the user-profile
			case 'pms':
				// check ids
				$ids = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
				$id_array = explode(',',$ids);
				if(!FWS_Array_Utils::is_integer($id_array))
				{
					$this->report_error();
					return;
				}
				
				$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
				$mode = $input->get_var(BS_URL_MODE,'get',FWS_Input::STRING);
				$operation = $input->get_var('operation','post',FWS_Input::STRING);
				
				// check other parameter
				if($mode == null || $operation == null)
				{
					$this->report_error();
					return;
				}
				
				switch($operation)
				{
					case 'mark_read':
						$action_type = '&'.BS_URL_AT.'='.BS_ACTION_MARK_PMS_READ;
						break;
					
					case 'mark_unread':
						$action_type = '&'.BS_URL_AT.'='.BS_ACTION_MARK_PMS_UNREAD;
						break;
					
					case 'delete':
						$action_type = '&'.BS_URL_MODE.'=delete';
						break;
					
					default:
						$this->report_error();
						return;
				}
				
				// redirect
				$params = '&'.BS_URL_LOC.'='.$mode.'&'.BS_URL_SITE.'='.$site.$action_type.'&'.BS_URL_DEL.'='.$ids;
				$murl = BS_URL::get_url('userprofile',$params,'&');
				$doc->redirect($murl);
				break;
	
			// delete-messages at other locations
			case 'del_pm_ban':
			case 'del_pms':
			case 'del_subscr':
			case 'del_avatars':
			case 'del_cal_event':
				$ids = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
				$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
				$option = $input->isset_var('option_yes','post') ? 'yes' : 'no';
				switch($loc)
				{
					case 'del_subscr':
						$loc_param = '&'.BS_URL_LOC.'=pr_subt';
						$action_type = BS_ACTION_UNSUBSCRIBE_TOPIC;
						break;
					
					case 'del_avatars':
						$loc_param = '&'.BS_URL_LOC.'=pr_avatars';
						$action_type = BS_ACTION_DELETE_AVATAR;
						break;
					
					case 'del_pms':
						$loc_param = '&'.BS_URL_LOC.'=pmoverview';
						$action_type = BS_ACTION_DELETE_PMS;
						break;
					
					case 'del_pm_ban':
						$loc_param = '&'.BS_URL_LOC.'=pm_banlist';
						$action_type = BS_ACTION_UNBAN_USER;
						break;
						
					case 'del_cal_event':
						$loc_param = '';
						$action_type = BS_ACTION_CAL_DEL_EVENT;
						break;
				}
				
				if($loc != 'del_pm_ban' && $loc != 'del_cal_event')
					$site_param = '&'.BS_URL_SITE.'='.$site;
				else
					$site_param = '';

				// check parameter
				if($ids == null || !FWS_Array_Utils::is_integer(FWS_Array_Utils::advanced_explode(',',$ids)))
				{
					$this->report_error();
					return;
				}

				$action = ($loc != 'del_cal_event') ? 'userprofile' : 'calendar';
					
				// build url
				if($option == 'yes')
				{
					$murl = BS_URL::get_url(
						$action,$loc_param.'&'.BS_URL_AT.'='.$action_type.'&'.BS_URL_DEL.'='.$ids.$site_param,'&',true
					);
				}
				else
				{
					$murl = BS_URL::get_url(
						$action,$loc_param.$site_param,'&',true
					);
				}
				
				$doc->redirect($murl);
				break;
	
			// redirect to a module or forum
			case 'forum_jump':
				$forum_jump = $input->get_var('forum_jump','post',FWS_Input::STRING);
				$murl = '';
				switch($forum_jump)
				{
					case 'index':
						$murl = BS_URL::get_url('forums','','&');
						break;
					case 'admin':
						$murl = str_replace('&amp;','&',BS_URL::get_admin_url());
						break;
					case 'memberlist':
						$murl = BS_URL::get_url('memberlist','','&');
						break;
					case 'linklist':
						$murl = BS_URL::get_url('linklist','','&');
						break;
					case 'faq':
						$murl = BS_URL::get_url('faq','','&');
						break;
					case 'stats':
						$murl = BS_URL::get_url('stats','','&');
						break;
					case 'calendar':
						$murl = BS_URL::get_url('calendar','','&');
						break;
					case 'search':
						$murl = BS_URL::get_url('search','','&');
						break;
					case 'profile':
						$murl = BS_URL::get_url('userprofile','&'.BS_URL_LOC.'=pr_infos','&');
						break;
					case 'pms':
						$murl = BS_URL::get_url('userprofile','&'.BS_URL_LOC.'=pmoverview','&');
						break;
					case 'register':
						$murl = BS_URL::get_url('register','','&');
						break;
					case 'unread':
						$murl = BS_URL::get_url('unread','','&');
						break;
					case 'team':
						$murl = BS_URL::get_url('team','','&');
						break;
					case 'userloc':
						$murl = BS_URL::get_url('user_locations','','&');
						break;
					default:
						$parts = explode('_',$forum_jump);
						if(count($parts) == 2 && $parts[0] == 'f' && FWS_Helper::is_integer($parts[1]))
							$murl = BS_URL::get_topics_url($parts[1],'&');
						break;
				}
	
				if($murl == '')
				{
					$this->report_error();
					return;
				}
	
				$doc->redirect($murl);
				break;
	
			default:
				$this->report_error();
				break;
		}
	}
}
?>
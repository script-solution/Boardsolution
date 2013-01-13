<?php
/**
 * Contains the redirect-module
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
 * The redirect-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
				
				$url = BS_URL::get_sub_url('userprofile','pmdetails');
				$url->set(BS_URL_ID,$pmid);
				$doc->redirect($url);
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
				
				$doc->redirect(BS_URL::get_posts_url($tdata['rubrikid'],$tid));
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
				
				$post_index = 0;
				$page = 1;
				$postlist = BS_DAO::get_posts()->get_all_from_topic(
					$pdata['rubrikid'],$pdata['threadid'],'id',BS_PostingUtils::get_posts_order()
				);
				foreach($postlist as $data)
				{
					if($data['id'] == $pid)
					{
						$url = BS_URL::get_posts_url($pdata['rubrikid'],$pdata['threadid'],$page);
						if($hl !== null)
						{
							$hl = stripslashes(FWS_StringHelper::htmlspecialchars_back($hl));
							$url->set(BS_URL_HL,$hl);
						}
						$url->set_anchor('b_'.$pid);
						$doc->redirect($url);
					}
		
					$post_index++;
					if(($post_index % $cfg['posts_per_page']) == 0)
						$page++;
				}
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
				$topic_action = $input->get_var('topic_action','post',FWS_Input::STRING);
				switch($topic_action)
				{
					case 'close':
					case 'open':
						$url = BS_URL::get_mod_url('openclose_topics');
						$url->set(BS_URL_MODE,$topic_action);
						$url->set(BS_URL_FID,$fid);
						$url->set(BS_URL_ID,$ids);
						break;
					
					case 'edit':
					case 'delete':
					case 'move':
						if($topic_action == 'edit')
							$url = BS_URL::get_mod_url('edit_topic');
						else if($topic_action == 'delete')
							$url = BS_URL::get_mod_url('delete_topics');
						else
							$url = BS_URL::get_mod_url('move_topics');
						$url->set(BS_URL_FID,$fid);
						$url->set(BS_URL_ID,$ids);
						break;
					
					case 'mark_unread':
					case 'mark_read':
						$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
						$url = BS_URL::get_mod_url(-1);
						$url->set(BS_URL_AT,BS_ACTION_CHANGE_READ_STATUS);
						$url->set(BS_URL_LOC,$topic_action == 'mark_read' ? 'read' : 'unread');
						$url->set(BS_URL_MODE,'topics');
						$url->set(BS_URL_SITE,$site);
						$url->set(BS_URL_FID,$fid);
						$url->set(BS_URL_ID,$ids);
						$url->set_sid_policy(BS_URL::SID_FORCE);
						break;
				}
	
				// invalid mode?
				if(!isset($url))
				{
					$this->report_error();
					return;
				}
	
				$doc->redirect($url);
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
				
				$url = BS_URL::get_sub_url('userprofile',$mode);
				$url->set(BS_URL_SITE,$site);
				$url->set(BS_URL_DEL,$ids);
				
				switch($operation)
				{
					case 'mark_read':
						$url->set(BS_URL_AT,BS_ACTION_MARK_PMS_READ);
						break;
					
					case 'mark_unread':
						$url->set(BS_URL_AT,BS_ACTION_MARK_PMS_UNREAD);
						break;
					
					case 'delete':
						$url->set(BS_URL_MODE,'delete');
						break;
					
					default:
						$this->report_error();
						return;
				}
				
				// redirect
				$doc->redirect($url);
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

				// check parameter
				if($ids == null || !FWS_Array_Utils::is_integer(FWS_Array_Utils::advanced_explode(',',$ids)))
				{
					$this->report_error();
					return;
				}
				
				$action = ($loc != 'del_cal_event') ? 'userprofile' : 'calendar';
				$url = BS_URL::get_mod_url($action);
				$url->set_sid_policy(BS_URL::SID_FORCE);
				
				switch($loc)
				{
					case 'del_subscr':
						$url->set(BS_URL_SUB,'topics');
						$action_type = BS_ACTION_UNSUBSCRIBE_TOPIC;
						break;
					
					case 'del_avatars':
						$url->set(BS_URL_SUB,'avatars');
						$action_type = BS_ACTION_DELETE_AVATAR;
						break;
					
					case 'del_pms':
						$url->set(BS_URL_SUB,'pmoverview');
						$action_type = BS_ACTION_DELETE_PMS;
						break;
					
					case 'del_pm_ban':
						$url->set(BS_URL_SUB,'pmbanlist');
						$action_type = BS_ACTION_UNBAN_USER;
						break;
						
					case 'del_cal_event':
						$action_type = BS_ACTION_CAL_DEL_EVENT;
						break;
				}
				
				if($option == 'yes')
				{
					$url->set(BS_URL_AT,$action_type);
					$url->set(BS_URL_DEL,$ids);
				}
				
				if($loc != 'del_pm_ban' && $loc != 'del_cal_event')
					$url->set(BS_URL_SITE,$site);
				
				$doc->redirect($url);
				break;
	
			// redirect to a module or forum
			case 'forum_jump':
				$forum_jump = $input->get_var('forum_jump','post',FWS_Input::STRING);
				switch($forum_jump)
				{
					case 'index':
						$url = BS_URL::get_mod_url('forums','&');
						break;
					case 'admin':
						$url = BS_URL::get_admin_url('&');
						break;
					case 'memberlist':
						$url = BS_URL::get_mod_url('memberlist','&');
						break;
					case 'linklist':
						$url = BS_URL::get_mod_url('linklist','&');
						break;
					case 'faq':
						$url = BS_URL::get_mod_url('faq','&');
						break;
					case 'stats':
						$url = BS_URL::get_mod_url('stats','&');
						break;
					case 'calendar':
						$url = BS_URL::get_mod_url('calendar','&');
						break;
					case 'search':
						$url = BS_URL::get_mod_url('search','&');
						break;
					case 'profile':
						$url = BS_URL::get_sub_url('userprofile','infos');
						break;
					case 'pms':
						$url = BS_URL::get_sub_url('userprofile','pmoverview');
						break;
					case 'register':
						$url = BS_URL::get_mod_url('register','&');
						break;
					case 'unread':
						$url = BS_URL::get_mod_url('unread','&');
						break;
					case 'team':
						$url = BS_URL::get_mod_url('team','&');
						break;
					case 'userloc':
						$url = BS_URL::get_mod_url('user_locations','&');
						break;
					default:
						$parts = explode('_',$forum_jump);
						if(count($parts) == 2 && $parts[0] == 'f' && FWS_Helper::is_integer($parts[1]))
							$url = BS_URL::get_topics_url($parts[1],0,'&');
						break;
				}
	
				if(!isset($url))
				{
					$this->report_error();
					return;
				}
	
				$doc->redirect($url);
				break;
	
			default:
				$this->report_error();
				break;
		}
	}
}
?>
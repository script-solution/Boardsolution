<?php
/**
 * Contains the reply-action
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
 * The reply-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_new_post_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();
		$ips = FWS_Props::get()->ips();
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		
		// anything to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// has the user the permission to reply in this forum?
		if(!$auth->has_current_forum_perm(BS_MODE_REPLY))
			return 'You have no permission to reply in this forum';

		// are all parameters valid?
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		
		// closed?
		$forum_data = $forums->get_node_data($fid);
		if(!$user->is_admin() && $forum_data->get_forum_is_closed())
			return 'You are no admin and the forum is closed';
		
		// Posts allowed?
		$topic_data = BS_DAO::get_topics()->get_by_id($tid);
		if($topic_data['comallow'] == 0)
			return 'Posts for this topic are disabled';

		// check if the user has just made a post
		$spam_post_on = $auth->is_ipblock_enabled('spam_post');
		if($spam_post_on)
		{
			if($ips->entry_exists('post'))
				return sprintf($locale->lang('error_postipsperre'),($ips->get_timeout('post') / 60));
		}

		// does the topic exist and is it open?
		if(!$user->is_admin() && $topic_data['thread_closed'] == 1)
			return 'You are no admin and the topic is closed';
		
		// check wether there was a reply in the meantime
		$timestamp = $input->get_var('timestamp','post',FWS_Input::INTEGER);
		if($topic_data['lastpost_time'] > $timestamp)
			return 'newpost_while_replying';
		
		// check security-code for guests
		if(!$user->is_loggedin() && $cfg['use_captcha_for_guests'] == 1 &&
				!FWS_Props::get()->functions()->check_security_code(false))
			return 'invalid_security_code';

		// build plain-action
		$post = BS_Front_Action_Plain_Post::get_default($fid,$tid,false);
		
		// check the data
		$res = $post->check_data();
		if($res != '')
			return $res;
		
		// check attachments
		$attachments = $user->is_loggedin() && $auth->has_global_permission('attachments_add');
		if($attachments)
		{
			$att = BS_Front_Action_Plain_Attachments::get_default();
			$res = $att->check_data();
			// we don't want to abort here, we just skip the attachments
			if($res != '')
				$attachments = false;
		}
		
		// perform actions
		$post->perform_action();
		if($attachments)
		{
			$att->set_target($post->get_post_id(),$tid);
			$att->perform_action();
		}

		$ips->add_entry('post');

		// generate the redirect-url
		$murl = BS_URL::get_mod_url('posts');
		$murl->set(BS_URL_FID,$fid);
		$murl->set(BS_URL_TID,$tid);
		$murl->set_anchor('b_'.$post->get_post_id());
		$murl->set_sef(true);
		
		if(BS_PostingUtils::get_posts_order() == 'ASC')
		{
			$post_num = BS_DAO::get_posts()->get_count_in_topic($tid);
			if($post_num > $cfg['posts_per_page'])
			{
				$pagination = new BS_Pagination($cfg['posts_per_page'],$post_num);
				$murl->set(BS_URL_SITE,$pagination->get_page_count());
			}
		}

		$this->add_link($locale->lang('go_to_post'),$murl);
		$this->set_action_performed(true);

		return '';
	}
}
?>
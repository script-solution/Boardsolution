<?php
/**
 * Contains the delete-forums-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-forums-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_forums_delete extends BS_ACP_Action_Base
{
	public function perform_action($type = 'delete')
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$functions = FWS_Props::get()->functions();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		// if we delete a forum we have to delete the sub-forums, too
		if($type == 'delete')
		{
			$delete = array();
			foreach($ids as $fid)
			{
				if($forums->has_childs($fid))
				{
					$sub = $forums->get_sub_nodes($fid);
					$sub_len = count($sub);
					for($x = 0;$x < $sub_len;$x++)
						$delete[] = $sub[$x]->get_id();
				}
				$delete[] = $fid;
			}
			
			$ids = $delete;
		}
		
		$reduce = array();
		$thread_ids = array();
		$poll_ids = array();

		foreach(BS_DAO::get_posts()->get_user_posts_in_forums($ids) as $pdaten)
		{
			if(!isset($reduce[$pdaten['post_user']]['posts']))
				$reduce[$pdaten['post_user']]['posts'] = 0;
			if(!isset($reduce[$pdaten['post_user']]['exp']))
				$reduce[$pdaten['post_user']]['exp'] = 0;
			
			$reduce[$pdaten['post_user']]['posts'] += $pdaten['num'];
			if($pdaten['increase_experience'] == 1)
				$reduce[$pdaten['post_user']]['exp'] += $pdaten['num'] * BS_EXPERIENCE_FOR_POST;
		}

		foreach(BS_DAO::get_topics()->get_by_forums($ids) as $tdaten)
		{
			if($tdaten['increase_experience'] == 1 && $tdaten['post_user'] > 0)
			{
				if(isset($reduce[$tdaten['post_user']]))
					$reduce[$tdaten['post_user']]['exp'] += BS_EXPERIENCE_FOR_TOPIC;
				else
					$reduce[$tdaten['post_user']]['exp'] = BS_EXPERIENCE_FOR_TOPIC;
			}

			$thread_ids[] = $tdaten['id'];
			if($tdaten['type'] > 0)
				$poll_ids[] = $tdaten['type'];
		}

		if(count($reduce) > 0)
		{
			foreach($reduce as $uid => $data)
			{
				$fields = array(
					'exppoints' => array('exppoints - '.$data['exp'])
				);
				if(isset($data['posts']))
					$fields['posts'] = array('posts - '.$data['posts']);
				BS_DAO::get_profile()->update_user_by_id($fields,$uid);
			}
		}
		
		// remove from unread
		if($type == 'delete')
			BS_UnreadUtils::get_instance()->remove_forums($ids);

		if($type == 'delete')
			BS_DAO::get_mods()->delete_by_forums($ids);
		
		if(count($poll_ids) > 0)
			BS_DAO::get_polls()->delete_by_ids($poll_ids);

		if(count($thread_ids) > 0)
		{
			BS_DAO::get_events()->delete_by_topicids($thread_ids);
			
			foreach(BS_DAO::get_attachments()->get_by_topicids($thread_ids) as $adata)
				$functions->delete_attachment($adata['attachment_path']);

			BS_DAO::get_attachments()->delete_by_topicids($thread_ids);
		}

		BS_DAO::get_subscr()->delete_by_forums($ids);
		if(count($thread_ids) > 0)
			BS_DAO::get_subscr()->delete_by_topics($thread_ids);
		
		BS_DAO::get_posts()->delete_by_forums($ids);
		BS_DAO::get_topics()->delete_by_forums($ids);
		
		if($type == 'delete')
		{
			BS_DAO::get_forums()->delete_by_ids($ids);
			BS_DAO::get_forums_perm()->delete_by_forums($ids);
			BS_DAO::get_intern()->delete_by_forums($ids);
			BS_DAO::get_unreadhide()->delete_by_forums($ids);
		}
		else
			BS_DAO::get_forums()->reset_attributes($ids);
		
		// update the intern-permissions and moderators
		$cache->refresh('intern');
		$cache->refresh('moderators');
		
		// refresh forums
		FWS_Props::get()->reload('forums');
		
		if($type == 'delete')
			$this->set_success_msg($locale->lang('delete_forums_successfully'));
		else
			$this->set_success_msg($locale->lang('empty_forums_successfully'));
		
		$this->set_action_performed(true);

		return '';
	}
}
?>
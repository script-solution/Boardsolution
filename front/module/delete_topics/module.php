<?php
/**
 * Contains the delete-topics-module
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
 * The delete-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_delete_topics extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$auth = FWS_Props::get()->auth();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_DELETE_TOPICS,'default');

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$ids = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);

		// don't show forum-title if its intern
		if($fid !== null && $auth->has_access_to_intern_forum($fid))
			$this->add_loc_forum_path($fid);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$ids);
		$renderer->add_breadcrumb($locale->lang('delete_topics'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		// check parameters
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
		{
			$this->report_error();
			return;
		}

		if($fid == null)
		{
			$this->report_error();
			return;
		}
		
		$selected_topic_data = array();
		$selected_topic_ids = array();
		$last_data = null;

		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// skip this topic if the user is not allowed to delete it
			if(!$auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS,$data['post_user']))
				continue;
			
			// forum closed?
			if(!$user->is_admin() && $forums->forum_is_closed($data['rubrikid']))
				continue;
			
			$selected_topic_data[] = $data;
			$selected_topic_ids[] = $data['id'];

			$last_data = $data;
		}

		$selected_topics = BS_TopicUtils::get_selected_topics($selected_topic_data);
		if(count($selected_topics) == 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('no_topics_chosen'));
			return;
		}

		if(count($selected_topic_ids) == 1 && $last_data['moved_tid'] == 0)
			BS_PostingUtils::add_topic_review($last_data,false);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$id_str);
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_DELETE_TOPICS,
			'target_url' => $url->to_url(),
			'selected_topics' => $selected_topics,
			'back_url' => BS_URL::build_topics_url($fid)
		));
	}
}
?>
<?php
/**
 * Contains the lock-topics-module
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
 * The lock-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_lock_topics extends BS_Front_Module
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
		
		$renderer->add_action(BS_ACTION_LOCK_TOPICS,'default');
		
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$ids = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		
		// don't show forum-title if its intern
		if($fid !== null && $auth->has_access_to_intern_forum($fid))
			$this->add_loc_forum_path($fid);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$ids);
		$renderer->add_breadcrumb($locale->lang('lock_topics'),$url->to_url());
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
			if(!$auth->has_current_forum_perm(BS_MODE_LOCK_TOPICS))
				continue;
			
			// forum closed?
			if(!$user->is_admin() && $forums->forum_is_closed($data['rubrikid']))
				continue;
			
			// check if this is a shadow topic
			if($data['moved_tid'] != 0)
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

		$this->request_formular(false,false);
		
		$edit_topic_vals = $this->_get_vals($selected_topic_data,BS_LOCK_TOPIC_EDIT);
		$openclose_topic_vals = $this->_get_vals($selected_topic_data,BS_LOCK_TOPIC_OPENCLOSE);
		$posts_topic_vals = $this->_get_vals($selected_topic_data,BS_LOCK_TOPIC_POSTS);

		if(count($selected_topic_ids) == 1 && $last_data['moved_tid'] == 0)
			BS_PostingUtils::add_topic_review($last_data,false);

		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_ID,$id_str);
		$url->set_sid_policy(BS_URL::SID_FORCE);
			
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_LOCK_TOPICS,
			'target_url' => $url->to_url(),
			'selected_topics' => $selected_topics,
			'edit_topic_def' => $edit_topic_vals['val'],
			'openclose_topic_def' => $openclose_topic_vals['val'],
			'posts_topic_def' => $posts_topic_vals['val'],
			'edit_topic_diffs' => $edit_topic_vals['diffs'],
			'openclose_topic_diffs' => $openclose_topic_vals['diffs'],
			'posts_topic_diffs' => $posts_topic_vals['diffs'],
			'show_diff_hint' => count($selected_topic_ids) > 1,
			'back_url' => BS_URL::build_topics_url($fid)
		));
	}
	
	/**
	 * Determines the value to use for the given type and if there are different values
	 * over the selected topics
	 * 
	 * @param array $selected_topic_data the data-array
	 * @param int $type the type. BS_LOCK_TOPIC_*
	 * @return array an array of the form: array('diff' => ...,'val' => ...)
	 */
	private function _get_vals($selected_topic_data,$type)
	{
		$cval_diffs = false;
		if(count($selected_topic_data) == 1)
			$cval = ($selected_topic_data[0]['locked'] & $type) != 0;
		else
		{
			$etval = $selected_topic_data[0]['locked'] & $type;
			for($i = 1;$i < count($selected_topic_data);$i++)
			{
				$d = $selected_topic_data[$i];
				if(($d['locked'] & $type) != $etval)
				{
					$cval_diffs = true;
					break;
				}
			}
			
			if($cval_diffs)
				$cval = 0;
			else	
				$cval = $etval != 0;
		}
		
		return array(
			'diffs' => $cval_diffs,
			'val' => $cval
		);
	}
}
?>
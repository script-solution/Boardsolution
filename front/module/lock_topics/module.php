<?php
/**
 * Contains the lock-topics-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The lock-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
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
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->add_action(BS_ACTION_LOCK_TOPICS,'default');
		
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$ids = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);

		$this->add_loc_forum_path($fid);
		$renderer->add_breadcrumb(
			$locale->lang('lock_topics'),
			BS_URL::get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$ids)
		);
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

		$selected_topics = BS_TopicUtils::get_instance()->get_selected_topics($selected_topic_data);
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
			BS_PostingUtils::get_instance()->add_topic_review($last_data,false);

		$tpl->add_variables(array(
			'action_type' => BS_ACTION_LOCK_TOPICS,
			'target_url' => BS_URL::get_url(0,'&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_ID.'='.$id_str,'&amp;',true),
			'selected_topics' => $selected_topics,
			'edit_topic_def' => $edit_topic_vals['val'],
			'openclose_topic_def' => $openclose_topic_vals['val'],
			'posts_topic_def' => $posts_topic_vals['val'],
			'edit_topic_diffs' => $edit_topic_vals['diffs'],
			'openclose_topic_diffs' => $openclose_topic_vals['diffs'],
			'posts_topic_diffs' => $posts_topic_vals['diffs'],
			'show_diff_hint' => count($selected_topic_ids) > 1,
			'back_url' => BS_URL::get_topics_url($fid)
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
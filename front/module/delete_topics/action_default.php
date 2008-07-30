<?php
/**
 * Contains the delete-topics-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-topics-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_delete_topics_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// check parameter
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		if($fid == null)
			return 'The forum id "'.$fid.'" is invalid';

		if(!$forums->node_exists($fid))
			return 'The forum with id "'.$fid.'" does not exist';

		$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'No valid id-string got via GET';

		// collect the topic-ids we are allowed to delete
		$tids = array();
		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// skip this topic if the user is not allowed to delete it
			if(!$auth->has_current_forum_perm(BS_MODE_DELETE_TOPICS,$data['post_user']))
				continue;
			
			$tids[] = $data['id'];
		}
		
		if(count($tids) == 0)
			return 'You have no permission to delete any of the selected topics';
		
		// create and check plain-action
		$deltopics = new BS_Front_Action_Plain_DeleteTopics($tids,$fid);
		$res = $deltopics->check_data();
		if($res != '')
			return $res;
		
		// delete the topics
		$deltopics->perform_action();

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back_to_forum'),BS_URL::get_topics_url($fid));

		return '';
	}
}
?>
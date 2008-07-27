<?php
/**
 * Contains the update-userranks-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-userranks-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_userranks_update extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();

		$post_to = $input->get_var('post_to','post');
		$rank_name = $input->get_var('rank_name','post');

		$count = 0;
		if(PLIB_Array_Utils::is_integer($post_to))
		{
			asort($post_to);

			$last_post_to = 0;
			foreach($post_to as $id => $post_to_val)
			{
				$data = $cache->get_cache('user_ranks')->get_element($id);
				if($data['rank'] != $rank_name[$id] || $data['post_from'] != $last_post_to ||
					$data['post_to'] != $post_to_val)
				{
					BS_DAO::get_ranks()->update_by_id($id,array(
						'rank' => $rank_name[$id],
						'post_from' => $last_post_to,
						'post_to' => $post_to_val
					));
					$count++;
				}

				$last_post_to = $post_to_val + 1;
			}

			if($count > 0)
				$cache->refresh('user_ranks');
		}

		$this->set_success_msg($locale->lang('user_ranks_updated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>
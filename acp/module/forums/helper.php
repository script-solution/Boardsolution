<?php
/**
 * Contains the helper-class for the forums
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the forums-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Forums_Helper extends FWS_Singleton
{
	/**
	 * @return BS_ACP_Module_Forums_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}

	/**
	 * checks wether $target_id is a subcategory of the given parent-id
	 *
	 * @param int $parent_id the parent-id which can contain the target-id
	 * @param int $target_id the id of the forum you're looking for
	 * @return boolean true if $target_id is a subcategory (has not to be a direct one) of $parent_id
	 */
	public function is_no_sub_category($parent_id,$target_id)
	{
		$forums = FWS_Props::get()->forums();
		
		if($parent_id == $target_id)
			return false;

		$nodes = $forums->get_sub_nodes($parent_id);
		$len = count($nodes);
		for($i = 0;$i < $len;$i++)
		{
			if($nodes[$i]->get_id() == $target_id)
				return false;
		}

		return true;
	}

	/**
	 * Refreshes the intern-table including the cache
	 *
	 * @param int $id the forum-id
	 * @param array $changeable the changeable-data
	 * @param string $selected_user a string with the user-ids separated by ","
	 * @param array $selected_groups an associative array of the form:
	 * 	<code>array(<group_id> => <selected>)</code>
	 * @param boolean $is_intern is the forum intern?
	 */
	public function refresh_intern_access($id,$selected_user,$selected_groups,$is_intern)
	{
		$cache = FWS_Props::get()->cache();

		$regen = false;
		$intern = $cache->get_cache('intern');
		$user_ids = FWS_Array_Utils::advanced_explode(',',$selected_user);

		if($is_intern)
		{
			$rows = $intern->get_elements_with(array('fid' => $id));
			$del_ids = array();
			foreach($rows as $data)
			{
				if($data['access_type'] == 'group' &&
					 (!isset($selected_groups[$data['access_value']]) ||
					 	$selected_groups[$data['access_value']] == 0))
				{
					$del_ids[] = $data['id'];
				}
				else if($data['access_type'] == 'user' && !in_array($data['access_value'],$user_ids))
				{
					$del_ids[] = $data['id'];
				}
			}

			if(count($del_ids) > 0)
			{
				BS_DAO::get_intern()->delete_by_ids($del_ids);
				$regen = true;
			}

			if(is_array($selected_groups))
			{
				foreach($selected_groups as $gid => $val)
				{
					if($val == 1 && !$intern->get_element_with(
						array('fid' => $id,'access_type' => 'group','access_value' => $gid)))
					{
						BS_DAO::get_intern()->create($id,'group',$gid);
						$regen = true;
					}
				}
			}

			foreach($user_ids as $uid)
			{
				if(!$intern->get_element_with(
					array('fid' => $id,'access_type' => 'user','access_value' => $uid)))
				{
					BS_DAO::get_intern()->create($id,'user',$uid);
					$regen = true;
				}
			}
		}
		else
		{
			$rows = BS_DAO::get_intern()->delete_by_forums(array($id));
			if($rows > 0)
				$regen = true;
		}

		if($regen)
			$cache->refresh('intern');
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
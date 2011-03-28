<?php
/**
 * Contains the forums-permission-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the forums-permission-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_ForumsPerm extends FWS_Singleton
{
	/**
	 * @return BS_DAO_ForumsPerm the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return array all rows from the forums-permission-table
	 */
	public function get_all()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT * FROM '.BS_TB_FORUMS_PERM
		);
	}
	
	/**
	 * Sets the permissions of given type for the given forum
	 *
	 * @param int $fid the forum-id
	 * @param string $type the type: topic, poll, event, reply
	 * @param array $groups an array of all groups that should have access
	 * @return int the number of affected rows
	 */
	public function set_permissions($fid,$type,$groups)
	{
		$db = FWS_Props::get()->db();

		if(count($groups) == 0)
			return 0;
		
		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!in_array($type,array('topic','poll','event','reply')))
			FWS_Helper::def_error('inarray','type',array('topic','poll','event','reply'),$type);
		if(!FWS_Array_Utils::is_integer($groups))
			FWS_Helper::def_error('intarray','groups',$groups);
		
		$sql = 'INSERT INTO '.BS_TB_FORUMS_PERM.' (forum_id,type,group_id) VALUES ';
		foreach($groups as $gid)
			$sql .= '('.$fid.',"'.$type.'",'.$gid.'),';
		$sql = FWS_String::substr($sql,0,-1);
		$db->execute($sql);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries for the given forums
	 *
	 * @param array $fids the forum-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			FWS_Helper::def_error('intarray>0','fids',$fids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_FORUMS_PERM.' WHERE forum_id IN ('.implode(',',$fids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries for the given groups
	 *
	 * @param array $gids the group-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_groups($gids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($gids) || count($gids) == 0)
			FWS_Helper::def_error('intarray>0','gids',$gids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_FORUMS_PERM.' WHERE group_id IN ('.implode(',',$gids).')'
		);
		return $db->get_affected_rows();
	}
}
?>
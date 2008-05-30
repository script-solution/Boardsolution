<?php
/**
 * Contains the db-tree-storage implementation
 *
 * @version			$Id: db.php 741 2008-05-24 12:04:56Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.forums
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An db-based implementation for the tree-storage
 *
 * @package			Boardsolution
 * @subpackage	src.forums
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Forums_Storage_DB extends PLIB_FullObject implements PLIB_Tree_Storage
{
	public function get_nodes()
	{
		$nodes = array();
		foreach(BS_DAO::get_forums()->get_all_for_cache() as $data)
			$nodes[] = new BS_Forums_NodeData($data);
		return $nodes;
	}
	
	public function update_nodes($nodes)
	{
		foreach($nodes as $node)
			BS_DAO::get_forums()->update_by_id($node->get_id(),$node->get_attributes());
	}
	
	public function add_node($data)
	{
		BS_DAO::get_forums()->create($data->get_attributes());
	}
	
	public function remove_nodes($ids)
	{
		BS_DAO::get_forums()->delete_by_ids($ids);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
<?php
/**
 * Contains the db-tree-storage implementation
 * 
 * @package			Boardsolution
 * @subpackage	src.forums
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
 * An db-based implementation for the tree-storage
 *
 * @package			Boardsolution
 * @subpackage	src.forums
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Forums_Storage_DB extends FWS_Object implements FWS_Tree_Storage
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
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
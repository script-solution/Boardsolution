<?php
/**
 * Contains the forums-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.forums
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The forums class caches all forums in a structure which makes it easy to perform the required
 * tasks.
 * 
 * @package			Boardsolution
 * @subpackage	src.forums
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Forums_Manager extends PLIB_Tree_Manager
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(new BS_Forums_Storage_DB());
	}
	
	/**
	 * Collects all forum-ids that are not in the given list
	 *
	 * @param array $ids the ids you don't want to get
	 * @param boolean $categories include categories?
	 * @return array all other forum-ids
	 */
	public function get_nodes_with_other_ids($ids,$categories = true)
	{
		$other = array();
		foreach($this->get_all_nodes() as $node)
		{
			if(!in_array($node->get_id(),$ids))
			{
				if($categories || $node->get_data()->get_forum_type() == 'contains_threads')
				$other[] = $node->get_id();
			}
		}
		return $other;
	}
	
	/**
	 * Builds an array with all properties of the forum with given id
	 *
	 * @param int $id the id of the forum
	 * @return array an associative array with all properties or null if it does not exist
	 */
	public function get_forum_data($id)
	{
		$node = $this->get_node($id);
		if($node !== null)
		{
			$data = $node->get_data();
			return $data->get_attributes();
		}
		
		return null;
	}

	/**
	 * @param int $id the id of the forum
	 * @return int the forum-type of the forum with given id or -1 if not found
	 */
	public function get_forum_type($id)
	{
		$node = $this->get_node($id);
		if($node !== null)
			return $node->get_data()->get_forum_type();

		return -1;
	}

	/**
	 * Checks wether the forum with given id is closed
	 *
	 * @param int $id the id of the forum
	 * @return boolean true if the forum exists and is closed (!)
	 */
	public function forum_is_closed($id)
	{
		$node = $this->get_node($id);
		if($node !== null)
			return $node->get_data()->get_forum_is_closed();
		
		return false;
	}

	/**
	 * @param int $id the id of the forum
	 * @return boolean true if the forum with given id is an intern forum
	 */
	public function is_intern_forum($id)
	{
		$node = $this->get_node($id);
		if($node !==  null)
			return $node->get_data()->get_forum_is_intern();

		return false;
	}

	/**
	 * @param int $id the id of the forum
	 * @return string the name of the forum with given id or an empty string if not found
	 */
	public function get_forum_name($id)
	{
		$node = $this->get_node($id);
		if($node !==  null)
			return $node->get_name();

		return '';
	}

	/**
	 * Determines if the forum or any sub-forum is unread
	 * 
	 * @param int $id the id of the forum
	 * @return boolean true if the given forum is unread
	 */
	public function is_unread_forum($id)
	{
		$node = $this->get_node($id);
		if($node !== null)
		{
			if($this->unread->is_unread_forum($id))
				return true;

			return $this->_is_unread_forum_rek($node);
		}

		return false;
	}

	/**
	 * Calculates recursivly if the given node is unread (or any of it's childs)
	 *
	 * @param PLIB_Tree_Node $node the node
	 * @return boolean true if any sub-forum is unread
	 */
	private function _is_unread_forum_rek($node)
	{
		foreach($node->get_childs() as $child)
		{
			if($this->unread->is_unread_forum($child->get_id()))
				return true;

			if($this->has_childs($child->get_id()))
			{
				$res = $this->_is_unread_forum_rek($child);
				if($res)
					return true;
			}
		}

		return false;
	}
}
?>
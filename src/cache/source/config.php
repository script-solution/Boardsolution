<?php
/**
 * Contains the config-implementation for the source
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The implementation for the source for the config
 *
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Cache_Source_Config extends PLIB_Object implements PLIB_Cache_Source
{
	public function get_content()
	{
		// collect rows
		$rows = array();
		foreach(BS_DAO::get_config()->get_all() as $row)
			$rows[$row['name']] = $row['value'];
		
		return $rows;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
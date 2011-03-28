<?php
/**
 * Contains the custom-db-implementation for the source
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * A db-based implementation for the source which lets you specify the query manually.
 *
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Cache_Source_CustomDB extends FWS_Object implements FWS_Cache_Source
{
	/**
	 * The SQL-query
	 *
	 * @var string
	 */
	private $_sql;
	
	/**
	 * The key of the table (null = none)
	 *
	 * @var string
	 */
	private $_key;
	
	/**
	 * Constructor
	 * 
	 * @param string $sql the SQL-query
	 * @param string $key the key of the table (null = none)
	 */
	public function __construct($sql,$key = null)
	{
		parent::__construct();
		
		if(empty($sql))
			FWS_Helper::def_error('notempty','sql',$sql);
		if($key !== null && empty($key))
			FWS_Helper::error('$key is not null but empty!');
		
		$this->_sql = $sql;
		$this->_key = $key;
	}
	
	public function get_content()
	{
		$db = FWS_Props::get()->db();
		$rows = array();
		foreach($db->execute($this->_sql) as $row)
		{
			if($this->_key !== null)
				$rows[$row[$this->_key]] = $row;
			else
				$rows[] = $row;
		}
		return $rows;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
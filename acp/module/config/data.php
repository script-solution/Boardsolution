<?php
/**
 * Contains the config-data class
 *
 * @version			$Id: data.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The config-data for boardsolution
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Data extends PLIB_Config_Data
{
	/**
	 * Indicates wether the setting affects the messages:
	 * <pre>
	 * 	0 = no
	 * 	1 = a little bit
	 * 	2 = heavy
	 * </pre>
	 *
	 * @var int
	 */
	private $_affects_msgs;
	
	/**
	 * Constructor
	 *
	 * @param array $data the config-data from the database
	 */
	public function __construct($data)
	{
		parent::__construct(
			$data['id'],$data['name'],$data['custom_title'],$data['group_id'],$data['sort'],
			$data['type'],$data['properties'],$data['suffix'],$data['value'],$data['default']
		);
		
		$this->_affects_msgs = $data['affects_msgs'];
	}
	
	/**
	 * @return int wether the setting affects the messages:
	 * 	<pre>
	 * 		0 = no
	 * 		1 = a little bit
	 * 		2 = heavy
	 * 	</pre>
	 */
	public function get_affects_msgs()
	{
		return $this->_affects_msgs;
	}
}
?>
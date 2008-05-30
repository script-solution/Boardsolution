<?php
/**
 * Contains the additional-fields manager for boardsolution
 *
 * @version			$Id: manager.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The additional fields manager for Boardsolution
 *
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_AddField_Manager extends PLIB_AddField_Manager
{
	/**
	 * @return BS_AddField_Manager the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(new BS_AddField_Source_DB());
	}
}
?>
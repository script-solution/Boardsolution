<?php
/**
 * Contains the task-container
 *
 * @version			$Id: container.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task-container for Boardsolution
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_Container extends PLIB_Tasks_Container
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$storage = new BS_Tasks_Storage_DB();
		parent::__construct($storage,PLIB_Path::inner().'src/tasks/','BS_Tasks_');
	}
}
?>
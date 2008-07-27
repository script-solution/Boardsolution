<?php
/**
 * Contains the front-base-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-action-class for all front-actions
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Action_Base extends PLIB_Actions_Base
{
	/**
	 * An array for all actions that defines for which action a status-page should be displayed
	 *
	 * @var array
	 */
	static $_action_msgs = null;
	
	/**
	 * Includes the file config/actions.php and stores the action-msgs in a class-attribute
	 */
	public static function load_actions()
	{
		$action_msgs = array();
		include_once(PLIB_Path::server_app().'config/actions.php');
		self::$_action_msgs = $action_msgs;
	}
	
	/**
	 * Constructor
	 * 
	 * @param int $id the id of the action
	 */
	public function __construct($id)
	{
		parent::__construct($id);
		
		// set wether we want to show a status-page
		if(isset(self::$_action_msgs[$this->get_action_id()]))
			$this->set_show_status_page(self::$_action_msgs[$this->get_action_id()]);
	}
}
?>
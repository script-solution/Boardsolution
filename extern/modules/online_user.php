<?php
/**
 * Contains the online-user-module
 * 
 * @version			$Id: online_user.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains the currently online user, bots and so on.
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_API_Module_online_user extends BS_API_Module
{
	/**
	 * the total number of online "user", that means registered, guests, bots and ghosts
	 *
	 * @var integer
	 */
	public $total_online = 0;

	/**
	 * a numeric array with the names of all currently online bots
	 *
	 * @var array
	 */
	public $online_bots = array();

	/**
	 * an associative array with the online registered user in the following form:
	 * <code>
	 * array(
	 * 	'id' => <idOfTheUser>,
	 * 	'name' => <nameOfTheUser>,
	 * 	'group' => <idOfTheMainUserGroup>,
	 * 	'location' => <theCurrentLocation>
	 * )
	 * </code>
	 *
	 * @var array
	 */
	public $online_user = array();

	/**
	 * the number of currently online guests
	 *
	 * @var integer
	 */
	public $online_guest_num = 0;

	/**
	 * the number of currently online ghosts
	 *
	 * @var integer
	 */
	public $online_ghost_num = 0;

	public function run()
	{
		$online = $this->sessions->get_user_at_location();

		$this->total_online = count($online);
		foreach($online as $data)
		{
			if($data['bot_name'] != '')
				$this->online_bots[] = $data['bot_name'];
			else if($data['user_id'] == 0)
				$this->online_guest_num++;
			else if(!$this->user->is_admin() && $data['ghost_mode'] == 1 &&
					$this->cfg['allow_ghost_mode'] == 1)
				$this->online_ghost_num++;
			else
			{
				$loc = new BS_Location($data['location']);
				$this->online_user[] = array(
					'id' => $data['user_id'],
					'name' => $data['user_name'],
					'group' => (int)$data['user_group'],
					'location' => $loc->decode()
				);
			}
		}
	}
}
?>
<?php
/**
 * Contains the updates-task
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which checks for Boardsolution-Updates
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_updates extends FWS_Tasks_Base
{
	public function run()
	{
		// load versions-file
		$http = new FWS_HTTP(BS_Version::VERSION_HOST);
		$versions = $http->get(BS_Version::VERSION_PATH);
		if($versions === false)
			return;
		
		// check for updates
		$vs = BS_Version::read_versions($versions);
		$res = BS_Version::check_for_update($vs);
		if($res !== null)
		{
			$mail = BS_EmailFactory::get_instance()->get_updates_mail(is_array($res));
			foreach(BS_DAO::get_profile()->get_users_by_groups(array(BS_STATUS_ADMIN)) as $user)
				$mail->add_bcc_recipient($user['user_email']);
			$mail->send_mail();
		}
	}
}
?>
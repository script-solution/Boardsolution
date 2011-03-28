<?php
/**
 * Contains the delete-backups-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-backups-action
 *
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Action_backups_delete extends BS_DBA_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$backups = FWS_Props::get()->backups();

		$ibackups = $input->get_var('backups','get',FWS_Input::STRING);
		$abackups = FWS_Array_Utils::advanced_explode(',',$ibackups);
		if(count($abackups) == 0)
			return 'No backups specified via GET "backups"';
		
		$i = 0;
		foreach($abackups as $prefix)
		{
			if($backups->delete_backup($prefix))
				$i++;
		}

		$this->set_success_msg(sprintf($locale->lang('deleted_backups'),$i));
		$this->set_action_performed(true);

		return '';
	}
}
?>
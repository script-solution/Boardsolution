<?php
/**
 * Contains the delete-backups-action
 *
 * @version			$Id: action_delete.php 676 2008-05-08 09:02:28Z nasmussen $
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
		$backups = $this->input->get_var('backups','get',PLIB_Input::STRING);
		$abackups = PLIB_Array_Utils::advanced_explode(',',$backups);
		if(count($abackups) == 0)
			return 'No backups specified via GET "backups"';
		
		$i = 0;
		foreach($abackups as $prefix)
		{
			if($this->backups->delete_backup($prefix))
				$i++;
		}

		$this->set_success_msg(sprintf($this->locale->lang('deleted_backups'),$i));
		$this->set_action_performed(true);

		return '';
	}
}
?>
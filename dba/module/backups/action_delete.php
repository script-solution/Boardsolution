<?php
/**
 * Contains the delete-backups-action
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
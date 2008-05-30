<?php
/**
 * Contains the delete-index-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-index-action
 *
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Action_index_delete extends BS_DBA_Action_Base
{
	public function perform_action()
	{
		$stables = $this->input->get_var('tables','get',PLIB_Input::STRING);
		$tables = PLIB_Array_Utils::advanced_explode(';',$stables);
		if(count($tables) > 0)
			$this->db->sql_qry('DROP TABLE `'.implode('`, `',$tables).'`');
		
		$this->set_success_msg(
			sprintf($this->locale->lang('delete_tables_success'),'"'.implode('", "',$tables).'"')
		);
		$this->set_action_performed(true);

		return '';
	}
}
?>
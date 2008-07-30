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
		$input = FWS_Props::get()->input();
		$db = FWS_Props::get()->db();
		$locale = FWS_Props::get()->locale();

		$stables = $input->get_var('tables','get',FWS_Input::STRING);
		$tables = FWS_Array_Utils::advanced_explode(';',$stables);
		if(count($tables) > 0)
			$db->sql_qry('DROP TABLE `'.implode('`, `',$tables).'`');
		
		$this->set_success_msg(
			sprintf($locale->lang('delete_tables_success'),'"'.implode('", "',$tables).'"')
		);
		$this->set_action_performed(true);

		return '';
	}
}
?>
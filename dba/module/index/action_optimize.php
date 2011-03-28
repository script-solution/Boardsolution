<?php
/**
 * Contains the optimize-index-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The optimize-index-action
 *
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Action_index_optimize extends BS_DBA_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$db = FWS_Props::get()->db();
		$locale = FWS_Props::get()->locale();

		$stables = $input->get_var('tables','get',FWS_Input::STRING);
		$tables = FWS_Array_Utils::advanced_explode(';',$stables);
		if(count($tables) > 0)
			$db->execute('OPTIMIZE TABLE `'.implode('`, `',$tables).'`');
		
		$this->set_success_msg(
			sprintf($locale->lang('optimize_tables_success'),'"'.implode('", "',$tables).'"')
		);
		$this->set_action_performed(true);

		return '';
	}
}
?>
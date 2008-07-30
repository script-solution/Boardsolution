<?php
/**
 * Contains the regenerate-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The regenerate-action for the db-cache module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_dbcache_regenerate extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$names = $input->get_var('delete','post');
		if(count($names) == 0)
			return '';
		
		$found = array();
		foreach($names as $name)
		{
			if($cache->get_cache($name) !== null)
			{
				$cache->refresh($name);
				$found[] = $name;
			}
		}
		
		$this->set_action_performed(true);
		$this->set_success_msg(
			sprintf($locale->lang('regenerate_cache_success'),'"'.implode('", "',$found).'"')
		);
		
		return '';
	}
}
?>
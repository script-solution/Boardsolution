<?php
/**
 * Contains the add-event-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_calendar_addevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		// nothing to do?
		if(!$input->isset_var('submit','post',PLIB_Input::STRING))
			return '';
		
		// has the user permission to start an event?
		if($cfg['enable_calendar_events'] == 0 ||
			!$auth->has_global_permission('add_cal_event'))
			return 'Calendar-events disabled or no permission to add events';

		$event = BS_Front_Action_Plain_Event::get_default();
		$res = $event->check_data();
		if($res != '')
			return $res;
		
		$event->perform_action();

		$this->add_link($locale->lang('back'),$url->get_url('calendar'));
		$this->set_action_performed(true);

		return '';
	}
}
?>
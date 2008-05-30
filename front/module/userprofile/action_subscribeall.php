<?php
/**
 * Contains the subscribe-all-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The subscribe-all-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_subscribeall extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// is the user loggedin?
		if($this->cfg['enable_email_notification'] == 0 || !$this->user->is_loggedin())
			return 'Subscriptions are disabled or you are a guest';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// collect all existing forum-subscriptions
		$ex_fids = array();
		foreach(BS_DAO::get_subscr()->get_subscr_forums_of_user($this->user->get_user_id()) as $data)
			$ex_fids[$data['forum_id']] = true;

		$denied_forums = BS_ForumUtils::get_instance()->get_denied_forums(true);

		// get all missing forum-ids
		$fids = array();
		foreach($this->forums->get_all_nodes() as $fnode)
		{
			$fdata = $fnode->get_data();
			// denied forum or category?
			if(in_array($fdata->get_id(),$denied_forums))
				continue;

			if(!isset($ex_fids[$fdata->get_id()]))
				$fids[] = $fdata->get_id();
		}

		// do we have a subscription-limit?
		if($this->cfg['max_forum_subscriptions'] > 0)
		{
			if(count($fids) + count($ex_fids) > $this->cfg['max_forum_subscriptions'])
			{
				return sprintf(
					$this->locale->lang('error_subscribe_all_not_possible'),
					$this->cfg['max_forum_subscriptions']
				);
			}
		}

		// add all missing forum-ids
		foreach($fids as $fid)
			BS_DAO::get_subscr()->subscribe_forum($fid,$this->user->get_user_id());
		
		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),$this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=forums')
		);

		return '';
	}
}
?>
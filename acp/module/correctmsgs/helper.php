<?php
/**
 * Contains the helper-class for correctmsgs
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the correctmsgs-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_CorrectMsgs_Helper extends FWS_UtilBase
{
	/**
	 * Determines all incorrect messages
	 *
	 * @return array an array of the form: <code>array(<type>,<id>)</code>
	 */
	public static function get_incorrect_messages()
	{
		$incorrect = array();
		
		// posts
		foreach(BS_DAO::get_posts()->get_invalid_post_ids() as $data)
			$incorrect[] = array('post',$data['id']);
	
		// signatures
		foreach(BS_DAO::get_profile()->get_invalid_signature_ids() as $data)
			$incorrect[] = array('signature',$data['id']);
	
		// pms
		foreach(BS_DAO::get_pms()->get_invalid_pm_ids() as $data)
			$incorrect[] = array('pm',$data['id']);
	
		// links
		foreach(BS_DAO::get_links()->get_invalid_link_ids() as $data)
			$incorrect[] = array('link',$data['id']);
	
		// events
		foreach(BS_DAO::get_events()->get_invalid_event_ids() as $data)
			$incorrect[] = array('event',$data['id']);
		
		return $incorrect;
	}
}
?>
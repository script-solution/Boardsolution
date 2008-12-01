<?php
/**
 * Contains the base-document-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-document for all documents in Boardsolution
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Document extends FWS_Document
{
	/**
	 * @see FWS_Document::finish()
	 */
	protected function finish()
	{
		parent::finish();
		
		$db = FWS_Props::get()->db();
		$db->disconnect();
	}

	/**
	 * @see FWS_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		
		$tpl->add_global('gisloggedin',$user->is_loggedin());
		$tpl->add_global('gusername',$user->get_user_name());
		$tpl->add_global('guserid',$user->get_user_id());
		$tpl->add_global('gisadmin',$user->is_admin());
		$tpl->add_global('glang',$user->get_language());
		// TODO add theme
		// TODO add current module
		
		$this->set_charset(BS_HTML_CHARSET);
		FWS_Path::set_outer($cfg['board_url'].'/');
		
		// set our error-logger
		FWS_Error_Handler::get_instance()->set_logger(new BS_Error_Logger());
		
		// load language
		$locale->add_language_file('index');
		
		// run tasks
		$taskcon = new BS_Tasks_Container();
		$taskcon->run_tasks();
		
		parent::prepare_rendering();
	}
}
?>
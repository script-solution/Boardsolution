<?php
/**
 * Contains the base-class for all install-actions
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-class for all install-actions
 *
 * @package			Boardsolution
 * @subpackage	install.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Install_Action_Base extends FWS_Action_Base
{
	/**
	 * Constructor
	 * 
	 * @param mixed $id the action-id
	 */
	public function __construct($id)
	{
		parent::__construct($id);
		
		$this->set_redirect(false);
		$this->set_show_status_page(false);
	}
	
	/**
	 * Generates an URL for the next step
	 *
	 * @return FWS_URL the url
	 */
	protected function get_step_url()
	{
		$input = FWS_Props::get()->input();
		$action = $input->get_var('action','get',FWS_Input::STRING);
		$dir = $input->get_var('dir','get',FWS_Input::STRING);
		$phpself = $input->get_var('PHP_SELF','server',FWS_Input::STRING);
		$url = new FWS_URL();
		$url->set_file(basename($phpself));
		if($dir == 'back')
			$url->set('action',$action - 1);
		else if($dir == 'forward')
			$url->set('action',$action + 1);
		else
			$url->set('action',$action);
		return $url;
	}
}
?>
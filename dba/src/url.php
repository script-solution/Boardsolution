<?php
/**
 * Contains the URL-class for the dbbackup-script
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The URL-class for the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_URL extends FWS_Singleton
{
	/**
	 * @return BS_DBA_URL the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Builds an URL for the backup-script
	 * 
	 * @param mixed $target the action-value. 0 = current, -1 = none
	 * @param string $additional additional parameters
	 * @param string $separator the separator for the parameters.
	 * @return string the url
	 */
	public function get_url($target = 0,$additional = '',$separator = '&amp;')
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();

		// always pass the session-id via URL
		$session_id = $separator.'sid='.$user->get_session_id();
		
		$action_param = $input->get_var('action','get',FWS_Input::STRING);
		if($target === 0)
			$action = 'action='.$action_param;
		else if($target === -1)
			$action = '';
		else
			$action = 'action='.$target;
		
		$parameters = $action.$session_id.$additional;
		if(FWS_String::starts_with($parameters,$separator))
			$parameters = FWS_String::substr($parameters,strlen($separator));
		$url = $input->get_var('PHP_SELF','server',FWS_Input::STRING);
		if($parameters != '')
			$url .= '?'.$parameters;
		
		return $url;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
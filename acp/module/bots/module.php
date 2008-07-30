<?php
/**
 * Contains the bots module for the ACP
 * 
 * @version			$Id: module_bots.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The bots-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_bots extends BS_ACP_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('bots',array('default','add','edit'),'default');
	}

	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();

		$renderer->add_breadcrumb($locale->lang('acpmod_bots'),$url->get_acpmod_url());
		
		// init submodule
		$this->_sub->init($doc);
	}
	
	/**
	 * Checks the formular-values and returns the error-message, if any, or an array
	 * with the values
	 * 
	 * @return array the values
	 */
	public static function check_values()
	{
		$input = PLIB_Props::get()->input();
		
		$bot_name = $input->get_var('bot_name','post',PLIB_Input::STRING);
		$bot_match = $input->get_var('bot_match','post',PLIB_Input::STRING);
		$bot_ip_start = $input->get_var('bot_ip_start','post',PLIB_Input::STRING);
		$bot_ip_end = $input->get_var('bot_ip_end','post',PLIB_Input::STRING);
		$bot_access = $input->get_var('bot_access','post',PLIB_Input::INT_BOOL);

		if(trim($bot_name) == '')
			return 'bot_name_empty';

		if(trim($bot_match) == '')
			return 'bot_match_empty';

		if($bot_ip_start != '' && !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$bot_ip_start))
			return 'bot_invalid_start_ip';

		if($bot_ip_end != '' && !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$bot_ip_end))
			return 'bot_invalid_end_ip';
		
		if(($bot_ip_start != '' && $bot_ip_end == '') || ($bot_ip_start == '' && $bot_ip_end != ''))
			return 'bot_invalid_ip_range';
		
		return array(
			'bot_name' => $bot_name,
			'bot_match' => $bot_match,
			'bot_ip_start' => $bot_ip_start,
			'bot_ip_end' => $bot_ip_end,
			'bot_access' => $bot_access
		);
	}
}
?>
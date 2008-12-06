<?php
/**
 * Contains the dbcheck module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The dbcheck-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_4 extends BS_Install_Module
{
	/**
	 * @see FWS_Module::init()
	 *
	 * @param BS_Install_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(4,'forward');
		
		$this->connect_to_db();
		$renderer->get_action_performer()->perform_action_by_id(4);
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		
		$this->request_formular();

		$prefix = $user->get_session_data('table_prefix','bs_');
		$type = $user->get_session_data('install_type','full');
		
		$configs = array();
		$configs[] = array('type' => 'separator','desc' => '');
		
		if($type == 'update')
		{
			$tbls = BS_Install_Module_4_Helper::check_update();
			$succ = $locale->lang('ok');
			$failed = $locale->lang('notok');
		}
		else
		{
			$tbls = BS_Install_Module_4_Helper::check_full();
			$succ = $locale->lang('notavailable');
			$failed = $locale->lang('available');
		}
		
		foreach($tbls as $name => $status)
		{
			$configs[] = $this->get_status(
				$name,$status === true,$succ,$failed
			);
		}
		
		$tpl->add_array('configs',$configs);
		$tpl->add_variables(array(
			'prefix' => $prefix,
			'show_table_prefix' => true,
			'title' => $locale->lang('step_dbcheck')
		));
		
		$db->disconnect();
	}
}
?>
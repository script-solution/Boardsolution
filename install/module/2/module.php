<?php
/**
 * Contains the type module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The type-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_2 extends BS_Install_Module
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
		$renderer->add_action(2,'forward');
		$renderer->get_action_performer()->perform_action_by_id(2);
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$type = $user->get_session_data('install_type','full');
		$options = array(
			'full' => $locale->lang('fullinstall'),
			'update' => $locale->lang('update')
		);
		
		$this->request_formular();
		$tpl->add_variables(array(
			'type_options' => $options,
			'type_val' => $type
		));
	}
}
?>
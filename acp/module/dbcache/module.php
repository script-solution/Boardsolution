<?php
/**
 * Contains the dbcache module for the ACP
 * 
 * @version			$Id: module_dbcache.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The dbcache-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_dbcache extends BS_ACP_Module
{
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
		
		$renderer->add_action(BS_ACP_ACTION_REGENERATE_CACHE,'regenerate');
		$renderer->add_breadcrumb($locale->lang('acpmod_dbcache'),$url->get_acpmod_url());
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$tpl = PLIB_Props::get()->tpl();
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();

		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_REGENERATE_CACHE
		));
		
		$entries = array();
		foreach($cache->get_caches() as $name => $content)
		{
			if($content instanceof PLIB_Cache_Content)
				$entries[] = $name;
		}
		$tpl->add_array('entries',$entries);
		
		// show details?
		if($input->isset_var('name','get'))
		{
			$name = $input->get_var('name','get',PLIB_Input::STRING);
			$content = $cache->get_cache($name);
			if($content != null)
			{
				$tpl->add_variables(array(
					'show_cache' => true,
					'details_name' => $name,
					'cache_content' => PLIB_PrintUtils::to_string($content->get_elements())
				));
			}
		}
	}
}
?>
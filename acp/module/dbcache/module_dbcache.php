<?php
/**
 * Contains the dbcache module for the ACP
 * 
 * @version			$Id: module_dbcache.php 705 2008-05-15 10:14:58Z nasmussen $
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
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_REGENERATE_CACHE => 'regenerate'
		);
	}
	
	public function run()
	{
		$this->tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_REGENERATE_CACHE
		));
		
		$entries = array();
		foreach($this->cache->get_caches() as $name => $cache)
		{
			if($cache instanceof PLIB_Cache_Content)
				$entries[] = $name;
		}
		$this->tpl->add_array('entries',$entries);
		
		// show details?
		if($this->input->isset_var('name','get'))
		{
			$name = $this->input->get_var('name','get',PLIB_Input::STRING);
			$cache = $this->cache->get_cache($name);
			if($cache != null)
			{
				$this->tpl->add_variables(array(
					'show_cache' => true,
					'details_name' => $name,
					'cache_content' => PLIB_PrintUtils::to_string($cache->get_elements())
				));
			}
		}
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_dbcache') => $this->url->get_acpmod_url()
		);
	}
}
?>
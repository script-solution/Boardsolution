<?php
/**
 * Contains the search-submodule for cfgitems
 * 
 * @version			$Id: sub_search.php 701 2008-05-14 13:37:15Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The search sub-module for the cfgitems-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_config_search extends BS_ACP_SubModule
{
	/**
	 * The keyword
	 *
	 * @var string
	 */
	private $_keyword;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_keyword = $this->input->get_var('kw','get',PLIB_Input::STRING);
		if($this->_keyword !== null)
		{
			$this->_keyword = $this->_prepare_keyword($this->_keyword);
			$helper = BS_ACP_Module_Config_Helper::get_instance();
			$helper->get_manager()->load_items_with($this->_keyword);
		}
	}
	
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_SAVE_SETTINGS => 'save',
			BS_ACP_ACTION_REVERT_SETTING => 'revert'
		);
	}
	
	public function get_template()
	{
		return 'config_items.htm';
	}
	
	public function run()
	{
		$keyword = $this->input->get_var('kw','get',PLIB_Input::STRING);
		if($this->_keyword === null)
		{
			$this->_report_error();
			return;
		}
		
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		$manager = $helper->get_manager();
		$view = new BS_ACP_Module_Config_View_Default('search');
		$manager->display($view);
		
		// highlight the keyword
		$hl = new PLIB_KeywordHighlighter(array($this->_keyword),'<span class="bs_highlight">','</span>');
		$items = $view->get_items();
		foreach($items as $k => $item)
		{
			$items[$k]['title'] = $hl->highlight($item['title']);
			if(isset($item['description']))
				$items[$k]['description'] = $hl->highlight($item['description']);
		}
		
		$perline = 6;
		$hidden_fields = $this->url->get_acpmod_comps();
		$hidden_fields['action'] = 'search';
		$this->tpl->add_variables(array(
			'form_target' => $this->url->get_acpmod_url(0,'&amp;action=search&amp;kw='.$keyword),
			'action_type' => BS_ACP_ACTION_SAVE_SETTINGS,
			'title' => sprintf($this->locale->lang('config_search_result'),$this->_keyword),
			'items' => $items,
			'form_target' => $this->input->get_var('SERVER_PHPSELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'groups_per_line' => $perline,
			'group_rows' => $helper->get_groups(0,$perline),
			'keyword' => $keyword,
			'groups_width' => round(100 / $perline),
			'at' => BS_ACP_ACTION_REVERT_SETTING,
			'view' => 'search',
			'kw' => $keyword,
			'display_affects_msgs_hints' => $view->has_affects_msgs_settings()
		));
	}
	
	/**
	 * Replaces all german umlaute with the corresponding HTML-entity and transforms the string
	 * to lowercase.
	 *
	 * @param string $keyword the keyword
	 * @return string the result
	 */
	private function _prepare_keyword($keyword)
	{
		$keyword = PLIB_String::strtolower($keyword);
		// replace german umlaute
		$uml = array("\xE3\xA4","\xE3\xB6","\xE3\xBC","\xE3\x84","\xE3\x96","\xE3\x9C","\xE3\x9F");
		$repl = array('&auml;','&ouml;','&uuml;','&Auml;','&Ouml;','&Uuml;','&szlig');
		return str_replace($uml,$repl,$keyword);
	}
	
	public function get_location()
	{
		$keyword = $this->input->get_var('kw','get',PLIB_Input::STRING);
		if($keyword == null)
			return array();
		
		return array(
			$this->locale->lang('config_search_result_title') =>
				$this->url->get_acpmod_url(0,'&amp;action=search&amp;kw='.$keyword)
		);
	}
}
?>
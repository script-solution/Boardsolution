<?php
/**
 * Contains the search-submodule for cfgitems
 * 
 * @version			$Id$
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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_SAVE_SETTINGS,'save');
		$renderer->add_action(BS_ACP_ACTION_REVERT_SETTING,'revert');
		
		$renderer->set_template('config_items.htm');

		// init manager
		$this->_keyword = $input->get_var('kw','get',FWS_Input::STRING);
		if($this->_keyword !== null)
		{
			$this->_keyword = $this->_prepare_keyword($this->_keyword);
			$helper = BS_ACP_Module_Config_Helper::get_instance();
			$helper->get_manager()->load_items_with($this->_keyword);
		}

		// add bread crumb
		$keyword = $input->get_var('kw','get',FWS_Input::STRING);
		if($keyword != null)
		{
			$url = BS_URL::get_acpsub_url();
			$url->set('kw',$keyword);
			$renderer->add_breadcrumb($locale->lang('config_search_result_title'),$url->to_url());
		}
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$keyword = $input->get_var('kw','get',FWS_Input::STRING);
		if($this->_keyword === null)
		{
			$this->report_error();
			return;
		}
		
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		$manager = $helper->get_manager();
		$view = new BS_ACP_Module_Config_View_Default('search');
		$manager->display($view);
		
		// highlight the keyword
		$hl = new FWS_KeywordHighlighter(array($this->_keyword),'<span class="bs_highlight">','</span>');
		$items = $view->get_items();
		foreach($items as $k => $item)
		{
			$items[$k]['title'] = $hl->highlight($item['title']);
			if(isset($item['description']))
				$items[$k]['description'] = $hl->highlight($item['description']);
		}
		
		$perline = 6;
		$hidden_fields = BS_URL::get_acpmod_comps();
		$hidden_fields['action'] = 'search';
		
		$url = BS_URL::get_acpsub_url();
		$url->set('kw',$keyword);
		
		$tpl->add_variables(array(
			'form_target' => $url->to_url(),
			'action_type' => BS_ACP_ACTION_SAVE_SETTINGS,
			'title' => sprintf($locale->lang('config_search_result'),$this->_keyword),
			'items' => $items,
			'form_target' => $input->get_var('SERVER_PHPSELF','server',FWS_Input::STRING),
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
		$keyword = FWS_String::strtolower($keyword);
		// replace german umlaute
		$uml = array("\xE3\xA4","\xE3\xB6","\xE3\xBC","\xE3\x9F");
		$repl = array('&auml;','&ouml;','&uuml;','&szlig');
		$keyword = str_replace($uml,$repl,$keyword);
		
		$uml = array('ä','ö','ü','ß');
		$repl = array('&auml;','&ouml;','&uuml;','&szlig');
		$keyword = str_replace($uml,$repl,$keyword);
		return $keyword;
	}
}
?>
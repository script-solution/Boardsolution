<?php
/**
 * Contains the acp-navi-document-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.document
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The acp-navi-document. We have no modules here and no renderer.
 *
 * @package			Boardsolution
 * @subpackage	acp.src.document
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Document_Navi extends BS_ACP_Document
{
	/**
	 * @see PLIB_Document::render()
	 *
	 * @return string
	 */
	public function render()
	{
		$this->prepare_rendering();
		
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$locale = PLIB_Props::get()->locale();

		$menu = $input->get_var(BS_COOKIE_PREFIX.'acp_menu','cookie',PLIB_Input::STRING);
			
		$tpl->set_template('navi.htm');
		$tpl->add_variables(array(
			'charset' => 'charset='.BS_HTML_CHARSET,
			'cookie_prefix' => BS_COOKIE_PREFIX,
			'cookie_init_value' => ($menu != null) ? $menu : '',
			'cookie_path' => $cfg['cookie_path'],
			'cookie_domain' => $cfg['cookie_domain'],
		  'page_title' => sprintf($locale->lang('page_title'),BS_VERSION)
		));
		
		$this->_load_modules();
		
		$this->finish();
		return $tpl->parse_template();
	}
	
	/**
	 * Loads all modules for the menu
	 */
	private function _load_modules()
	{
		$locale = PLIB_Props::get()->locale();
		$auth = PLIB_Props::get()->auth();
		$user = PLIB_Props::get()->user();
		$tpl = PLIB_Props::get()->tpl();

		$c = 0;
		$m = 0;
		$tpl_categories = array();
		
		foreach(BS_ACP_Menu::get_instance()->get_menu_items() as $cat)
		{
			$tpl_categories[$c] = array(
				'id' => $c,
				'title' => $locale->lang($cat['title']),
				'modules' => array()
			);
			
			// ok, collect the modules
			foreach($cat['modules'] as $mod => $data)
			{
				$access = isset($data['access']) ? $data['access'] : 'default';
				if(!in_array($access,array('no','all','default','admin')))
					$access = 'default';
				
				// has the user access to the module?
				$has_access = $access == 'all';
				if(!$has_access)
				{
					if($access == 'default' && $auth->has_access_to_module($mod))
						$has_access = true;
					else if($access = 'admin' && $user->is_admin())
						$has_access = true;
				}
				
				if($has_access)
				{
					$frame = isset($data['frame']) ? $data['frame'] : 'content';
					$url = isset($data['url']) && !empty($data['url']) ? $data['url'] : null;
					$item = new BS_ACP_MenuItem($mod,$frame,$url);
					
					if(isset($data['target']))
						$target = 'target="'.$data['target'].'" ';
					else if(!isset($data['frame']))
						$target = $item->get_frame();
					else
						$target = '';
					
					$js = !isset($data['target']) || $data['target'] != '_blank' ? $item->get_javascript() : '';
					$tpl_categories[$c]['modules'][] = array(
						'id' => $m++,
						'title' => $locale->lang($data['title']),
						'url' => $item->get_url(),
						'frame' => $target,
						'javascript' => $js
					);
				}
			}
			
			// skip empty groups
			if(count($tpl_categories[$c]['modules']) == 0)
				unset($tpl_categories[$c]);
			else
				$c++;
		}
		
		$tpl->add_array('categories',$tpl_categories);
	}

	/**
	 * @see PLIB_Document::load_module()
	 *
	 * @return BS_Front_Module
	 */
	protected function load_module()
	{
		// no module here
		return null;
	}
}
?>
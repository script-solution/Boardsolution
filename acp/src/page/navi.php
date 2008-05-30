<?php
/**
 * Contains the acp-navi-page
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The navi-page of the ACP. Contains the navigation
 * 
 * @package			Boardsolution
 * @subpackage	acp.src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Page_Navi extends BS_ACP_Document
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
			
			$this->_start_document(BS_ENABLE_ADMIN_GZIP);
			
			$menu = $this->input->get_var(BS_COOKIE_PREFIX.'acp_menu','cookie',PLIB_Input::STRING);
			
			$this->tpl->set_template('navi.htm');
			$this->tpl->add_variables(array(
				'charset' => 'charset='.BS_HTML_CHARSET,
				'cookie_prefix' => BS_COOKIE_PREFIX,
				'cookie_init_value' => ($menu != null) ? $menu : '',
				'cookie_path' => $this->cfg['cookie_path'],
				'cookie_domain' => $this->cfg['cookie_domain'],
			  'page_title' => sprintf($this->locale->lang('page_title'),BS_VERSION)
			));
			
			$this->_load_modules();
			
			echo $this->tpl->parse_template();
			
			$this->_finish();
	
			$this->_send_document(BS_ENABLE_ADMIN_GZIP);
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}
	
	/**
	 * Loads all modules for the menu
	 *
	 */
	private function _load_modules()
	{
		$c = 0;
		$m = 0;
		$tpl_categories = array();
		
		foreach(BS_ACP_Menu::get_instance()->get_menu_items() as $cat)
		{
			$tpl_categories[$c] = array(
				'id' => $c,
				'title' => $this->locale->lang($cat['title']),
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
					if($access == 'default' && $this->auth->has_access_to_module($mod))
						$has_access = true;
					else if($access = 'admin' && $this->user->is_admin())
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
						'title' => $this->locale->lang($data['title']),
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
		
		$this->tpl->add_array('categories',$tpl_categories);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
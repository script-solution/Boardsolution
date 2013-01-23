<?php
/**
 * Contains the acp-navi-document-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.src.document
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	 * @see FWS_Document::render()
	 *
	 * @return string
	 */
	public function render()
	{
		$this->prepare_rendering();
		
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();

		$menu = $input->get_var(BS_COOKIE_PREFIX.'acp_menu','cookie',FWS_Input::STRING);
			
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
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();

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
		
		$tpl->add_variable_ref('categories',$tpl_categories);
	}
}
?>
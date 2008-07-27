<?php
/**
 * Contains the edit-submodule for tpleditor
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit sub-module for the tpleditor-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_tpleditor_edit extends BS_ACP_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACP_ACTION_EDIT_TPL,'edit');

		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		$file = $input->get_var('file','get',PLIB_Input::STRING);
		$doc->add_breadcrumb(
			$locale->lang('edit'),
			$url->get_acpmod_url(0,'&amp;action=edit&amp;path='.$path.'&amp;file='.$file)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$file = $input->get_var('file','get',PLIB_Input::STRING);
		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		$def_path = $helper->get_path_in_default();
		
		if(is_file($path.'/'.$file))
			$real_file = $path.'/'.$file;
		else
			$real_file = $def_path.'/'.$file;

		if(!is_file($real_file))
		{
			$this->report_error();
			return;
		}
		
		$target_url = $url->get_acpmod_url(
			0,'&amp;action=edit&amp;path='.$path.'&amp;file='.$file.'&amp;at='.BS_ACP_ACTION_EDIT_TPL
		);
		
		$file_content = file_get_contents($real_file);
		$file_content = htmlspecialchars($file_content);
		$file_content = str_replace('&#123;','{',$file_content);
		$file_content = str_replace('&#125;','}',$file_content);
		
		$cpath = '';
		$path_links = '';
		foreach(PLIB_Array_Utils::advanced_explode('/',$path) as $part)
		{
			$cpath .= $part.'/';
			$murl = $url->get_acpmod_url('tpleditor','&amp;path='.$cpath);
			$path_links .= '<a href="'.$murl.'">'.$part.'</a>/';
		}
		
		$tpl->set_template('tpleditor_formular.htm');
		$tpl->add_variables(array(
			'target_url' => $target_url,
			'image' => BS_ACP_Utils::get_instance()->get_file_image($real_file),
			'filename' => $path_links.$file,
			'filesize' => number_format(filesize($real_file),0,',','.'),
			'last_modification' => PLIB_Date::get_date(filemtime($real_file)),
			'file_content' => $file_content,
			'back_url' => $url->get_acpmod_url(0,'&amp;action=view&amp;path='.$path),
			'back_button' => true
		));
		$tpl->restore_template();
	}
}
?>
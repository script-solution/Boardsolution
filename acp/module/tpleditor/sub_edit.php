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
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_TPL,'edit');

		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		$file = $input->get_var('file','get',FWS_Input::STRING);
		$url = BS_URL::get_acpsub_url();
		$url->set('path',$path);
		$url->set('file',$file);
		$renderer->add_breadcrumb($locale->lang('edit'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$file = $input->get_var('file','get',FWS_Input::STRING);
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
		
		$file_content = file_get_contents($real_file);
		$file_content = htmlspecialchars($file_content);
		$file_content = str_replace('&#123;','{',$file_content);
		$file_content = str_replace('&#125;','}',$file_content);
		
		$cpath = '';
		$path_links = '';
		foreach(FWS_Array_Utils::advanced_explode('/',$path) as $part)
		{
			$cpath .= $part.'/';
			$murl = BS_URL::get_acpmod_url('tpleditor');
			$murl->set('path',$cpath);
			$path_links .= '<a href="'.$murl->to_url().'">'.$part.'</a>/';
		}
		
		
		$target = BS_URL::get_acpsub_url();
		$target->set('path',$path);
		$target->set('file',$file);
		$target->set('at',BS_ACP_ACTION_EDIT_TPL);
		
		$back = BS_URL::get_acpsub_url(0,'view');
		$back->set('path',$path);
		
		$tpl->set_template('tpleditor_formular.htm');
		$tpl->add_variables(array(
			'target_url' => $target->to_url(),
			'image' => BS_ACP_Utils::get_file_image($real_file),
			'filename' => $path_links.$file,
			'filesize' => number_format(filesize($real_file),0,',','.'),
			'last_modification' => FWS_Date::get_date(filemtime($real_file)),
			'file_content' => $file_content,
			'back_url' => $back->to_url(),
			'back_button' => true
		));
		$tpl->restore_template();
	}
}
?>
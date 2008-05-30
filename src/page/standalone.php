<?php
/**
 * Contains the standalone-page-class
 *
 * @version			$Id: standalone.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The page for all standalone-files.
 *
 * @package			Boardsolution
 * @subpackage	src.page
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Page_Standalone extends BS_Document
{
	/**
	 * The current module
	 *
	 * @var BS_Standalone
	 */
	private $_module;

	/**
	 * The name of the current module
	 *
	 * @var string
	 */
	private $_module_name;

	/**
	 * constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
			
			$page = $this->input->correct_var(
				BS_URL_PAGE,'get',PLIB_Input::STRING,array('acp','front'),'front'
			);
	
			$this->_module = $this->_load_module();
			if($this->_module === null)
			{
				PLIB_Helper::error('Unable to load module');
				return;
			}
	
			if($this->_module->use_output_buffering())
				$this->_start_document($page == 'acp' ? BS_ENABLE_ADMIN_GZIP : $this->cfg['enable_gzip']);
			
			// run the module
			if(!$this->_module->require_board_access() || $this->auth->has_board_access())
			{
				// set template if required
				if($this->_module->get_template() != '')
				{
					$this->tpl->set_template($this->_module->get_template());
					
					// populate header
					$this->tpl->set_template('inc_popup_header.htm');
					$this->tpl->add_variables(array(
						'page_title' => $this->cfg['forum_title'],
						'charset' => 'charset='.BS_HTML_CHARSET
					));
					$this->tpl->restore_template();
				}
				
				$this->_module->run();
			
				if($this->_module->get_template() != '')
				{
					// notify the template if an error has occurred
					$this->tpl->add_global('module_error',$this->_module->error_occurred());
				
					$this->msgs->print_messages();
					echo $this->tpl->parse_template($this->_module->get_template());
				}
			}
			
			$this->_finish();
	
			if($this->_module->use_output_buffering())
				$this->_send_document($page == 'acp' ? BS_ENABLE_ADMIN_GZIP : $this->cfg['enable_gzip']);
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}

	/**
	 * Loads the corresponding module
	 *
	 */
	private function _load_module()
	{
		$page = $this->input->get_var(BS_URL_PAGE,'get',PLIB_Input::STRING);
		
		$this->_module_name = PLIB_Helper::get_standalone_name(
			$this,'BS_'.$page.'_Standalone_',BS_URL_ACTION,$page.'/standalone/'
		);
		$class = 'BS_'.$page.'_Standalone_'.$this->_module_name;
		if(class_exists($class))
		{
			$c = new $class();
			return $c;
		}

		$c = null;
		return $c;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
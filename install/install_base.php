<?php
/**
 * Contains the install-base-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The base class for the installation-modules
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_Install extends BS_Base
{
	/**
	 * The selected language
	 *
	 * @var string
	 */
	var $lang_name;
	
	/**
	 * The current step
	 *
	 * @var integer
	 */
	var $step;
	
	/**
	 * The created entries for the current check
	 *
	 * @var array
	 */
	var $_check;
	
	/**
	 * The result of the check
	 *
	 * @var array
	 */
	var $_check_result;
	
	/**
	 * constructor
	 * 
	 * @param string $path the path to bs
	 * @param int $step the current step
	 */
	public function BS_Install($path,$step)
	{
		BS_Base::BS_Base($path,array('sql_helper','sess','url','unread','forums','cache','db'));
		
		$this->lang_name = $this->input->correct_var(
			'lang','get',PLIB_Input::STRING,array('en','ger_du','ger_sie'),'ger_du'
		);
		$this->step = $step;
		$this->_log = '';
		
		$this->tpl->set_path($path.'install/templates/');
		$this->locale->add_language_file('lang_install.php',$this->lang_name);
		
		include_once($path.'install/install_functions.php');
		$this->functions = &new BS_InstallFunctions($this);
	}
	
	/**
	 * Displays this object / performs the action
	 *
	 */
	public function display()
	{
		session_name('PHPSESSID');
		session_start();
		
		$this->functions->transfer_to_session();
		
		// navigate backwards?
		if($this->input->isset_var('back','post') && $this->step > 0)
		{
			$phpself = $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING);
			// we can't use functions->redirect() here because BS_FOLDER_URL is not set yet
			header('Location: '.$phpself.'?step='.($this->step - 1).'&lang='.$this->lang_name);
			exit;
		}
		
		// check the current step
		$this->_check = array();
		$this->_check_result = $this->check_inputs($this->_check);
		$redirect = $this->input->isset_var('forward','post') ||
			$this->input->get_var('forward','get',PLIB_Input::INTEGER) == 1;
		
		// navigate forwards?
		if($this->_check_result[0] && $redirect)
		{
			$phpself = $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING);
			header('Location: '.$phpself.'?step='.($this->step + 1).'&lang='.$this->lang_name);
			exit;
		}
		
		$this->functions->start_document();
		
		$this->_display_head();
		$this->run();
		$this->_display_foot();
		
		$this->finish();
		
		$this->functions->send_document();
	}
	
	/**
	 * Displays the head
	 *
	 */
	public function _display_head()
	{
		$phpself = $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING);
		$this->tpl->set_template('inc_header.htm',0);
		$this->tpl->add_variables(array(
			'show_lang_choose' => $this->step < 5,
			'target_url' => $phpself.'?step='.$this->step.'&amp;lang='.$this->lang_name,
			'step' => $this->step,
			'sel_ger_du' => $this->lang_name == 'ger_du' ? ' selected="selected"' : '',
			'sel_ger_sie' => $this->lang_name == 'ger_sie' ? ' selected="selected"' : '',
			'sel_en' => $this->lang_name == 'en' ? ' selected="selected"' : '',
			'show_form' => $this->step < 5,
			'charset' => 'charset='.BS_HTML_CHARSET
		));
		echo $this->tpl->parse_template();
		
		if($this->step < 5)
			$this->functions->display_navigation('top');
		
		// display errors?
		if(!$this->_check_result[0])
		{
			$errors = '<ul>'."\n";
			foreach($this->_check_result[1] as $value)
				$errors .= '<li>'.$value.'</li>'."\n";
			$errors .= '</ul>'."\n";
		
			$this->tpl->set_template('errors.htm',0);
			$this->tpl->add_variables(array(
				'errors' => $errors
			));
			echo $this->tpl->parse_template();
		}
	}
	
	/**
	 * Displays the foot
	 *
	 */
	public function _display_foot()
	{
		if($this->step < 5)
			$this->functions->display_navigation('bottom');
		
		$this->tpl->set_template('inc_footer.htm',0);
		echo $this->tpl->parse_template();
	}
	
	/**
	 * This method will be invoked to start the action.
	 * The sub-class should implement all stuff in this method
	 *
	 */
	public function run()
	{
		// will be implemented by the sub-class
	}
	
	/**
	 * Checks the inputs of the current step
	 * 
	 * @param array $check an array which will contain the checked inputs after the call
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(<error>,array(<errorMsg1>,...))
	 * 	</code>
	 */
	public function check_inputs(&$check)
	{
		// should be overwritten of the sub-classes
		return array(true,array());
	}
}
?>
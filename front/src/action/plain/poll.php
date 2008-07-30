<?php
/**
 * Contains the plain-poll-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to create a poll (without the topic)
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_Poll extends BS_Front_Action_Plain
{
	/**
	 * All options of the poll in text-form. One option per line! (\n as separator)
	 *
	 * @var string
	 */
	private $_options;
	
	/**
	 * An array with the options we want to use
	 *
	 * @var array
	 */
	private $_option_lines = null;
	
	/**
	 * Wether multichoice is allowed
	 *
	 * @var boolean
	 */
	private $_multichoice;
	
	/**
	 * The created poll-id
	 *
	 * @var int
	 */
	private $_poll_id = null;
	
	/**
	 * Constructor
	 * 
	 * @param string $options All options of the poll in text-form. One option per line!
	 * 	(\n as separator)
	 * @param boolean $multichoice Wether multichoice is allowed
	 */
	public function __construct($options,$multichoice)
	{
		parent::__construct();
		
		$this->_options = (string)$options;
		$this->_multichoice = (bool)$multichoice;
	}
	
	/**
	 * @return int the poll-id that has been created (will be available after check_data())
	 */
	public function get_poll_id()
	{
		return $this->_poll_id;
	}
	
	public function check_data()
	{
		$cfg = FWS_Props::get()->cfg();

		// calculate the options
		$this->_option_lines = array();
		$lines = explode("\n",$this->_options);
		foreach($lines as $line)
		{
			if(trim($line) != '')
				$this->_option_lines[] = trim($line);
		}

		// check if the user has entered more than 1 and less than the maximum options
		$number_of_lines = count($this->_option_lines);
		if($number_of_lines < 2)
			return 'pollmoeglichkeitenleer';

		if($number_of_lines > $cfg['max_poll_options'])
			return 'max_poll_options';
		
		$this->_poll_id = BS_DAO::get_polls()->get_next_id();
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = FWS_Props::get()->db();

		parent::perform_action();

		$db->start_transaction();
		
		// insert the poll-options
		foreach($this->_option_lines as $option)
			BS_DAO::get_polls()->create($this->_poll_id,$option,$this->_multichoice);
		
		$db->commit_transaction();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>
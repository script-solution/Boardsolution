<?php
/**
 * Contains the messages-task for the miscellaneous module
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The task to refresh the texts of messages
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Miscellaneous_Tasks_Messages extends FWS_Object implements FWS_Progress_Task
{
	/**
	 * The number of posts
	 *
	 * @var int
	 */
	private $_posts;
	
	/**
	 * The number of pms
	 *
	 * @var int
	 */
	private $_pms;
	
	/**
	 * The number of signatures
	 *
	 * @var int
	 */
	private $_sigs;
	
	/**
	 * The number of links
	 *
	 * @var int
	 */
	private $_links;
	
	/**
	 * The number of events
	 *
	 * @var int
	 */
	private $_events;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->_pms = BS_DAO::get_pms()->get_count();
		$this->_posts = BS_DAO::get_posts()->get_count();
		$this->_sigs = BS_DAO::get_user()->get_user_count();
		$this->_links = BS_DAO::get_links()->get_count();
		$this->_events = BS_DAO::get_events()->get_count();
	}
	
	/**
	 * @see FWS_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return max($this->_pms,$this->_posts,$this->_sigs,$this->_links,$this->_events);
	}

	/**
	 * @see FWS_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		// refresh linklist-descriptions
		if($pos < $this->_links)
		{
			foreach(BS_DAO::get_links()->get_list(-1,'id','ASC',$pos,$ops) as $data)
			{
				$text = '';
				BS_PostingUtils::prepare_message_for_db(
					$text,addslashes($data['link_desc_posted']),'desc'
				);
				
				BS_DAO::get_links()->update_text($data['id'],$text,$data['link_desc_posted']);
			}
		}
		
		// refresh event-descriptions
		if($pos < $this->_events)
		{
			foreach(BS_DAO::get_events()->get_list($pos,$ops) as $data)
			{
				$text = '';
				BS_PostingUtils::prepare_message_for_db(
					$text,addslashes($data['description_posted']),'desc'
				);
				
				BS_DAO::get_events()->update($data['id'],array(
					'description' => $text
				));
			}
		}
		
		// refresh posts
		if($pos < $this->_posts)
		{
			foreach(BS_DAO::get_posts()->get_list('id','ASC',$pos,$ops) as $data)
			{
				$text = '';
				BS_PostingUtils::prepare_message_for_db(
					$text,addslashes($data['text_posted']),'posts',$data['use_smileys'],$data['use_bbcode']
				);
				
				BS_DAO::get_posts()->update($data['id'],array('text' => $text));
			}
		}

		// refresh PMs
		if($pos < $this->_pms)
		{
			foreach(BS_DAO::get_pms()->get_list('id','ASC',$pos,$ops) as $data)
			{
				$text = '';
				BS_PostingUtils::prepare_message_for_db(
					$text,addslashes($data['pm_text_posted']),'posts',true,true
				);
				
				BS_DAO::get_pms()->update_text($data['id'],$text,$data['pm_text_posted']);
			}
		}

		if($pos < $this->_sigs)
		{
			foreach(BS_DAO::get_profile()->get_users('p.id','ASC',$pos,$ops,-1,-1) as $data)
			{
				$text = '';
				BS_PostingUtils::prepare_message_for_db(
					$text,addslashes($data['signature_posted']),'sig'
				);
				
				BS_DAO::get_profile()->update_user_by_id(array('signatur' => $text),$data['id']);
			}
		}
		
		// clear warnings here (highlighting-limit e.g.)
		$msgs = FWS_Props::get()->msgs();
		$msgs->clear_type(FWS_Document_Messages::WARNING);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
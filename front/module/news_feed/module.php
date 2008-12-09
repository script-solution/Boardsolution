<?php
/**
 * Contains the news-feed-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Outputs the news in a given feed-syntax.
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_news_feed extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_raw_renderer();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$doc = FWS_Props::get()->doc();

		if(!$cfg['enable_news_feeds'])
		{
			$this->report_error();
			return;
		}
		
		$mode = $input->correct_var(BS_URL_MODE,'get',FWS_Input::STRING,array('rss20','atom'),'rss20');
		
		// load feed-generator
		if($mode == 'rss20')
			$rss = new BS_Front_Feed_RSS20();
		else
			$rss = new BS_Front_Feed_Atom();
		
		// output
		$xml = $rss->get_news();
		
		$doc->set_mimetype('application/xml');
		$doc->set_header('Content-Type','application/xml');
		
		$renderer = $doc->use_raw_renderer();
		$renderer->set_content($xml);
	}
}
?>
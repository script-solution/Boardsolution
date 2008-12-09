<?php
/**
 * Contains the FAQ-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The FAQ-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_faq extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($cfg['enable_faq'] == 1);
		$renderer->add_breadcrumb($locale->lang('faq'),BS_URL::build_mod_url('faq'));
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$tpl = FWS_Props::get()->tpl();
		$cache = FWS_Props::get()->cache();
		$functions = FWS_Props::get()->functions();
		$locale->add_language_file('faq');

		$rank_names = array(
			'experience_bar' => 1,
			'register' => 2,
			'change_pw' => 3,
			'send_pw' => 4,
			'cookies' => 5,
			'ranks' => 6,
			'moderators' =>	7,
			'posts' => 8,
			'email' => 9,
			'wildcards' => 10,
			'pms' => 11,
			'bbcode' =>	12
		);
		
		$enable_bbcode = $cfg['posts_enable_bbcode'] || $cfg['sig_enable_bbcode'] ||
			$cfg['desc_enable_bbcode'];
		
		$conditions = array(
			$rank_names['moderators'] => $cfg['enable_moderators'] == 1,
			$rank_names['pms'] => $cfg['enable_pms'] == 1,
			$rank_names['bbcode'] => $enable_bbcode,
			$rank_names['ranks'] => $cfg['enable_user_ranks'] == 1,
			$rank_names['experience_bar'] => $cfg['post_stats_type'] != 'disabled' && $cfg['enable_post_count'] == 1
		);

		$faq_titles = array();
		$count = $locale->lang('faq_item_count');
		for($i = 1;$i <= $count;$i++)
		{
			if(!$this->_is_enabled($conditions,$i))
				continue;
			
			$faq_titles[] = array(
				'title' => $locale->lang('faq_title_'.$i)
			);
		}
		
		$tpl->add_array('faq_titles',$faq_titles);
		
		$faqs = array();
		for($i = 1;$i <= $count;$i++)
		{
			if(!$this->_is_enabled($conditions,$i))
				continue;

			$ranks = array();
			
			// add the ranks dynamicly
			switch($i)
			{
				case $rank_names['ranks']:
					// add the ranks
					$num = $cache->get_cache('user_ranks')->get_element_count();
					$a = 0;
					foreach($cache->get_cache('user_ranks') as $data)
					{
						$ranks[] = array(
							'points' => $data['post_from'].' - '.$data['post_to']." ".$locale->lang('points'),
							'image' => $functions->get_rank_images($num,$a,-1,(string)BS_STATUS_USER),
							'title' => $data['rank']
						);
						$a++;
					}
	
					if($cfg['enable_moderators'])
					{
						$ranks[] = array(
							'points' => '-',
							'image' => $functions->get_rank_images($num,$num - 1,-1,'',true),
							'title' => $locale->lang('moderator')
						);
					}
	
					// add admin
					$ranks[] = array(
						'points' => '-',
						'image' => $functions->get_rank_images($num,$num - 1,-1,(string)BS_STATUS_ADMIN),
						'title' => $locale->lang('administrator')
					);
					
					$faq_text = sprintf(
						$locale->lang('faq_text_'.$i),
						BS_EXPERIENCE_FOR_POST,
						BS_EXPERIENCE_FOR_TOPIC
					);
					break;
				
				case $rank_names['experience_bar']:
					$faq_text = sprintf(
						$locale->lang('faq_text_'.$i.'_'.$cfg['post_stats_type']),
						BS_URL::get_standalone_url('user_experience')->set(BS_URL_ID,0)->to_url()
					);
					break;
				
				case $rank_names['bbcode']:
					$rurl = BS_URL::get_mod_url('redirect');
					$durl = BS_URL::get_standalone_url('download');
					$faq_text = sprintf(
						$locale->lang('faq_text_'.$i),
						$rurl->set(BS_URL_LOC,'show_post')->set(BS_URL_ID,1)->to_url(),
						$rurl->set(BS_URL_LOC,'show_topic')->set(BS_URL_TID,1)->to_url(),
						$durl->set('path','uploads/file.txt')->to_url(),
						$durl->set('path','uploads/image.jpg')->to_url(),
						highlight_string("<?php\necho \"test\";\n?>",1)
					);
					break;
				
				default:
					$faq_text = $locale->lang('faq_text_'.$i);
					break;
			}
			
			$faqs[] = array(
				'key' => $i,
				'title' => $locale->lang('faq_title_'.$i),
				'text' => $faq_text,
				'show_ranks' => $i == $rank_names['ranks'],
				'ranks' => $ranks
			);
		}
		
		$tpl->add_array('faqs',$faqs);
	}

	/**
	 * checks wether the FAQ-entry with given key is enabled
	 *
	 * @param array $conditions an associative array with the conditions
	 * @param int $key the key of the entry
	 * @return boolean true if the FAQ-entry is enabled
	 */
	private function _is_enabled($conditions,$key)
	{
		if(isset($conditions[$key]))
			return $conditions[$key];

		return true;
	}
}
?>
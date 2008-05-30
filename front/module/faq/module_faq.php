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
	public function run()
	{
		$this->locale->add_language_file('faq');

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
		
		$enable_bbcode = $this->cfg['posts_enable_bbcode'] || $this->cfg['sig_enable_bbcode'] ||
			$this->cfg['lnkdesc_enable_bbcode'];
		
		$conditions = array(
			$rank_names['moderators'] => $this->cfg['enable_moderators'] == 1,
			$rank_names['pms'] => $this->cfg['enable_pms'] == 1,
			$rank_names['bbcode'] => $enable_bbcode,
			$rank_names['ranks'] => $this->cfg['enable_user_ranks'] == 1,
			$rank_names['experience_bar'] => $this->cfg['post_stats_type'] != 'disabled' && $this->cfg['enable_post_count'] == 1
		);

		$faq_titles = array();
		$count = $this->locale->lang('faq_item_count');
		for($i = 1;$i <= $count;$i++)
		{
			if(!$this->_is_enabled($conditions,$i))
				continue;
			
			$faq_titles[] = array(
				'title' => $this->locale->lang('faq_title_'.$i)
			);
		}
		
		$this->tpl->add_array('faq_titles',$faq_titles,false);
		
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
					$num = $this->cache->get_cache('user_ranks')->get_element_count();
					$a = 1;
					foreach($this->cache->get_cache('user_ranks') as $data)
					{
						$ranks[] = array(
							'points' => $data['post_from'].' - '.$data['post_to']." ".$this->locale->lang('points'),
							'image' => $this->functions->get_rank_images($num,$a,-1,BS_STATUS_USER),
							'title' => $data['rank']
						);
						$a++;
					}
	
					if($this->cfg['enable_moderators'])
					{
						$ranks[] = array(
							'points' => '-',
							'image' => $this->functions->get_rank_images($num,$num,-1,-1,true),
							'title' => $this->locale->lang('moderator')
						);
					}
	
					// add admin
					$ranks[] = array(
						'points' => '-',
						'image' => $this->functions->get_rank_images($num,$num,-1,BS_STATUS_ADMIN),
						'title' => $this->locale->lang('administrator')
					);
					
					$faq_text = sprintf(
						$this->locale->lang('faq_text_'.$i),
						BS_EXPERIENCE_FOR_POST,
						BS_EXPERIENCE_FOR_TOPIC
					);
					break;
				
				case $rank_names['experience_bar']:
					$faq_text = sprintf(
						$this->locale->lang('faq_text_'.$i.'_'.$this->cfg['post_stats_type']),
						$this->url->get_standalone_url('front','user_experience','&amp;'.BS_URL_ID.'=0')
					);
					break;
				
				case $rank_names['bbcode']:
					$faq_text = sprintf(
						$this->locale->lang('faq_text_'.$i),
						$this->url->get_url('redirect','&amp;'.BS_URL_LOC.'=show_post&amp;'.BS_URL_ID.'=1'),
						$this->url->get_url('redirect','&amp;'.BS_URL_LOC.'=show_topic&amp;'.BS_URL_TID.'=1'),
						$this->url->get_standalone_url('front','download','&amp;path=uploads/file.txt'),
						$this->url->get_standalone_url('front','download','&amp;path=image.jpg'),
						highlight_string("<?php\necho \"test\";\n?>",1)
					);
					break;
				
				default:
					$faq_text = $this->locale->lang('faq_text_'.$i);
					break;
			}
			
			$faqs[] = array(
				'key' => $i,
				'title' => $this->locale->lang('faq_title_'.$i),
				'text' => $faq_text,
				'show_ranks' => $i == $rank_names['ranks'],
				'ranks' => $ranks
			);
		}
		
		$this->tpl->add_array('faqs',$faqs,false);
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

	public function get_location()
	{
		return array($this->locale->lang('faq') => $this->url->get_url('faq'));
	}

	public function has_access()
	{
		return $this->cfg['enable_faq'] == 1;
	}
}
?>
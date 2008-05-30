<?php
/**
 * Contains the formular-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.html
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The HTML-formular for Boardsolution which may check for attachments and preview, too.
 *
 * @package			Boardsolution
 * @subpackage	src.html
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_HTML_Formular extends PLIB_HTML_Formular
{
	/**
	 * Constructor
	 *
	 * @param boolean $check_attachments sets wether the attachment-form-vars should be checked
	 * @param boolean $check_preview sets wether the preview-var should be checked
	 */
	public function __construct($check_attachments = true,$check_preview = false)
	{
		$condition = $this->doc->get_action_result() === -1;
		if(!$condition && $check_attachments)
			$condition |= $this->input->isset_var('add_attachment','post') ||
						$this->input->isset_var('remove_attachment','post');
		if(!$condition && $check_preview)
			$condition |= $this->input->isset_var('preview','post');
		
		parent::__construct($condition);
	}
	
	protected function _get_js_calendar_style()
	{
		return $this->user->get_theme_item_path('calendar.css');
	}
	
	protected function _get_js_calendar_lang()
	{
		return PLIB_Javascript::get_instance()->get_file(
			'language/'.$this->user->get_language().'/calendar_lang.js'
		);
	}
	
	protected function _get_js_calendar_image()
	{
		return $this->user->get_theme_item_path('images/calendar.png');
	}

	/**
	 * Builds a combobox with the timezones
	 * 
	 * @param string $name the name of the combobox
	 * @param int $default the selected timezone
	 * @return string the HTML-code of the combobox
	 */
	public function get_timezone_combo($name = 'timezone',$default = 0)
	{
		$tz = timezone_identifiers_list();
		$zones = array();
		foreach($tz as $zone)
		{
			$parts = explode('/',$zone);
			$continent = isset($parts[0]) ? $parts[0] : '';
			// we don't want to have GMT, UTC etc. because they are deprecated and
			// confusing (GMT +/- reversed and no daylight-saving)
			if($continent == 'Etc')
				continue;
			
			if(!isset($zones[$continent]))
				$zones[$continent] = array();
			
			$city = isset($parts[1]) ? $parts[1] : '';
			$cname = isset($parts[2]) ? $city.'/'.$parts[2] : $city;
			// skip empty names
			if($cname == '')
				continue;
			
			$zones[$continent][] = array($zone,str_replace('_',' ',$cname));
		}
		
		$default = ($default == 0) ? $this->user->get_profile_val('timezone') : $default;
		$html = '<select name="'.$name.'">'."\n";
		foreach($zones as $continent => $cities)
		{
			// skip empty continents
			if(count($cities) > 0)
			{
				$html .= '	<optgroup label="'.$continent.'">'."\n";
				foreach($cities as $city)
				{
					$html .= '		<option';
					if($city[0] == $default)
						$html .= ' selected="selected"';
					$html .= ' value="'.$city[0].'">'.$city[1].'</option>'."\n";
				}
				$html .= '	</optgroup>'."\n";
			}
		}
		$html .= '</select>'."\n";
		return $html;
	}
}
?>
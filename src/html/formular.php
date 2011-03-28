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
final class BS_HTML_Formular extends FWS_HTML_Formular
{
	/**
	 * Constructor
	 *
	 * @param boolean $check_attachments sets wether the attachment-form-vars should be checked
	 * @param boolean $check_preview sets wether the preview-var should be checked
	 */
	public function __construct($check_attachments = true,$check_preview = false)
	{
		$doc = FWS_Props::get()->doc();
		$input = FWS_Props::get()->input();
		$renderer = $doc->get_renderer();
		
		if($renderer instanceof FWS_Document_Renderer_HTML_Default)
			$condition = $renderer->get_action_result() === -1;
		else
			$condition = false;
		
		if(!$condition && $check_attachments)
			$condition |= $input->isset_var('add_attachment','post') ||
						$input->isset_var('remove_attachment','post');
		if(!$condition && $check_preview)
			$condition |= $input->isset_var('preview','post');
		
		parent::__construct($condition);
	}
	
	protected function get_js_calendar_style()
	{
		$user = FWS_Props::get()->user();

		return $user->get_theme_item_path('calendar.css');
	}
	
	protected function get_js_calendar_lang()
	{
		$user = FWS_Props::get()->user();

		return FWS_Javascript::get_instance()->get_file(
			'language/'.$user->get_language().'/calendar_lang.js'
		);
	}
	
	protected function get_js_calendar_image()
	{
		$user = FWS_Props::get()->user();

		return $user->get_theme_item_path('images/calendar.png');
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
		$user = FWS_Props::get()->user();

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
		
		$default = ($default == 0) ? $user->get_profile_val('timezone') : $default;
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
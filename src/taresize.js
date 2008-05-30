/**
 * Contains the resize-javascript for textareas
 * 
 * @version			$Id: taresize.js 612 2008-04-24 19:33:30Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

var ta_dragging = false;
var ta_id;
var ta_resizer_id;

/**
 * Init function
 * 
 * @param mixed id the id of the textarea
 * @param mixed rid the id of the resizer
 */
function BS_taResizer(id,rid)
{
	ta_id = id;
	ta_resizer_id = rid;
}

/**
 * Starts the dragging
 *
 * @param ev the event-args
 */
function BS_startDrag(ev)
{
	if(!ev)
    ev = window.event;

	var tar = PLIB_getElement(ta_resizer_id);
	if(tar == null)
		return;
	
	// determine positions
	var evy = (document.all && !window.opera) ? ev.clientY + document.documentElement.scrollTop : ev.pageY;
	var tary = PLIB_getPageOffsetTop(tar);
	
	// just move if we are over the resizer-element (with a little bit tolerance)
	if(evy >= tary - 3 && evy <= tary + tar.offsetHeight + 3)
		ta_dragging = true;
}

/**
 * Stops the dragging
 *
 * @param ev the event-args
 */
function BS_stopDrag(ev)
{
	ta_dragging = false;
}

/**
 * Resizes the textarea if we're currently dragging
 *
 * @param ev the event-args
 */
function BS_moveMouse(ev)
{
	if(!ev)
    ev = window.event;

	if(!ta_dragging)
		return;
	
	var ta = PLIB_getElement(ta_id);
	var tar = PLIB_getElement(ta_resizer_id);
	if(ta == null || tar == null)
		return;
	
	// determine positions
	var evy = (document.all && !window.opera) ? ev.clientY + document.documentElement.scrollTop : ev.pageY;
	var tay = PLIB_getPageOffsetTop(ta);
	
	// set new height
	var newHeight = evy - tay - 10;
	ta.style.height = newHeight + "px";
	
	// clear selection in IE (very annoying there..)
	if(document.all && !window.opera)
		document.selection.clear();
}

// register events
document.onmousedown = BS_startDrag;
document.onmouseup = BS_stopDrag;
document.onmousemove = BS_moveMouse;
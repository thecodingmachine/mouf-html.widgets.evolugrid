<?php
namespace Mouf\Html\Widgets\EvoluGrid;

/**
 * This Interface defines how to implement an item (row) description element that will cary some additionnal information about an item of the data loaded in the grid.
 * 
 * Typically, this could be a row that slides down, a simple title attriute, a modal window etc.
 * 
 * @author Kevin Nguyen
 *
 */
interface RowEventListernerInterface {
	
	const EVENT_CLICK = 'click';
	const EVENT_DBLCLICK = 'dblclick';
	const EVENT_HOVER = 'hover'; 	
	/**
	 * Returns the event that will be called from the row element and will trigger the listener's callback
	 * @return string
	 */
	public function getEventName();

	/**
	 * Returns the callback function that takes the row variable as parameter
	 * @return string
	 */
	public function getCallback();
	
}
<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Html\Widgets\EvoluGrid\ItemDescriptionRendererInterface;

class ToggleSlideRowDescription implements RowEventListernerInterface {

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\ItemDescriptionInterface::getRowClickCallback()
	 */
	public function getEventName(){
		return RowEventListernerInterface::EVENT_CLICK;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\RowEventListernerInterface::getCallback()
	 */
	public function getCallback(){
		return "function(row, event){ alert(row.id) }";
	}
	
}
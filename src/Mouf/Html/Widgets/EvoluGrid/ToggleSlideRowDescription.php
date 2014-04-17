<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Html\Widgets\EvoluGrid\ItemDescriptionRendererInterface;

class ToggleSlideRowDescription implements RowEventListernerInterface {

	/**
	 * The Key of the JS object that contains the value to display as description
	 * @var string
	 */
	public $descriptionKey;
	
	/**
	 * The name of the event that will trigger the description row to appear
	 * Might be one of 'click', 'dblclick', or 'hover'
	 * @var string
	 */
	public $eventName;
	
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\ItemDescriptionInterface::getRowClickCallback()
	 */
	public function getEventName(){
		return $this->eventName;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\RowEventListernerInterface::getCallback()
	 */
	public function getCallback(){
		return "
		function(row, event){
			var parentRow = $(event.currentTarget);
			if (!parentRow.hasClass('deployed')){
				var description = row.$this->descriptionKey;
				var rowElem = $('<tr/>').hide();
				rowElem.append($('<td/>').attr('colspan', parentRow.children().length).html(description)).insertAfter(parentRow);
				rowElem.fadeIn();
				parentRow.addClass('deployed');
			}else{
				parentRow.removeClass('deployed');
				var descriptionRow = parentRow.next();
				descriptionRow.fadeOut().delay().remove();
			}
		}";
	}
	
}
<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Common\ConditionInterface\ConditionInterface;

/**
 * A column of an EvoluGrid that renders a cell of the EvoluGrid using a JS function.
 * The Javascript function is passed directly in this class, as an anonymous function
 * taking one parameter (the row) in parameter.
 * The function should return a jQuery object representing the cell to display.
 * Here is a sample to display a link:	
 * 	function(row) { return $("&lt;a/&gt;").text(row["name"]).attr("href", "/mylink.php?id="+row.id) }
 * 
 * @author David Negrier
 */
class JsColumn implements EvoluColumnJsInterface {
	/**
	 * The title of the column to display
	 * 
	 * @var string
	 */
	private $title;

	/**
	 * Get the JS function used to display the cell.
	 * 
	 * @var string
	 */
	private $jsRenderer;
	
	/**
	 * The key to sort upon (or null if the column is not sortable)
	 *
	 * @var string
	 */
	private $sortKey;

	/**
	 * The width of the column.
	 *
	 * @var string
	 */
	private $width;
	
	/**
	 * This condition must be matched to display the column.
	 * Otherwise, the column is not displayed.
	 * The displayCondition is optional. If no condition is set, the column will always be displayed.
	 *
	 * @var ConditionInterface
	 */
	private $displayCondition;
	
	/**
	 * @Important
	 * @param string $title The title of the column to display
	 * @param string $jsRenderer Returns the JS function to be used to render the cell. Here is a sample to display a link:	function(row) { return $("&lt;a/&gt;").text(row["name"]).attr("href", "/mylink.php?id="+row.id) }
	 * @param string $sortKey The key to sort upon (or null if the column is not sortable)
	 * @param int $width Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc... This is optionnal. Leave empty to let the browser decide.
	 * @param ConditionInterface $displayCondition
	 */
	public function __construct($title, $jsRenderer, $sortKey = null, $width = null, $displayCondition = null) {
		$this->title = $title;
		$this->jsRenderer = $jsRenderer;
		$this->sortKey = $sortKey;
		$this->width = $width;
		$this->displayCondition = $displayCondition;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnInterface::getTitle()
	 */
	public function getTitle() {
		return $this->title;
	}


	public function getJsRenderer() {
		return $this->jsRenderer;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnKeyInterface::getSortKey()
	 */
	public function getSortKey() {
		return $this->sortKey;
	}
	
	/**
	 * Returns true if the column is sortable, and false otherwise.
	 */
	public function isSortable() {
		return $this->sortKey != null;
	}
	
	/**
	 * Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc...
	 * This is optionnal. Leave empty to let the browser decide.
	 *
	 * @return string
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * If this function returns true, the column should not be displayed.
	 *
	 * @return bool
	 */
	public function isHidden() {
		if ($this->displayCondition == null) {
			return false;
		}
		return !$this->displayCondition->isOk();
	}
}

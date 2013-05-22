<?php
namespace Mouf\Html\Widgets\EvoluGrid;
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
	 * 
	 * @param string $title The title of the column to display
	 * @param string $jsRenderer Returns the JS function to be used to render the cell. Here is a sample to display a link:	function(row) { return $("&lt;a/&gt;").text(row["name"]).attr("href", "/mylink.php?id="+row.id) }
	 */
	public function __construct($title, $jsRenderer) {
		$this->title = $title;
		$this->jsRenderer = $jsRenderer;
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

}

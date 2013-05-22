<?php
namespace Mouf\Html\Widgets\EvoluGrid;

/**
 * Classes implementing this interface represent a column in an evolugrid that is rendered by using a JS function.
 * If you want advanced display (for instance if you want to display links, images, text with colors, etc...),
 * your class should implement this interface. For simple display (if you just want to output the value in 
 * a resultset, head for the EvoluColumnKeyInterface.
 * 
 * @author david
 */
interface EvoluColumnJsInterface extends EvoluColumnInterface {
	/**
	 * Returns the JS function to be used to render the cell.
	 * Here is a sample to display a link:
	 * 	function(row) { return $("&lt;a/&gt;").text(row["name"]).attr("href", "/mylink.php?id="+row.id) }
	 * 
	 * @return string
	 */
	public function getJsRenderer();	
}
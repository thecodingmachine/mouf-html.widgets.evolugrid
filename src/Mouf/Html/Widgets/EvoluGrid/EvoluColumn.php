<?php
namespace Mouf\Html\Widgets\EvoluGrid;

/**
 * A column of an EvoluGrid
 * 
 * @author david
 * @Component
 */
class EvoluColumn {
	/**
	 * The title of the column to display
	 * 
	 * @Important
	 * @var string
	 */
	public $title;
	
	/**
	 * Get the key to map to in the datagrid.
	 * 
	 * @Important
	 * @var string
	 */
	public $key;
	
	/**
	 * If set, this JS function will be used to render the cell.
	 * Here is a sample to display a link:
	 * 	function(row) { return $("&lt;a/&gt;").text(row["name"]).attr("href", "/mylink.php?id="+row.idclient) }
	 * 
	 * @var string
	 */
	public $jsrenderer;
	
	/**
	 * 
	 * @param string $title The title of the column to display
	 * @param string $key Get the key to map to in the datagrid.
	 */
	public function __construct($title = null, $key=null) {
		$this->title = $title;
		$this->key = $key;
	}
}
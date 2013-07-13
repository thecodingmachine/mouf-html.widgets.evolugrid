<?php
namespace Mouf\Html\Widgets\EvoluGrid;

/**
 * Classes implementing this interface represent a column in an evolugrid.
 * Please note that all columns should in fact implement one of the 2 inherited interfaces:
 * - EvoluColumnKeyInterface
 * - or EvoluColumnJSInterface
 * 
 * @author david
 */
interface EvoluColumnInterface {
	/**
	 * Returns the title of the column to display.
	 * 
	 * @return string
	 */
	public function getTitle();
	
	/**
	 * Returns the key to map to in the datagrid. If you are using the "SimpleColumn" class,
	 * the data associated to the key will be directly displayed. If you are using a class
	 * extending the EvoluColumnJsInterface, the key is not directly displayed. Instead, a JS function is called
	 * for the rendering. Nonetheless, the key is always sent back to the server if you are trying to
	 * apply a sort on a sortable column.
	 *
	 * @return string
	 */
	public function getKey();
	
	/**
	 * Returns true if the column is sortable, and false otherwise.
	 */
	public function isSortable();
}
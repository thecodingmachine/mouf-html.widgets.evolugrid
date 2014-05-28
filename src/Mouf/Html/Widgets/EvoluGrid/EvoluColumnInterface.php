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
	 * Returns true if the column is sortable, and false otherwise.
	 * 
	 * @return bool
	 */
	public function isSortable();
	
	/**
	 * Returns the key to sort upon in the datagrid.
	 *
	 * @return string
	 */
	public function getSortKey();
	
	/**
	 * Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc...
	 * This is optionnal. Leave empty to let the browser decide.
	 *
	 * @return string
	 */
	public function getWidth();
	
	/**
	 * If this function returns true, the column should not be displayed.
	 *
	 * @return bool
	 */
	public function isHidden();

    /**
     * Returns the class of the cells of this column
     * @return string
     */
    public function getClass();
	
}
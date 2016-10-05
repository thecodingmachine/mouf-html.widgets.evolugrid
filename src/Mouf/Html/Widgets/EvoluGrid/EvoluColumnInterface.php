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
	public function getTitle() : string;
	
	/**
	 * Returns true if the column is sortable, and false otherwise.
	 * 
	 * @return bool
	 */
	public function isSortable() : bool;
	
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
     * Returns the CSS class of the cells of this column
     * @return string
     */
    public function getClass();

    /**
     * Returns a (HTML) representation of the row.
     * @return string
     */
    public function render($row);

    /**
     * Returns true if the column escapes HTML, and false otherwise.
     *
     * @return bool
     */
    public function isEscapeHTML() : bool;

    /**
     * Returns true if the column should be displayed in HTML pages
     *
     * @return bool
     */
    public function isDisplayed() : bool;

    /**
     * Returns true if the column should be exported in CSV
     *
     * @return bool
     */
    public function isExported() : bool;
}

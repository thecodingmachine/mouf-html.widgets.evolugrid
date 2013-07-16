<?php
namespace Mouf\Html\Widgets\EvoluGrid;
/**
 * A column of an EvoluGrid that renders a key of the resultset.
 * 
 * @author David Negrier
 */
class SimpleColumn implements EvoluColumnKeyInterface {
	/**
	 * The title of the column to display
	 * 
	 * @Important
	 * @var string
	 */
	private $title;

	/**
	 * Get the key to map to in the datagrid.
	 * 
	 * @Important
	 * @var string
	 */
	private $key;

	/**
	 * True if the column is sortable, false otherwise.
	 * 
	 * @var bool
	 */
	private $sortable;
	
	/**
	 * The width of the column.
	 * 
	 * @var string
	 */
	private $width;
	/**
	 * @Important $title
	 * @Important $key
	 * @Important $sortable
	 * @Important $width
	 * @param string $title The title of the column to display
	 * @param string $key Get the key to map to in the datagrid.
	 * @param bool $sortable True if the column is sortable, false otherwise.
	 * @param int $width Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc... This is optionnal. Leave empty to let the browser decide.
	 */
	public function __construct($title, $key, $sortable = false, $width = null) {
		$this->title = $title;
		$this->key = $key;
		$this->sortable = $sortable;
		$this->width = $width;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnInterface::getTitle()
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnKeyInterface::getKey()
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Returns true if the column is sortable, and false otherwise.
	 */
	public function isSortable() {
		return $this->sortable;
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
	
}

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
	 * @Important $title
	 * @Important $key
	 * @param string $title The title of the column to display
	 * @param string $key Get the key to map to in the datagrid.
	 * @param bool $sortable True if the column is sortable, false otherwise.
	 */
	public function __construct($title, $key, $sortable = false) {
		$this->title = $title;
		$this->key = $key;
		$this->sortable = $sortable;
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
}

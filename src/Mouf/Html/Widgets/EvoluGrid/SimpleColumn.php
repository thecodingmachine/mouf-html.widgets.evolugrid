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
	 * @Important $title
	 * @Important $key
	 * @param string $title The title of the column to display
	 * @param string $key Get the key to map to in the datagrid.
	 */
	public function __construct($title, $key) {
		$this->title = $title;
		$this->key = $key;
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

}

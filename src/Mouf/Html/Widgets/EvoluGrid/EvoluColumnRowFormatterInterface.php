<?php
namespace Mouf\Html\Widgets\EvoluGrid;

/**
 * Classes implementing this interface represent a column in an evolugrid that could be rendered by using a formatter.
 * 
 * @author Nicolas
 */
interface EvoluColumnRowFormatterInterface extends EvoluColumnInterface {
	/**
	 * Format the row passed in parameter
	 * 
	 * @param array $row
	 * @return array
	 */
	public function formatRow($row);	
}
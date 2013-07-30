<?php
namespace Mouf\Html\Widgets\EvoluGrid;

/**
 * Classes implementing this interface represent a column in an evolugrid that could be rendered by using a formatter.
 * 
 * @author Pierre
 */
interface EvoluColumnFormatterInterface extends EvoluColumnInterface {
	/**
	 * Returns the formatter used
	 * 
	 */
	public function getFormatter();	
}
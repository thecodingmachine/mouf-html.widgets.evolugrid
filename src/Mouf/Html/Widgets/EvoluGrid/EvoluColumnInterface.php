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
}
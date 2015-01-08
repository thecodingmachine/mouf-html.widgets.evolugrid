<?php
namespace Mouf\Html\Widgets\EvoluGrid;

class EvoluGridColumn{
	
	/**
	 * Tells if the column should be displayed in the Grid view
	 * @var bool
	 */
	private $display = true;
	
	/**
	 * Tells if the column should be added to the Grid's export
	 * @var bool
	 */
	private $export = true;
	
	public function doDisplay(){
		return $this->display !== false;
	}
	
	public function doExport(){
		return $this->export !== false;
	}
	
	/**
	 * Tells if the column should be added to the Grid's export, default is TRUE
	 * @Important
	 * @param bool $display
	 */
	public function setDisplay($display){
		$this->display = $display;
	}

	/**
	 * Tells if the column should be added to the Grid's export, default is TRUE
	 * @Important
	 * @param bool $export
	 */
	public function setExport($export){
		$this->export = $export;
	}
	
}
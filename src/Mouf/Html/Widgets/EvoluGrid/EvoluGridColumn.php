<?php
namespace Mouf\Html\Widgets\EvoluGrid;

class EvoluGridColumn{
	
	/**
	 * Tells if the column should be displayed in the Grid view
	 * @var bool
	 */
    protected $display = true;
	
	/**
	 * Tells if the column should be added to the Grid's export
	 * @var bool
	 */
	protected $export = null;
	
	public function isDisplayed() : bool
    {
		return $this->display === true;
	}
	
	public function isExported() : bool
    {
		return $this->export === true;
	}
	
	/**
	 * Tells if the column should be added to the Grid's export, default is TRUE
	 * @Important IfSet
	 * @param bool $display
	 */
	public function setDisplay(bool $display){
		$this->display = $display;
	}

	/**
	 * Tells if the column should be added to the Grid's export, default is TRUE
	 * @Important IfSet
	 * @param bool $export
	 */
	public function setExport(bool $export){
		$this->export = $export;
	}
	
}
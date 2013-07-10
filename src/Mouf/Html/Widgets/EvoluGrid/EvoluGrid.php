<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Common\UrlInterface;
use Mouf\Html\HtmlElement\HtmlElementInterface;

/**
 * This class represents a grid that can be rendered using the EvoluGrid JS jQuery plugin.
 *
 * @ExtendedAction {"name":"Generate from SQL", "url":"evolugrid/updateFromSql", "default":false}
 * @author David Negrier
 */
class EvoluGrid implements HtmlElementInterface{
	
	/**
	 * @var string
	 */
	private $id;
	
	/**
	 * @var string
	 */
	private $class;
	
	/**
	 * @var boolean
	 */
	private $exportCSV;
	
	/**
	 * @var int
	 */
	private $limit = 100;
	
	/**
	 * @var UrlInterface|string
	 */
	private $url;
	
	/**
	 * @var string
	 */
	private $formSelector;
	
	/**
	 * Replaces the pagination by an infinite scroll.
	 * 
	 * @var bool
	 */
	private $infiniteScroll = false;
	
	/**
	 * URL that will be called in Ajax and return the data to display.
	 *
	 * @Property
	 * @param UrlInterface|string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * Maximum number of rows displayed before pagination.
	 * Defaults to 100.
	 *
	 * @Property
	 * @param int $limit
	 */
	public function setLimit($limit) {
		if($limit)
			$this->limit = $limit;
		else
			$this->limit = 100;
	}
	
	private static $nbGridCount = 0;
	
	/**
	 * The id of the evolugrid.
	 * 
	 * @Property
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * The class of the evolugrid.
	 * 
	 * @Property
	 * @param string $class
	 */
	public function setClass($class) {
		$this->class = $class;
	}
	
	/**
	 * Export the grid to CSV format.
	 * 
	 * @Property
	 * @param boolean $exportCSV
	 */
	public function setExportCSV($exportCSV) {
		$this->exportCSV = $exportCSV;
	}
	
	/**
	 * Form selector of the controller to filter data.
	 * 
	 * @Property
	 * @param string $formSelector
	 */
	public function setFormSelector($formSelector) {
		$this->formSelector = $formSelector;
	}
	
	/**
	 * Replaces the pagination by an infinite scroll.
	 *
	 * @param bool $infiniteScroll
	 */
	public function setInfiniteScroll($infiniteScroll) {
		$this->infiniteScroll = $infiniteScroll;
	}

	/**
	 * Renders the object in HTML.
	 * The Html is echoed directly into the output.
	 *
	 */
	public function toHtml() {
		$descriptor = new \stdClass();
		
		if ($this->url instanceof UrlInterface) {
			$url = $this->url->getUrl();
		} else {
			$url = ROOT_URL.$this->url;
		}
		
		$descriptor->url = $url;
		$descriptor->tableClasses = $this->class;
		$descriptor->export_csv = $this->exportCSV;
		$descriptor->limit = $this->limit;
		$descriptor->infiniteScroll = $this->infiniteScroll;
		
		if ($this->formSelector){
			$descriptor->filterForm = $this->formSelector;
		}
		
		$descriptorJSON = json_encode($descriptor);
	
		$id = $this->id;
		if ($id == null) {
			$id = "evolugrid_number_".self::$nbGridCount;
			self::$nbGridCount++;
		}
		
		
		echo '
			<div id="'.$id.'"></div>
			<script type="text/javascript">
				$(document).ready(function() {
				    var descriptor = '.$descriptorJSON.';
				    $("#'.$id.'").evolugrid(descriptor);
				});
			</script> 
		';
	}
}
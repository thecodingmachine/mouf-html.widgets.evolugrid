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
	 * The search form that will be displayed just before the grid.
	 * If you want to put the search form somewhere else, you do not have to use this property.
	 * You can instead ue the formSelector to point to a form anywhere on your page. 
	 * 
	 * @var HtmlElementInterface
	 */
	private $searchForm;
	
	
	
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
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * The class of the evolugrid.
	 * 
	 * @param string $class
	 */
	public function setClass($class) {
		$this->class = $class;
	}
	
	/**
	 * Export the grid to CSV format.
	 * 
	 * @param boolean $exportCSV
	 */
	public function setExportCSV($exportCSV) {
		$this->exportCSV = $exportCSV;
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
	 * A CSS form selector that points to the form used to filter data.
	 * This is optionnal if you are using the formHtmlElement.
	 *
	 * @param string $formSelector
	 */
	public function setFormSelector($formSelector) {
		$this->formSelector = $formSelector;
	}
	
	/**
	 * The search form that will be displayed just before the grid.
	 * If you want to put the search form somewhere else, you do not have to use this property.
	 * You can instead ue the formSelector to point to a form anywhere on your page.
	 *
	 * @param HtmlElementInterface $searchForm
	 */
	public function setSearchForm(HtmlElementInterface $searchForm = null) {
		$this->searchForm = $searchForm;
		return $this;
	}
	

	/**
	 * Renders the object in HTML.
	 * The Html is echoed directly into the output.
	 *
	 */
	public function toHtml() {

		$id = $this->id;
		if ($id == null) {
			$id = "evolugrid_number_".self::$nbGridCount;
			self::$nbGridCount++;
		}
		
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
		} elseif ($this->searchForm) {
			$descriptor->filterForm = '#'.$id.'__searchform form';
		}
		
		
		$descriptorJSON = json_encode($descriptor);
					
		echo '
			<div id="'.$id.'__evolugrid_holder">
				';
		if ($this->searchForm) {
			echo '<div id="'.$id.'__searchform">';
			$this->searchForm->toHtml();
			echo '</div>';
		}
		echo '
				<div id="'.$id.'"></div>
			</div>
			<script type="text/javascript">
				$(document).ready(function() {
				    var descriptor = '.$descriptorJSON.';
				    $("#'.$id.'").evolugrid(descriptor);
				});
			</script> 
		';
	}
	
}
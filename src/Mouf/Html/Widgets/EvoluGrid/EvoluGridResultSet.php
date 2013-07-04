<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Value\ValueUtils;

use Mouf\Utils\Value\IntValueInterface;

use Mouf\Utils\Common\PaginableInterface;

use Mouf\Mvc\Splash\Services\SplashRoute;

use Mouf\MoufManager;

use Mouf\Mvc\Splash\Services\UrlProviderInterface;

use Mouf\Utils\Common\UrlInterface;

use Mouf\Utils\Value\ArrayValueInterface;

use Mouf\Database\QueryWriter\QueryResult;

use Mouf\Utils\Action\ActionInterface;

/**
 * This class represents the JSON result that can be sent to an evolugrid to display results.
 *
 * @author David Negrier
 */
class EvoluGridResultSet implements ActionInterface, UrlProviderInterface,
		UrlInterface {

	const FORMAT_JSON = 'json';
	const FORMAT_CSV = 'csv';

	/**
	 * @var ArrayValueInterface
	 */
	private $results;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var array<EvoluColumnInterface>
	 */
	private $columns = array();

	/**
	 * The data to be displayed by the grid.
	 *
	 * @var array<stdObject>
	 */
	private $rows = array();

	/**
	 * The total number of rows (!= from the number of rows returned by the grid, used to paginate)
	 * @var int
	 */
	private $count = null;

	/**
	 * The format to use when outputing data.
	 * Can be self::FORMAT_JSON or self::FORMAT_CSV
	 * @var unknown
	 */
	private $format = null;
	
	private $limit;
	private $offset;

	/**
	 * Sets the result set to display.
	 *
	 * @param ArrayValueInterface $results
	 */
	public function setResults($results) {
		$this->results = $results;
	}
	
	/**
	 * Sets the total number of records for this resultset (used to paginate results).
	 * Warning, total number of rows != from the number of rows returned by the grid
	 *
	 * @param IntValueInterface $count
	 */
	public function setTotalRowsCount($count) {
		$this->count = $count;
	}
	
	/**
	 * URL that exposes this result set.
	 *
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * The list of columns displayed in the grid.
	 *
	 * @param array<EvoluColumnInterface> $columns
	 */
	public function setColumns($columns) {
		$this->columns = $columns;
	}

	/**
	 * Add a new column to the grid.
	 *
	 * @param EvoluColumn $column
	 */
	public function addColumn(EvoluColumn $column) {
		$this->columns[] = $column;
	}

	/**
	 * The format to use to export the data.
	 * It can be "json" or "csv".
	 * If none is specified, JSON will be used.
	 * 
	 * @param string $format
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Utils\Action\ActionInterface::run()
	 */
	public function run() {
		if ($this->offset == null && isset($_GET['offset'])) {
			$this->offset = $_GET['offset'];
		}
		if ($this->limit == null && isset($_GET['limit']) && !empty($_GET['limit'])) {
			$this->limit = $_GET['limit'];
		}
		
		
		$this->output($this->format);
	}

	/**
	 * Outputs the data in the format passed in parameter (json OR csv)
	 * If format is empty, we default to JSON
	 * 
	 * @param string $format
	 * @param string $filename
	 * @throws \Exception
	 */
	public function output($format = null, $filename = "data.csv") {
		if ($format == null) {
			$format = $this->format;
			if ($format == null) {
				$format = self::FORMAT_JSON;
			}
		}

		$autoBuildColumns = false;
		$columnsByKey = array();
		if (empty($this->columns)) {
			$autoBuildColumns = true;
		}

		if ($format == self::FORMAT_JSON) {

			$jsonMessage = array();

			$descriptor = array();
			if ($this->count !== null) {
				$jsonMessage['count'] = ValueUtils::val($this->count);
			}

			// TODO: Build the $autoBuildColumns mechanism!!!!!!
			
			if ($this->results instanceof PaginableInterface) {
				$this->results->paginate($this->limit, $this->offset);
			}
			
			$resultArray = ValueUtils::val($this->results);

			$resultData = array();
			$columns = $this->columns;
			foreach ($resultArray as $rowArray) {
				if ($autoBuildColumns) {
					foreach ($rowArray as $key => $cell) {
						if (!isset($columnsByKey[$key])) {
							$columnsByKey[$key] = true;
							// Let's create a column whose title is the key.
							$columns[] = new SimpleColumn($key, $key);
						}
					}
				}
				$resultData[] = (array) $rowArray;
			}

			$jsonMessage['data'] = $resultData;

			$columnsArr = array();
			foreach ($columns as $column) {
				/* @var $column EvoluColumnInterface */
				$columnArr = array("title" => $column->getTitle());
				if ($column instanceof EvoluColumnKeyInterface) {
					$columnArr['display'] = $column->getKey();
				}
				if ($column instanceof EvoluColumnJsInterface) {
					$columnArr['jsdisplay'] = $column->getJsRenderer();
				}
				$columnsArr[] = $columnArr;
			}
			$descriptor['columns'] = $columnsArr;

			$jsonMessage['descriptor'] = $descriptor;
			echo json_encode($jsonMessage);
		} elseif ($format == self::FORMAT_CSV) {

			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename");
			header("Content-Type: mime/type");
			header("Content-Transfer-Encoding: binary");
			$fp = fopen("php://output", "w");

			$this->outputCsv($fp);
		} else {
			throw new \Exception(
					"The output format '" . $format . "' is not supported");
		}
	}

	public function saveCsv($filePath) {
		$fp = fopen($filePath, "w");
		$this->outputCsv($fp);
	}

	private function outputCsv($fp) {
		// TODO: enable autoBuildColumns on CSV
		$columnsTitles = array_map(
				function (EvoluColumn $column) {
					return utf8_decode($column->title);
				}, $this->columns);
		fputcsv($fp, $columnsTitles, ";");
		foreach ($this->getRows() as $row) {
			$columns = array_map(
					function (EvoluColumn $elem) use ($row) {
						if (is_object($row)) {
							$key = $elem->key;
							if (property_exists($row, $key)) {
								return ($row->$key == "") ? " "
										: utf8_decode($row->$key);
							} else {
								return " ";
							}
						} else {
							if (isset($row[$elem->key])) {
								return ($row[$elem->key] == "") ? " "
										: utf8_decode($row[$elem->key]);
							} else {
								return " ";
							}
						}
					}, $this->columns);
			fputcsv($fp, $columns, ";");

		}

		fclose($fp);
	}

	/**
	 * Returns the URL represented by this object, as a string.
	 *
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Returns the list of URLs that can be accessed, and the function/method that should be called when the URL is called.
	 *
	 * @return array<SplashRoute>
	 */
	function getUrlsList() {
		$instanceName = MoufManager::getMoufManager()->findInstanceName($this);
	
		$route = new SplashRoute($this->url, $instanceName, "run", null, "Ajax call by Evolugrid.");
		return array($route);
	}
}

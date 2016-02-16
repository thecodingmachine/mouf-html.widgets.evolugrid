<?php
declare(strict_types=1);

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
use Mouf\Utils\Common\SortableInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

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
	 * @var ArrayValueInterface|array
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
	 * @var IntValueInterface|int
	 */
	private $count = null;

    /**
     * The encoding of the csv file output
     * //IGNORE remove unexcepting notice when some special character cannot be converted
     * Don't forget to add it if you change the encoding
     * @var string
     */
    private $csvEncoding = "CP1252//IGNORE";

	/**
	 * The format to use when outputing data.
	 * Can be self::FORMAT_JSON or self::FORMAT_CSV
	 * @var string
	 */
	private $format = null;

    private $csvFilename = "data.csv";
	

	private $limit;
	private $offset;
	private $sortKey;
	private $sortOrder;

    private $additionnalData;

	/**
	 * Sets the result set to display.
	 *
	 * @param ArrayValueInterface|array $results
	 */
	public function setResults($results) {
		$this->results = $results;
	}

    public function getResults() {
        return $this->results;
    }
	
	/**
	 * Sets the total number of records for this resultset (used to paginate results).
	 * Warning, total number of rows != from the number of rows returned by the grid
	 *
	 * @param IntValueInterface|int $count
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
     * The filename of your generated CSV file
     * @param string $fileName
     */
    public function setCsvFilename($fileName) {
        $this->csvFilename = $fileName;
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
	 * @param EvoluColumnInterface $column
	 */
	public function addColumn(EvoluColumnInterface $column) {
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
     * @param mixed $additionnalData
     */
    public function setAdditionnalData($additionnalData)
    {
        $this->additionnalData = $additionnalData;
    }
    public function setRows($rows) {
        $this->rows = $rows;
    }

    public function getRows() {
        return $this->rows;
    }

    /**
     * The encoding of the csv file output
     * @param string $encoding
     */
    public function setCsvEncoding($encoding) {
        $this->csvEncoding = $encoding;
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
		if ($this->sortKey == null && isset($_GET['sort_key']) && !empty($_GET['sort_key'])) {
			$this->sortKey = $_GET['sort_key'];
		}
		if ($this->sortOrder == null && isset($_GET['sort_order']) && !empty($_GET['sort_order'])) {
			$this->sortOrder = $_GET['sort_order'];
		}
		if ($this->format == null && isset($_GET['output']) && !empty($_GET['output'])) {
			$this->format = $_GET['output'];
		}
		
		return $this->getResponse($this->format, $this->csvFilename);
	}

	/**
	 * Outputs the data in the format passed in parameter (json OR csv)
	 * If format is empty, we default to JSON
	 * 
	 * @param string $format
	 * @param string $filename
	 * @throws \Exception
	 */
	public function getResponse($format = null, $filename = "data.csv") : Response
	{
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

			if ($this->results instanceof PaginableInterface) {
				$this->results->paginate($this->limit, $this->offset);
			}
			if ($this->results instanceof SortableInterface && !empty($this->sortKey)) {
				$this->results->sort($this->sortKey, $this->sortOrder);
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
							$columns[] = new SimpleColumn($key, $key, true);
						}
					}
				}
				if ($rowArray instanceof \Iterator) {
					$tmpArray = array();
					foreach ($rowArray as $key => $value) {
						$tmpArray[$key] = $value;
					}
					$resultData[] = $tmpArray;
				} else {
					$resultData[] = (array) $rowArray;
				}
			}

			$columnsArr = array();
			foreach ($columns as $column) {
				if (!$column->isHidden() && $column->doDisplay()) {
					/* @var $column EvoluColumnInterface */
					$columnArr = array("title" => $column->getTitle());
					$columnArr['sortable'] = $column->isSortable();
					$columnArr['sortKey'] = $column->getSortKey();
                    $columnArr['cssClass'] = $column->getClass();
					$width = $column->getWidth();
					if ($width) {
						$columnArr['width'] = $width;
					}
					if ($column instanceof EvoluColumnJsInterface) {
						$columnArr['jsdisplay'] = $column->getJsRenderer();
					}
					if ($column instanceof EvoluColumnKeyInterface) {
						$columnArr['display'] = $column->getKey();
						$columnArr['escapeHTML'] = $column->isEscapeHTML();
					}
					$columnsArr[] = $columnArr;
					
					if (($column instanceof EvoluColumnFormatterInterface) && ($column->getFormatter() != null)) {
						foreach ($resultData as $key=>$row) {
							$formatter = $column->getFormatter();
							$resultData[$key][$column->getKey()] = $formatter->format($row[$column->getKey()]);
						}
					}
					if ($column instanceof EvoluColumnRowFormatterInterface) {
						foreach ($resultData as $key=>$row) {
							$resultData[$key] = $column->formatRow($row);
						}
					}
				}
			}

			$jsonMessage['data'] = $resultData;
			
			$descriptor['columns'] = $columnsArr;

			$jsonMessage['descriptor'] = $descriptor;
            $jsonMessage['additionnalData'] = $this->additionnalData;
			return new Response\JsonResponse($jsonMessage);
		} elseif ($format == self::FORMAT_CSV) {

			$fp = fopen("php://temp", "w");

			$this->outputCsv($fp);
			rewind($fp);
			$content = stream_get_contents($fp);

			fclose($fp);

			return new Response\HtmlResponse($content, 200, [
				"Cache-Control" => "public",
				"Content-Description" => "File Transfer",
				"Content-Disposition" => "attachment; filename=$filename",
				"Content-Type" => "mime/type",
				"Content-Transfer-Encoding" => "binary"
			]);
		} else {
			throw new \Exception(
					"The output format '" . $format . "' is not supported");
		}
	}

	public function saveCsv($filePath) {
		$fp = fopen($filePath, "w");
		$this->outputCsv($fp);
		fclose($fp);
	}

	private function outputCsv($fp) {
        foreach($this->columns as $key => $column) {
        	/* @var $column EvoluGridColumn */
            if (!($column instanceof EvoluColumnKeyInterface) || !$column->doExport()){
                unset($this->columns[$key]);
            }
        }

        // Let's only keep the columns that are tied to keys (we cannot render JS in CSV exports)
        $keyColumns = array_filter($this->columns, function(EvoluColumnInterface $column) {
            return $column instanceof EvoluColumnKeyInterface;
        });

		// TODO: enable autoBuildColumns on CSV
		$columnsTitles = array_map(
				function (EvoluColumnKeyInterface $column) {
					return iconv("UTF-8", $this->csvEncoding, $column->getTitle());
				}, $keyColumns);
		fputcsv($fp, $columnsTitles, ";");

        $resultArray = ValueUtils::val($this->results);

		foreach ($resultArray as $row) {
			$columns = array_map(
					function (EvoluColumnKeyInterface $elem) use ($row) {
                        if (($elem instanceof EvoluColumnFormatterInterface) && ($elem->getFormatter() != null)) {
                            $formatter = $elem->getFormatter();
                            $row[$elem->getKey()] = $formatter->format($row[$elem->getKey()]);
                        }
                        if ($elem instanceof EvoluColumnRowFormatterInterface) {
                            if(is_array($row)) {
                                $row = $elem->formatRow($row);
                            }
                        }
						if (is_object($row)) {
							$key = $elem->getKey();
							if (property_exists($row, $key)) {
								return ($row->$key == "") ? " "
										: iconv("UTF-8", $this->csvEncoding, strip_tags($row->$key));
							} else {
								return " ";
							}
						} else {
							if (isset($row[$elem->getKey()])) {
								return ($row[$elem->getKey()] == "") ? " "
										: iconv("UTF-8", $this->csvEncoding, strip_tags($row[$elem->getKey()]));
							} else {
								return " ";
							}
						}
					}, $keyColumns);
			fputcsv($fp, $columns, ";");
		}

	}

	/**
	 * Returns the URL represented by this object, as a string.
	 *
	 * @return string
	 */
	public function getUrl() {
		return ROOT_URL.$this->url;
	}

	/**
	 * Returns the list of URLs that can be accessed, and the function/method that should be called when the URL is called.
	 *
	 * @return array<SplashRoute>
	 */
	public function getUrlsList($instanceName) {
		if ($this->url != null) {
			$instanceName = MoufManager::getMoufManager()->findInstanceName($this);
			$route = new SplashRoute($this->url, $instanceName, "run", null, "Ajax call by Evolugrid.");
			return array($route);
		} else {
			return array();
		}
	}
}

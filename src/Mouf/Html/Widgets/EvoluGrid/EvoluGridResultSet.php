<?php

declare (strict_types = 1);

namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Value\ValueUtils;
use Mouf\Utils\Value\IntValueInterface;
use Mouf\Utils\Common\PaginableInterface;
use Mouf\Utils\Common\UrlInterface;
use Mouf\Utils\Value\ArrayValueInterface;
use Mouf\Utils\Action\ActionInterface;
use Mouf\Utils\Common\SortableInterface;
use Porpaginas\Page;
use Porpaginas\Result;
use TheCodingMachine\Splash\Services\SplashRoute;
use TheCodingMachine\Splash\Services\UrlProviderInterface;
use Zend\Diactoros\Response;

/**
 * This class represents the JSON result that can be sent to an evolugrid to display results.
 *
 * @author David Negrier
 */
class EvoluGridResultSet implements ActionInterface, UrlProviderInterface,
        UrlInterface
{
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
     * @var EvoluColumnInterface[]
     */
    private $columns = array();

    /**
     * The data to be displayed by the grid.
     *
     * @var array<stdObject>
     */
    private $rows = array();

    /**
     * The total number of rows (!= from the number of rows returned by the grid, used to paginate).
     *
     * @var IntValueInterface|int
     */
    private $count = null;

    /**
     * The encoding of the csv file output
     * //IGNORE remove unexcepting notice when some special character cannot be converted
     * Don't forget to add it if you change the encoding.
     *
     * @var string
     */
    private $csvEncoding = 'CP1252//IGNORE';

    /**
     * The format to use when outputing data.
     * Can be self::FORMAT_JSON or self::FORMAT_CSV.
     *
     * @var string
     */
    private $format = null;

    private $csvFilename = 'data.csv';

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
    public function setResults($results)
    {
        $this->results = $results;
    }

    public function getResults()
    {
        return $this->results;
    }

    /**
     * Sets the total number of records for this resultset (used to paginate results).
     * Warning, total number of rows != from the number of rows returned by the grid.
     *
     * @param IntValueInterface|int $count
     */
    public function setTotalRowsCount($count)
    {
        $this->count = $count;
    }

    /**
     * Returns the total number of records.
     *
     * @return int
     */
    private function getTotalRowsCount() : int
    {
        if ($this->results instanceof Result) {
            return (int) $this->results->count();
        } elseif ($this->results instanceof Page) {
            return (int) $this->results->totalCount();
        } else {
            return (int) ValueUtils::val($this->count);
        }
    }

    /**
     * URL that exposes this result set.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * The filename of your generated CSV file.
     *
     * @param string $fileName
     */
    public function setCsvFilename($fileName)
    {
        $this->csvFilename = $fileName;
    }

    /**
     * The list of columns displayed in the grid.
     *
     * @param array<EvoluColumnInterface> $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Add a new column to the grid.
     *
     * @param EvoluColumnInterface $column
     */
    public function addColumn(EvoluColumnInterface $column)
    {
        $this->columns[] = $column;
    }

    /**
     * The format to use to export the data.
     * It can be "json" or "csv".
     * If none is specified, JSON will be used.
     * 
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param mixed $additionnalData
     */
    public function setAdditionnalData($additionnalData)
    {
        $this->additionnalData = $additionnalData;
    }
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function getRows()
    {
        return $this->rows;
    }

    /**
     * The encoding of the csv file output.
     *
     * @param string $encoding
     */
    public function setCsvEncoding($encoding)
    {
        $this->csvEncoding = $encoding;
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Mouf\Utils\Action\ActionInterface::run()
     */
    public function run()
    {
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
     * If format is empty, we default to JSON.
     * 
     * @param string $format
     * @param string $filename
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getResponse($format = null, $filename = 'data.csv') : Response
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
            $count = $this->getTotalRowsCount();
            if ($count !== null) {
                $jsonMessage['count'] = $count;
            }

            $results = $this->results;

            if ($results instanceof Result) {
                $results = $results->take($this->offset, $this->limit);
            }

            if ($results instanceof PaginableInterface) {
                $results->paginate($this->limit, $this->offset);
            }
            if ($results instanceof SortableInterface && !empty($this->sortKey)) {
                $results->sort($this->sortKey, $this->sortOrder);
            }

            $resultArray = ValueUtils::val($results);

            $columns = $this->columns;
            if ($autoBuildColumns) {
                foreach ($resultArray as $rowArray) {
                    foreach ($rowArray as $key => $cell) {
                        if (!isset($columnsByKey[$key])) {
                            $columnsByKey[$key] = true;
                            // Let's create a column whose title is the key.
                            $columns[] = new SimpleColumn($key, $key, true);
                        }
                    }
                }
            }

            $resultData = [];
            foreach ($resultArray as $rowArray) {
                $row = [];
                foreach ($columns as $key => $column) {
                    $row['col'.$key] = $column->render($rowArray);
                }
                $resultData[] = $row;
            }

            $columnsArr = [];
            foreach ($columns as $key => $column) {
                if (!$column->isHidden() && $column->isDisplayed()) {
                    /* @var $column EvoluColumnInterface */
                    $columnArr = array('title' => $column->getTitle());
                    $columnArr['sortable'] = $column->isSortable();
                    $columnArr['sortKey'] = $column->getSortKey();
                    $columnArr['cssClass'] = $column->getClass();
                    $width = $column->getWidth();
                    if ($width) {
                        $columnArr['width'] = $width;
                    }
                    $columnArr['display'] = 'col'.$key;
                    $columnArr['escapeHTML'] = $column->isEscapeHTML();

                    $columnsArr[] = $columnArr;
                }
            }

            $jsonMessage['data'] = $resultData;

            $descriptor['columns'] = $columnsArr;

            $jsonMessage['descriptor'] = $descriptor;
            $jsonMessage['additionnalData'] = $this->additionnalData;

            return new Response\JsonResponse($jsonMessage);
        } elseif ($format == self::FORMAT_CSV) {
            $fp = fopen('php://temp', 'w');

            $this->outputCsv($fp);
            rewind($fp);
            $content = stream_get_contents($fp);

            fclose($fp);

            return new Response\HtmlResponse($content, 200, [
                'Cache-Control' => 'public',
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => "attachment; filename=$filename",
                'Content-Type' => 'mime/type',
                'Content-Transfer-Encoding' => 'binary',
            ]);
        } else {
            throw new \Exception(
                    "The output format '".$format."' is not supported");
        }
    }

    public function saveCsv($filePath)
    {
        $fp = fopen($filePath, 'w');
        $this->outputCsv($fp);
        fclose($fp);
    }

    private function outputCsv($fp)
    {
        $columns = array_filter($this->columns, function (EvoluColumnInterface $column) {
            return $column->isExported();
        });

        // TODO: enable autoBuildColumns on CSV
        $columnsTitles = array_map(
            function (EvoluColumnInterface $column) {
                return iconv('UTF-8', $this->csvEncoding, (string) $column->getTitle());
            }, $columns);
        fputcsv($fp, $columnsTitles, ';');

        $resultArray = ValueUtils::val($this->results);

        foreach ($resultArray as $row) {
            $results = array_map(
                function (EvoluColumnInterface $column) use ($row) {
                    return iconv('UTF-8', $this->csvEncoding, (string) $column->render($row));
                }, $columns);
            fputcsv($fp, $results, ';');
        }
    }

    /**
     * Returns the URL represented by this object, as a string.
     *
     * @return string
     */
    public function getUrl()
    {
        return ROOT_URL.$this->url;
    }

    /**
     * Returns the list of URLs that can be accessed, and the function/method that should be called when the URL is called.
     *
     * @return array<SplashRoute>
     */
    public function getUrlsList($instanceName)
    {
        if ($this->url != null) {
            $route = new SplashRoute($this->url, $instanceName, 'run', null, 'Ajax call by Evolugrid.');

            return array($route);
        } else {
            return array();
        }
    }

    /**
     * Returns a unique tag representing the list of SplashRoutes returned.
     * If the tag changes, the cache is flushed by Splash.
     *
     * Important! This must be quick to compute.
     *
     * @return mixed
     */
    public function getExpirationTag() : string
    {
        return md5($this->url);
    }
}

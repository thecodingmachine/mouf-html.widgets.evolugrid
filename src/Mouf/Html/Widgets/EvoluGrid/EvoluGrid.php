<?php

namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Common\UrlInterface;
use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\ValueUtils;

/**
 * This class represents a grid that can be rendered using the EvoluGrid JS jQuery plugin.
 *
 * @ExtendedAction {"name":"Generate from SQL", "url":"evolugrid/updateFromSql", "default":false}
 *
 * @author David Negrier
 */
class EvoluGrid implements HtmlElementInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
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
     * Enable the search history when the user click on the previous page button.
     *
     * @var bool
     */
    private $searchHistory = false;

    /**
     * Enable auto fill form for search history.
     *
     * @var bool
     */
    private $searchHistoryAutoFillForm = false;

    /**
     * Replaces the pagination by an infinite scroll.
     * 
     * @var bool
     */
    private $infiniteScroll = false;

    /**
     * The position of the row which will launch the ajax call for infinite scroll when scrolling (start by the end).
     * if empty, the default value is 5.
     *
     * @var int
     */
    private $infiniteScroll_ElementPosition = 5;

    /**
     * Fixed the header of the evolugrid table.
     * 
     * @var bool
     */
    private $fixedHeader = false;

    /**
     * CSS selector of the nav bar (to fix the evolugrid header just below).
     * If empty, the header is fixed to the top of the window.
     *
     * @var string
     */
    private $fixedHeader_NavBarSelector;

    /**
     * The search form that will be displayed just before the grid.
     * If you want to put the search form somewhere else, you do not have to use this property.
     * You can instead use the formSelector to point to a form anywhere on your page. 
     * 
     * @var HtmlElementInterface
     */
    private $searchForm;

    /**
     * The JS callback taking 1 argument and triggered when a row is clicked. JS function signature: function(rowObject)
     * The object passed in parameter is the row object.
     *
     * @var string
     */
    private $onRowClick;
    /**
     * A set of RowEventListernerInterface that will associate mouseevents ans associated callbacks to each row item.
     *
     * @var RowEventListernerInterface[]
     */
    private $rowEventListeners = array();

    /**
     * The JS callback called when results have been displayed.
     *
     * @var string
     */
    private $onResultShown;

    /**
     * If set, for each row, we will look in the dataset for the row, for the "key" passed in parameter. The associated value will be used as a class of the tr row.
     *
     * @var string
     */
    private $rowCssClass;

    /**
     * If the sortable param is set to true, this property will set the class of the <i> element in order to display the up arrow to sort our grid (default is bootstrap glyphicon, but you can use basically font-awesome if you have display troubles with firefox).
     *
     * @var string
     */
    private $chevronUpClass;

    /**
     * If the sortable param is set to true, this property will set the class of the <i> element in order to display the down arrow to sort our grid (default is bootstrap glyphicon, but you can use basically font-awesome if you have display troubles with firefox).
     *
     * @var string
     */
    private $chevronDownClass;

    /**
     * If the customLoader is set, this property allows setting a custom loader
     *
     * @var HtmlElementInterface
     */
    private $customLoader;

    /**
     * Message to display if no results are shown.
     *
     * @var ValueInterface|string
     */
    private $noResultsMessage;

    /**
     * Message to display if no more results are shown.
     *
     * @var ValueInterface|string
     */
    private $noMoreResultsMessage = "> No more results <";

    /**
     * The selector name for the DOM element that will receive the number of results.
     *
     * @var string
     */
    private $countTarget;

    /**
     * Disable the automatic search on first load of the grid.
     *
     * @var bool
     */
    private $loadOnInit = true;

    /**
     * Init the evolugrid on a specific page on load
     *
     * @var int
     */
    private $loadOnInitPage;

    /**
     * URL that will be called in Ajax and return the data to display.
     *
     * @Property
     *
     * @param UrlInterface|string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Getter for URL.
     *
     * @Property
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Maximum number of rows displayed before pagination.
     * Defaults to 100.
     *
     * @Property
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        if (!empty($limit) || $limit === 0) {
            $this->limit = $limit;
        } else {
            $this->limit = 100;
        }
    }

    private static $nbGridCount = 0;

    /**
     * The id of the evolugrid.
     * 
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * The class of the evolugrid.
     * 
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Export the grid to CSV format.
     * 
     * @param bool $exportCSV
     */
    public function setExportCSV($exportCSV)
    {
        $this->exportCSV = $exportCSV;
    }

    /**
     * Replaces the pagination by an infinite scroll.
     *
     * @param bool $infiniteScroll
     */
    public function setInfiniteScroll($infiniteScroll)
    {
        $this->infiniteScroll = $infiniteScroll;
    }

    /**
     * The position of the row which will launch the ajax call for infinite scroll when scrolling (start by the end).
     * if empty, the default value is 5.
     * 
     * @param int $infiniteScroll_ElementPosition
     */
    public function setInfiniteScrollElementPosition($infiniteScroll_ElementPosition)
    {
        $this->infiniteScroll_ElementPosition = $infiniteScroll_ElementPosition;
    }

    /**
     * Fixed the header of the evolugrid table.
     *
     * @param bool $fixedHeader
     */
    public function setFixedHeader($fixedHeader)
    {
        $this->fixedHeader = $fixedHeader;
    }

    /**
     * CSS selector of the nav bar (to fix the evolugrid header just below).
     * If empty, the header is fixed to the top of the window.
     * 
     * @param string $fixedHeader_NavBarSelector
     */
    public function setFixedHeaderNavBarSelector($fixedHeader_NavBarSelector)
    {
        $this->fixedHeader_NavBarSelector = $fixedHeader_NavBarSelector;
    }

    /**
     * A CSS form selector that points to the form used to filter data.
     * This is optionnal if you are using the formHtmlElement.
     *
     * @param string $formSelector
     */
    public function setFormSelector($formSelector)
    {
        $this->formSelector = $formSelector;
    }

    /**
     * @param string $countTarget
     */
    public function setCountTarget($countTarget)
    {
        $this->countTarget = $countTarget;
    }

    /**
     * Enable the search history when the user click on the previous page button.
     * 
     * @param bool $searchHistory
     */
    public function setSearchHistory($searchHistory)
    {
        $this->searchHistory = $searchHistory;
    }

    /**
     * Enable auto fill form for search history.
     *
     * @param bool $searchHistoryAutoFillForm
     */
    public function setSearchHistoryAutoFillForm($searchHistoryAutoFillForm)
    {
        $this->searchHistoryAutoFillForm = $searchHistoryAutoFillForm;
    }

    /**
     * The search form that will be displayed just before the grid.
     * If you want to put the search form somewhere else, you do not have to use this property.
     * You can instead ue the formSelector to point to a form anywhere on your page.
     *
     * @param HtmlElementInterface $searchForm
     */
    public function setSearchForm(HtmlElementInterface $searchForm = null)
    {
        $this->searchForm = $searchForm;

        return $this;
    }

    /**
     * The JS callback taking 1 argument and triggered when a row is clicked. JS function signature: function(rowObject)
     * The object passed in parameter is the row object.
     *
     * For instance: function(row) { console.log("Row clicked:" + row.id); }
     *
     * @param string $onRowClick
     */
    public function setOnRowClick($onRowClick)
    {
        $this->onRowClick = $onRowClick;

        return $this;
    }

    /**
     *  If the sortable param is set to true, this property will set the class of the <i> element in order to display the up arrow to sort our grid
     * (default is bootstrap glyphicon, but you can use basically font-awesome if you have display troubles with firefox).
     *
     * @param string $chevronUpClass
     *
     * @return $this
     */
    public function setChevronUpClass($chevronUpClass)
    {
        $this->chevronUpClass = $chevronUpClass;

        return $this;
    }

    /**
     *  If the sortable param is set to true, this property will set the class of the <i> element in order to display the down arrow to sort our grid
     * (default is bootstrap glyphicon, but you can use basically font-awesome if you have display troubles with firefox).
     *
     * @param string $chevronDownClass
     *
     * @return $this
     */
    public function setChevronDownClass($chevronDownClass)
    {
        $this->chevronDownClass = $chevronDownClass;

        return $this;
    }

    /**
     * A set of RowEventListernerInterface that will associate mouseevents ans associated callbacks to each row item.
     *
     * @param RowEventListernerInterface[] $rowEventListeners
     */
    public function setRowEventListeners($rowEventListeners)
    {
        $this->rowEventListeners = $rowEventListeners;

        return $this;
    }

    /**
     * An object generating HTML for the loader.
     *
     * The generated HTML MUST use
     *
     * @param HtmlElementInterface $customLoader
     * @return $this
     */
    public function setCustomLoader($customLoader) {
        $this->customLoader = $customLoader;
        return $this;
    }
	
    /**
     * @param string $onResultShown
     */
    public function setOnResultShown($onResultShown)
    {
        $this->onResultShown = $onResultShown;
    }

    /**
     * @return string
     */
    public function getOnResultShown()
    {
        return $this->onResultShown;
    }

    /**
     * If set, for each row, we will look in the dataset for the row, for the "key" passed in parameter. The associated value will be used as a class of the tr row.
     *
     * @param string $rowCssClass
     */
    public function setRowCssClass($rowCssClass)
    {
        $this->rowCssClass = $rowCssClass;
    }

    /**
     * @return ValueInterface|string
     */
    public function getNoResultsMessage()
    {
        return $this->noResultsMessage;
    }

    /**
     * @param ValueInterface|string $noResultsMessage
     */
    public function setNoResultsMessage($noResultsMessage)
    {
        $this->noResultsMessage = $noResultsMessage;
    }

    /**
     * @return ValueInterface|string
     */
    public function getNoMoreResultsMessage()
    {
        return $this->noMoreResultsMessage;
    }

    /**
     * If not set, message to display if no more results are shown will be '> No more results <'
     *
     * @param ValueInterface|string $noMoreResultsMessage
     */
    public function setNoMoreResultsMessage($noMoreResultsMessage): void
    {
        $this->noMoreResultsMessage = $noMoreResultsMessage;
    }

    /**
     * @return bool
     */
    public function isLoadOnInit()
    {
        return $this->loadOnInit;
    }

    /**
     * Disable the automatic search on first load of the grid
     * Default value : true.
     *
     * @param bool $loadOnInit
     */
    public function setLoadOnInit($loadOnInit)
    {
        $this->loadOnInit = $loadOnInit;
    }

    /**
     * Init the evolugrid on a page on load
     *
     * @param $page
     */
    public function setloadOnInitPage($page) {
        $this->loadOnInitPage = $page;
    }

    /**
     * Renders the object in HTML.
     * The Html is echoed directly into the output.
     */
    public function toHtml()
    {
        $id = $this->id;
        if ($id == null) {
            $id = 'evolugrid_number_'.self::$nbGridCount;
            ++self::$nbGridCount;
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
        $descriptor->infiniteScroll_ElementPosition = $this->infiniteScroll_ElementPosition;
        $descriptor->fixedHeader = $this->fixedHeader;
        $descriptor->fixedHeader_NavBarSelector = $this->fixedHeader_NavBarSelector;
        $descriptor->searchHistory = $this->searchHistory;
        $descriptor->searchHistoryAutoFillForm = $this->searchHistoryAutoFillForm;
        $descriptor->rowCssClass = $this->rowCssClass;
        $descriptor->noResultsMessage = ValueUtils::val($this->noResultsMessage);
        $descriptor->noMoreResultsMessage = ValueUtils::val($this->noMoreResultsMessage);
        $descriptor->countTarget = $this->countTarget;
        if ($this->chevronUpClass) {
            $descriptor->chevronUpClass = $this->chevronUpClass;
        }
        if ($this->chevronDownClass) {
            $descriptor->chevronDownClass = $this->chevronDownClass;
        }
        $descriptor->loadOnInit = $this->isLoadOnInit();

        $listeners = '[';
        foreach ($this->rowEventListeners as $listener) {
            /* @var $listener RowEventListernerInterface */
            $listeners .= "{event:'".$listener->getEventName()."', callback: ".$listener->getCallback().'}';
        }
        $listeners .= ']';

        if ($this->formSelector) {
            $descriptor->filterForm = $this->formSelector;
        } elseif ($this->searchForm) {
            $descriptor->filterForm = '#'.$id.'__searchform form';
        }

        if (isset($this->loadOnInitPage)) {
            $descriptor->loadOnInitPage = $this->loadOnInitPage;
        }

        $descriptorJSON = json_encode($descriptor);

        echo '
			<div id="'.$id.'__evolugrid_holder" style="position: relative">
				';
        if ($this->searchForm) {
            echo '<div id="'.$id.'__searchform">';
            $this->searchForm->toHtml();
            echo '</div>';
        }
        echo '
				<div id="'.$id.'"></div>';

        /** if the customLoader is set we use it instead of the default one **/
        if ($this->customLoader) {
            echo '<div class="ajaxLoader">';
            $this->customLoader->toHtml();
            echo '</div>';
        } else {
            if ($this->infiniteScroll) {
                echo '<div class="ajaxLoader" style="text-align: center; margin-top: 20px; margin-bottom: 20px; display: none;"><img src="'.ROOT_URL.'vendor/mouf/html.widgets.evolugrid/img/ajax-loader.gif" alt="ajax-loader"></div>';
            } else {
                echo '<div class="ajaxLoader" style="text-align: center; background-color: black; width: 100%; height: 100%; position: absolute; top: 0; opacity: 0.3"><img src="'.ROOT_URL.'vendor/mouf/html.widgets.evolugrid/img/ajax-loader.gif" alt="ajax-loader" style="margin-top: -20px; position: absolute; top: 50%;"></div>';
            }
        }
		echo '</div>
			<script type="text/javascript">
                (function($) {
                    $(document).ready(function() {
                        var descriptor = '.$descriptorJSON.';';

        if ($this->onRowClick) {
            echo '
                        descriptor.onRowClick = '.$this->onRowClick.';
                    ';
        }
        if ($this->rowEventListeners) {
            echo '
                        descriptor.rowEventListeners = '.$listeners.';
                    ';
        }
        if ($this->onResultShown) {
            echo '
                        descriptor.onResultShown = '.$this->onResultShown.';
                    ';
        }
        echo '
                        $("#'.$id.'").evolugrid(descriptor);
                    });
                })(jQuery);
			</script> 
		';
    }
}

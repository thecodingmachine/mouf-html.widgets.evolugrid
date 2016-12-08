<?php

namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Html\Widgets\EvoluGrid\Utils\ObjectToArrayCaster;
use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\ValueUtils;
use Twig_Environment;

/**
 * A column of an EvoluGrid that renders a key of the resultset.
 * 
 * @author David Negrier
 */
class TwigColumn extends EvoluGridColumn implements EvoluColumnInterface
{
    use CssClassTrait;

    /**
     * The title of the column to display.
     *
     * @Important
     *
     * @var string|ValueInterface
     */
    private $title;

    /**
     * The twig code to render the column.
     * 
     * @var string
     */
    private $twig;

    /**
     * True if the column is sortable, false otherwise.
     * 
     * @var bool
     */
    private $sortable;

    /**
     * Get the key to sort upon.
     * 
     * @var string
     */
    private $sortKey;

    /**
     * The width of the column.
     * 
     * @var int|string
     */
    private $width;

    private $columnNumber;
    private static $COLUMN_NUMBER = 0;

    /**
     * This condition must be matched to display the column.
     * Otherwise, the column is not displayed.
     * The displayCondition is optional. If no condition is set, the column will always be displayed.
     *
     * @var ConditionInterface
     */
    private $displayCondition;

    private $twigEnvironment;

    /**
     * @Important $title
     * @Important $twig
     * @Important $sortKey
     * @Important $sortable
     * @Important $width
     * @Important $displayCondition
     *
     * @param string|ValueInterface $title            The title of the column to display
     * @param string                $twig             The twig code to render the column.
     * @param string|null           $sortKey
     * @param bool                  $sortable         True if the column is sortable, false otherwise.
     * @param int|string            $width            Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc... This is optional. Leave empty to let the browser decide.
     * @param ConditionInterface    $displayCondition
     * @param Twig_Environment      $twigEnvironment
     */
    public function __construct($title, $twig, $sortKey = null, $sortable = false, $width = null, $displayCondition = null, Twig_Environment $twigEnvironment = null)
    {
        $this->title = $title;
        $this->twig = $twig;
        $this->sortKey = $sortKey;
        $this->sortable = $sortable;
        $this->width = $width;
        $this->displayCondition = $displayCondition;

        ++self::$COLUMN_NUMBER;
        $this->columnNumber = self::$COLUMN_NUMBER;

        if (null === $twigEnvironment) {
            $loader = new \Twig_Loader_String();
            $this->twigEnvironment = new \Twig_Environment($loader);
        } else {
            $this->twigEnvironment = $twigEnvironment;
        }
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnInterface::getTitle()
     */
    public function getTitle() : string
    {
        return ValueUtils::val($this->title);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnKeyInterface::getKey()
     */
    public function getKey()
    {
        return 'twig_'.$this->columnNumber;
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnKeyInterface::getSortKey()
     */
    public function getSortKey()
    {
        return $this->sortKey;
    }

    /**
     * Returns true if the column is sortable, and false otherwise.
     */
    public function isSortable() : bool
    {
        return $this->sortable;
    }

    /**
     * Returns true if the column escapes HTML, and false otherwise.
     */
    public function isEscapeHTML() : bool
    {
        return false;
    }

    /**
     * Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc...
     * This is optionnal. Leave empty to let the browser decide.
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * If this function returns true, the column should not be displayed.
     *
     * @return bool
     */
    public function isHidden()
    {
        if ($this->displayCondition == null) {
            return false;
        }

        return !$this->displayCondition->isOk();
    }

    private static $lastRowSerialized;
    private static $lastArray;

    /**
     * Returns a (HTML) representation of the row.
     *
     * @return string
     */
    public function render($row)
    {
        if (is_object($row)) {
            // Object to array serialisation is costly. Let's call it only one for each column by saving the last value dealt with.
            if (self::$lastRowSerialized === $row) {
                $row = self::$lastArray;
            } else {
                // If this is an object, let's build an array out if its getters and setters and issers.
                $row = (new ObjectToArrayCaster(get_class($row)))->cast($row);
                self::$lastRowSerialized = $row;
                self::$lastArray = $row;
            }
        }

        return $this->twigEnvironment->render($this->twig, $row);
    }

    /**
     * Returns true if the column should be exported in CSV.
     * Note: if not set, the column is not exported.
     *
     * @return bool
     */
    public function isExported() : bool
    {
        if ($this->export === null) {
            return false;
        }

        return $this->export;
    }
}

<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Common\Formatters\FormatterInterface;
use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use \Twig_Environment;

/**
 * A column of an EvoluGrid that renders a key of the resultset.
 * 
 * @author David Negrier
 */
class TwigColumn implements EvoluColumnKeyInterface, EvoluColumnRowFormatterInterface {
	/**
	 * The title of the column to display
	 * 
	 * @var string
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
	 * @var string
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
	 * @param string $title The title of the column to display
	 * @param string $twig The twig code to render the column.
	 * @param string $key Get the key to map to in the datagrid. Only used for sort order.
	 * @param bool $sortable True if the column is sortable, false otherwise.
	 * @param int $width Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc... This is optionnal. Leave empty to let the browser decide.
	 * @param ConditionInterface $displayCondition
	 */
	public function __construct($title, $twig, $sortKey = null, $sortable = false, $width = null, $displayCondition = null) {
		$this->title = $title;
		$this->twig = $twig;
		$this->sortKey = $sortKey;
		$this->sortable = $sortable;
		$this->width = $width;
		$this->displayCondition = $displayCondition;
		
		self::$COLUMN_NUMBER++;
		$this->columnNumber = self::$COLUMN_NUMBER;
		
		$loader = new \Twig_Loader_String();
		$this->twigEnvironment = new \Twig_Environment($loader);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnInterface::getTitle()
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnKeyInterface::getKey()
	 */
	public function getKey() {
		return "twig_".$this->columnNumber;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnKeyInterface::getSortKey()
	 */
	public function getSortKey() {
		return $this->sortKey;
	}

	/**
	 * Returns true if the column is sortable, and false otherwise.
	 */
	public function isSortable() {
		return $this->sortable;
	}

	/**
	 * Returns true if the column escapes HTML, and false otherwise.
	 */
	public function isEscapeHTML() {
		return false;
	}
	
	/**
	 * Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc...
	 * This is optionnal. Leave empty to let the browser decide.
	 *
	 * @return string
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * If this function returns true, the column should not be displayed.
	 *
	 * @return bool
	 */
	public function isHidden() {
		if ($this->displayCondition == null) {
			return false;
		}
		return !$this->displayCondition->isOk();
	}
	
	/**
	 * Format the row passed in parameter
	 * 
	 * @param array $row
	 * @return array
	 */
	public function formatRow($row) {
		$cell = $this->twigEnvironment->render($this->twig, $row);
		$row["twig_".$this->columnNumber] = $cell;
		return $row;
	}
}

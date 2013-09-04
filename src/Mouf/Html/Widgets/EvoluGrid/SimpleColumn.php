<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Common\Formatters\FormatterInterface;
use Mouf\Utils\Common\ConditionInterface\ConditionInterface;


/**
 * A column of an EvoluGrid that renders a key of the resultset.
 * 
 * @author David Negrier
 */
class SimpleColumn implements EvoluColumnKeyInterface, EvoluColumnFormatterInterface {
	/**
	 * The title of the column to display
	 * 
	 * @Important
	 * @var string
	 */
	private $title;

	/**
	 * Get the key to map to in the datagrid.
	 * 
	 * @Important
	 * @var string
	 */
	private $key;

	/**
	 * True if the column is sortable, false otherwise.
	 * 
	 * @var bool
	 */
	private $sortable;

	/**
	 * True if you should escape HTML tag, false otherwise.
	 * 
	 * @var bool
	 */
	private $escapeHTML;
	
	/**
	 * The width of the column.
	 * 
	 * @var string
	 */
	private $width;
	
	/**
	 * This condition must be matched to display the column.
	 * Otherwise, the column is not displayed.
	 * The displayCondition is optional. If no condition is set, the column will always be displayed.
	 *
	 * @var ConditionInterface
	 */
	private $displayCondition;
	
	/**
	 * Formatter used to format the column output.
	 * The Formater is optional. If no formater is set, the value will be output directly without being formated.
	 * 
	 * @var FormatterInterface
	 */
	private $formatter;
	
	/**
	 * @Important $title
	 * @Important $key
	 * @Important $sortable
	 * @Important $escapeHTML
	 * @Important $width
	 * @Important $displayCondition
	 * @Important $formatter
	 * @param string $title The title of the column to display
	 * @param string $key Get the key to map to in the datagrid.
	 * @param bool $sortable True if the column is sortable, false otherwise.
	 * @param int $width Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc... This is optionnal. Leave empty to let the browser decide.
	 * @param ConditionInterface $displayCondition
	 * @param FormatterInterface $formatter Formatter used to format the column output (optional).
	 * @param bool $escapeHTML True if you should escape HTML tag, false otherwise.
	 */
	public function __construct($title, $key, $sortable = false, $width = null, $displayCondition = null, $formatter = null, $escapeHTML = true) {
		$this->title = $title;
		$this->key = $key;
		$this->sortable = $sortable;
		$this->escapeHTML = $escapeHTML;
		$this->width = $width;
		$this->displayCondition = $displayCondition;
		$this->formatter = $formatter;
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
		return $this->key;
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
		return $this->escapeHTML;
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
	 * This function return the formatter used if any
	 * 
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnFormatterInterface::getFormatter()
	 */
	public function getFormatter() {
		if (isset($this->formatter)) {
			return $this->formatter;
		} else {
			return null;
		}
	}
	
}

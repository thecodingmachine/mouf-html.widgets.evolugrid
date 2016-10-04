<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Html\Widgets\EvoluGrid\Utils\ObjectToArrayAdapter;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Common\Formatters\FormatterInterface;
use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;


/**
 * A column of an EvoluGrid that renders a key of the resultset.
 * 
 * @author David Negrier
 */
class SimpleColumn extends EvoluGridColumn implements EvoluColumnInterface {

    use CssClassTrait;

	/**
	 * The title of the column to display
	 * 
	 * @Important
	 * @var string|ValueInterface
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
     * The sort key (if it is different from the $key)
     *
     * @var string
     */
    private $sortKey;

    /**
     * @var PropertyAccessor
     */
    private $proprertyAccessor;

	/**
	 * @Important $title
	 * @Important $key
	 * @Important $sortable
	 * @Important $escapeHTML
	 * @Important $width
	 * @Important $displayCondition
	 * @Important $formatter
	 * @param string|ValueInterface $title The title of the column to display
	 * @param string $key Get the key to map to in the result set (or an expression in the symfony/expression-language).
	 * @param bool $sortable True if the column is sortable, false otherwise.
	 * @param int $width Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc... This is optional. Leave empty to let the browser decide.
	 * @param ConditionInterface $displayCondition
	 * @param FormatterInterface $formatter Formatter used to format the column output (optional).
	 * @param bool $escapeHTML True if you should escape HTML tag, false otherwise.
     * @param string $sortKey The sort key (if it is different from the $key)
     * @param ExpressionLanguage|null $expressionLanguage
     */
	public function __construct($title, $key, $sortable = false, $width = null, $displayCondition = null, $formatter = null, $escapeHTML = true, string $sortKey = null, ExpressionLanguage $expressionLanguage = null) {
		$this->title = $title;
		$this->key = $key;
		$this->sortable = $sortable;
		$this->escapeHTML = $escapeHTML;
		$this->width = $width;
		$this->displayCondition = $displayCondition;
		$this->formatter = $formatter;
        $this->sortKey = $sortKey;
        $this->expressionLanguage = $expressionLanguage ?: new ExpressionLanguage();
	}

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Html\Widgets\EvoluGrid\EvoluColumnInterface::getTitle()
	 */
	public function getTitle() {
		return ValueUtils::val($this->title);
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
	 * Returns the key to sort upon in the datagrid.
	 *
	 * @return string
	 */
	public function getSortKey() {
		return $this->key;
	}

	/**
	 * Returns true if the column escapes HTML, and false otherwise.
	 */
	public function isEscapeHTML() : bool {
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
     * Returns a (HTML) representation of the row.
     * @return string
     */
    public function render($row)
    {
        if (is_object($row)) {
            $row = new ObjectToArrayAdapter($row);
        }
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess
        // TODO: if $row is an object, we must wrap it in an object that allows accessing public properties or getters.... class ObjectAsArray implements ArrayAccess

        $value = $this->expressionLanguage->evaluate($this->key, $row);
        if ($this->formatter !== null) {
            return $this->formatter->format($value);
        } else {
            return $value;
        }
    }
}

<?php
namespace Mouf\Html\Widgets\EvoluGrid;

use Mouf\Utils\Common\ConditionInterface\ConditionInterface;

/**
 * A column of an EvoluGrid that renders HTML
 * 
 * @author Pierre Vaidie
 */
class HtmlColumn implements EvoluColumnJsInterface {
	/**
	 * The title of the column to display
	 * 
	 * @Important
	 * @var string
	 */
	private $title;
	
	/**
	 * The key to sort upon (or null if the column is not sortable)
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
	
	/**
	 * The Html to output
	 */
	private $html;
	
	/**
	 * This condition must be matched to display the column.
	 * Otherwise, the column is not displayed.
	 * The displayCondition is optional. If no condition is set, the column will always be displayed.
	 *
	 * @var ConditionInterface
	 */
	private $displayCondition;
	
	/**
	 * @Important $title
	 * @Important $sortKey
	 * @Important $width
	 * @Important $html
	 * @Important $displayCondition
	 * @param string $title The title of the column to display
	 * @param string $sortKey The key to sort upon (or null if the column is not sortable)
	 * @param int $width Returns the width of the column. Just like the CSS width property, you can express it in %, px, em, etc... This is optionnal. Leave empty to let the browser decide.
	 * @param string $html The Html to ouput. You can use placeholders to use row values. For example, <code>&lt;a href='show?id={id}' class="btn"&gt;{name}&lt;/a&gt;</code>
	 * @param ConditionInterface $displayCondition
	 */
	public function __construct($title, $html,  $sortKey = null, $width = null, $displayCondition = null) {
		$this->title = $title;
		$this->width = $width;
		$this->html = $html;
		$this->sortKey = $sortKey;
		$this->displayCondition = $displayCondition;
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
		return $this->sortKey;
	}

	/**
	 * Returns true if the column is sortable, and false otherwise.
	 */
	public function isSortable() {
		return $this->sortKey != null;
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
	 * Returns the JS function to be used to render the html.
	 *
	 * @return string
	 */
	public function getJsRenderer() {	
		$html = $this->html;	
		preg_match_all('/{\w*}/', $html, $res);		
		if (!empty($res)) {
			$matches = $res[0];
			$replaceStr = "";
			foreach ($matches as $key => $value) {
				$replaceStr .= '.replace("'.$value.'", row.'.str_replace(array('{','}'), array('',''), $value).')';
			}
		} 		
		return 'function(row) { return '.json_encode($html).$replaceStr.' }';	
		
	}
	
}

<?php
namespace Mouf\Html\Widgets\EvoluGrid;
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 28/05/14
 * Time: 13:45
 */
Trait CssClassTrait {

    private $cssClass;

     /**
      * @return string the CSS Class for all cells of the column
      */
    public function getClass(){
        return $this->cssClass;
    }

    /**
     * Sets the CSS class for all cells in the column
     * @param string $class
     */
    public function setClass($class){
        $this->cssClass = $class;
    }

}
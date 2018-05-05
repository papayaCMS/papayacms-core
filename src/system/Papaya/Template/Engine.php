<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
* Abstract Superclas for template engines, implements \paramter handling and a loader concept for
* Variables.
*
* The target of this class is to provide an identical interface for all kind of templates.
*
* The parameters are a key=>values list. The key will be converted to an uppercase string ([A-Z_]),
* only skalar values are allowed for the parameters.
*
* The values are converted into a DOMDocument. The context is used for restriction in the real
* implementation.
*
* @property PapayaObjectOptionsList $parameters
* @property PapayaObjectList $loaders
* @property DOMDocument $values
*
* @package Papaya-Library
* @subpackage Template
*/
abstract class PapayaTemplateEngine {

  /**
  * Parameter handling object
  *
  * @var PapayaObjectOptionsList
  */
  private $_parameters = NULL;

  /**
  * Loaders list
  * @var PapayaObjectList
  */
  private $_loaders = NULL;

  /**
  * Values tree
  * @var DOMDocument
  */
  private $_values = NULL;

  /**
  * Values tree limitation context
  * @var DOMElement
  */
  private $_context = NULL;

  /**
  * Prepare template engine if needed
  */
  abstract function prepare();

  /**
  * Execute/run template engine
  */
  abstract function run();

  /**
  * Get result of last run
  */
  abstract function getResult();


  abstract function setTemplateString($string);


  abstract function setTemplateFile($fileName);

  /**
   * Combined getter/setter for paramters
   *
   * @param \PapayaObjectOptionsList|array $parameters
   * @throws \InvalidArgumentException
   * @return \PapayaObjectOptionsList
   */
  public function parameters($parameters = NULL) {
    if (isset($parameters)) {
      if ($parameters instanceof \PapayaObjectOptionsList) {
        $this->_parameters = $parameters;
      } elseif (is_array($parameters)) {
        $this->_parameters = new \PapayaObjectOptionsList($parameters);
      } else {
        throw new \InvalidArgumentException(
          'Argument must be an array or a PapayaObjectOptionsList object.'
        );
      }
    } elseif (!isset($this->_parameters)) {
      $this->_parameters = new \PapayaObjectOptionsList();
    }
    return $this->_parameters;
  }

  /**
   * Combined getter/setter for loaders
   *
   * @param \PapayaObjectList $loaders
   * @throws \InvalidArgumentException
   * @return \PapayaObjectList
   */
  public function loaders(\PapayaObjectList $loaders = NULL) {
    if (isset($loaders)) {
      if ($loaders->getItemClass() === \PapayaTemplateEngineValuesLoadable::class) {
        $this->_loaders = $loaders;
      } else {
        throw new \InvalidArgumentException(
          sprintf(
            '%1$s with %2$s expected: "%3$s" given.',
            \PapayaObjectList::class,
            \PapayaTemplateEngineValuesLoadable::class,
            $loaders->getItemClass()
          )
        );
      }
    } elseif (!isset($this->_loaders)) {
      $this->_loaders = new \PapayaObjectList(\PapayaTemplateEngineValuesLoadable::class);
    }
    return $this->_loaders;
  }

  /**
   * Combined getter/setter for values, DOMElement and DOMDDocument are used directly, all other
   * values are converted using the loaders
   *
   * @param mixed $values
   * @throws \UnexpectedValueException
   * @return \PapayaXmlDocument
   */
  public function values($values = NULL) {
    if (isset($values)) {
      $this->_context = NULL;
      if (!($values instanceof \DOMElement || $values instanceof \DOMDocument)) {
        $loadedValues = NULL;
        /** @var PapayaTemplateEngineValuesLoadable $loader */
        foreach ($this->loaders() as $loader) {
          $loadedValues = $loader->load($values);
          if (FALSE !== $loadedValues) {
            break;
          }
        }
      } else {
        $loadedValues = $values;
      }
      if ($loadedValues instanceof \PapayaXmlDocument) {
        $this->_values = $loadedValues;
      } elseif ($loadedValues instanceof \PapayaXmlElement) {
        $this->_values = $loadedValues->ownerDocument;
        $this->_context = $loadedValues;
      } elseif ($loadedValues instanceof \DOMDocument && isset($loadedValues->documentElement)) {
        $this->_values = new \PapayaXmlDocument();
        $this->_values->appendChild(
          $this->_values->importNode($loadedValues->documentElement, TRUE)
        );
      } elseif ($loadedValues instanceof \DOMElement) {
        $this->_values = new \PapayaXmlDocument();
        $this->_values->appendChild(
          $this->_values->importNode($loadedValues, TRUE)
        );
        $this->_context = $this->_values->documentElement;
      } else {
        throw new \UnexpectedValueException(
          sprintf(
            '"%s" could not be converted into a PapayaXmlDocument.',
            is_object($values) ? get_class($values) : gettype($values)
          )
        );
      }
    }
    if (NULL === $this->_values) {
      $this->_values = new \PapayaXmlDocument();
      $this->_context = NULL;
    }
    return $this->_values;
  }

  public function getContext() {
    return $this->_context;
  }

  /**
   * Magic Method, provides virtual properties
   *
   * @param string $name
   * @return \PapayaObjectList|\PapayaObjectOptionsList|\PapayaXmlDocument
   */
  public function __get($name) {
    switch ($name) {
    case 'loaders' :
      return $this->loaders();
    case 'parameters' :
      return $this->parameters();
    case 'values' :
      return $this->values();
    }
    return $this->$name;
  }

  /**
  * Magic Method, provides virtual properties
  *
  * @param string $name
  * @param mixed $value
  */
  public function __set($name, $value) {
    switch ($name) {
    case 'loaders' :
      $this->loaders($value);
      break;
    case 'parameters' :
      $this->parameters($value);
      break;
    case 'values' :
      $this->values($value);
      break;
    }
    $this->$name = $value;
  }
}

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
namespace Papaya\Template;

use Papaya\BaseObject;
use Papaya\XML;

/**
 * Abstract superclass for template engines, implements parameter handling and a loader concept for
 * Variables.
 *
 * The target of this class is to provide an identical interface for all kind of templates.
 *
 * The parameters are a key=>values list. The key will be converted to an uppercase string ([A-Z_]),
 * only scalar values are allowed for the parameters.
 *
 * The values are converted into a DOMDocument. The context is used for restriction in the real
 * implementation.
 *
 * @property BaseObject\Options\Collection $parameters
 * @property BaseObject\Collection $loaders
 * @property \DOMDocument $values
 *
 * @package Papaya-Library
 * @subpackage Template
 */
abstract class Engine implements BaseObject\Interfaces\Properties {
  /**
   * Parameter handling object
   *
   * @var BaseObject\Options\Collection
   */
  private $_parameters;

  /**
   * Loaders list
   *
   * @var BaseObject\Collection
   */
  private $_loaders;

  /**
   * Values tree
   *
   * @var \DOMDocument
   */
  private $_values;

  /**
   * Values tree limitation context
   *
   * @var \DOMElement
   */
  private $_context;

  /**
   * Prepare template engine if needed
   */
  abstract public function prepare();

  /**
   * Execute/run template engine
   */
  abstract public function run();

  /**
   * Get result of last run
   */
  abstract public function getResult();

  /**
   * @param string $string
   * @return mixed
   */
  abstract public function setTemplateString($string);

  /**
   * @param string $fileName
   * @return mixed
   */
  abstract public function setTemplateFile($fileName);

  /**
   * Combined getter/setter for parameters
   *
   * @param BaseObject\Options\Collection|array $parameters
   *
   * @throws \InvalidArgumentException
   *
   * @return BaseObject\Options\Collection
   */
  public function parameters($parameters = NULL) {
    if (NULL !== $parameters) {
      if ($parameters instanceof BaseObject\Options\Collection) {
        $this->_parameters = $parameters;
      } elseif (\is_array($parameters)) {
        $this->_parameters = new BaseObject\Options\Collection($parameters);
      } else {
        throw new \InvalidArgumentException(
          \sprintf(
            'Argument must be an array or a %s object.',
            BaseObject\Options\Collection::class
          )
        );
      }
    } elseif (NULL === $this->_parameters) {
      $this->_parameters = new BaseObject\Options\Collection();
    }
    return $this->_parameters;
  }

  /**
   * Combined getter/setter for loaders
   *
   * @param BaseObject\Collection $loaders
   *
   * @throws \InvalidArgumentException
   *
   * @return BaseObject\Collection
   */
  public function loaders(BaseObject\Collection $loaders = NULL) {
    if (NULL !== $loaders) {
      if (Engine\Values\Loadable::class === $loaders->getItemClass()) {
        $this->_loaders = $loaders;
      } else {
        throw new \InvalidArgumentException(
          \sprintf(
            '%1$s with %2$s expected: "%3$s" given.',
            BaseObject\Collection::class,
            Engine\Values\Loadable::class,
            $loaders->getItemClass()
          )
        );
      }
    } elseif (NULL === $this->_loaders) {
      $this->_loaders = new BaseObject\Collection(Engine\Values\Loadable::class);
    }
    return $this->_loaders;
  }

  /**
   * Combined getter/setter for values, DOMElement and DOMDDocument are used directly, all other
   * values are converted using the loaders
   *
   * @param mixed $values
   *
   * @throws \UnexpectedValueException
   *
   * @return XML\Document
   */
  public function values($values = NULL) {
    if (NULL !== $values) {
      $this->_context = NULL;
      if (!($values instanceof \DOMElement || $values instanceof \DOMDocument)) {
        $loadedValues = NULL;
        /** @var Engine\Values\Loadable $loader */
        foreach ($this->loaders() as $loader) {
          $loadedValues = $loader->load($values);
          if (FALSE !== $loadedValues) {
            break;
          }
        }
      } else {
        $loadedValues = $values;
      }
      /** @var \DOMDocument|\DOMElement $loadedValues */
      if ($loadedValues instanceof XML\Document) {
        $this->_values = $loadedValues;
      } elseif ($loadedValues instanceof XML\Element) {
        $this->_values = $loadedValues->ownerDocument;
        $this->_context = $loadedValues;
      } elseif ($loadedValues instanceof \DOMDocument && NULL !== $loadedValues->documentElement) {
        $this->_values = new XML\Document();
        $this->_values->appendChild(
          $this->_values->importNode($loadedValues->documentElement, TRUE)
        );
      } elseif ($loadedValues instanceof \DOMElement) {
        $this->_values = new XML\Document();
        $this->_values->appendChild(
          $this->_values->importNode($loadedValues, TRUE)
        );
        $this->_context = $this->_values->documentElement;
      } else {
        throw new \UnexpectedValueException(
          \sprintf(
            '"%s" could not be converted into a Papaya\XML\Document.',
            \is_object($values) ? \get_class($values) : \gettype($values)
          )
        );
      }
    }
    if (NULL === $this->_values) {
      $this->_values = new XML\Document();
      $this->_context = NULL;
    }
    return $this->_values;
  }

  public function getContext() {
    return $this->_context;
  }

  /**
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    switch ($name) {
      case 'loaders' :
      case 'parameters' :
      case 'values' :
        return TRUE;
    }
    return FALSE;
  }

  /**
   * Magic Method, provides virtual properties
   *
   * @param string $name
   *
   * @return BaseObject\Collection|BaseObject\Options\Collection|XML\Document
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

  /**
   * @param $name
   */
  public function __unset($name) {
    throw new \LogicException(
      \sprintf('Can not unset property %s::$%s.', static::class, $name)
    );
  }
}

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
namespace Papaya\UI\Text\Translated;

/**
 * A list of string (objects) that will be translated if cast to string.
 *
 * It takes an array or an traversable, cast each element (if read) to string and returns a
 * Papaya\UI\Text\Translated for it.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Collection
  extends \IteratorIterator
  implements \Papaya\Application\Access {
  /**
   * @var \Papaya\Phrases
   */
  private $_phrases;

  /**
   * @var string
   */
  private $_phrasesGroupName;

  /**
   * Application object
   *
   * @var string
   */
  protected $_applicationObject;

  /**
   * Create object and store traversable as iterator
   *
   * @param array|\Traversable $traversable
   * @param \Papaya\Phrases $phrases
   * @param null $groupName
   */
  public function __construct($traversable, \Papaya\Phrases $phrases = NULL, $groupName = NULL) {
    parent::__construct(new \Papaya\Iterator\TraversableIterator($traversable));
    $this->_phrases = $phrases;
    $this->_phrasesGroupName = $groupName;
  }

  /**
   * Wrap the current element into an translated string and return it.
   *
   * @see \IteratorIterator::current()
   *
   * @return string
   */
  public function current() {
    $current = new \Papaya\UI\Text\Translated(
      (string)parent::current(),
      [],
      $this->_phrases,
      $this->_phrasesGroupName
    );
    $current->papaya($this->papaya());
    return $current;
  }

  /**
   * An combined getter/setter for the Papaya Application object
   *
   * @param \Papaya\Application $application
   *
   * @return \Papaya\Application\CMS|\Papaya\Application
   */
  public function papaya(\Papaya\Application $application = NULL) {
    if (NULL !== $application) {
      $this->_applicationObject = $application;
    } elseif (NULL === $this->_applicationObject) {
      $this->_applicationObject = \Papaya\Application::getInstance();
    }
    return $this->_applicationObject;
  }
}

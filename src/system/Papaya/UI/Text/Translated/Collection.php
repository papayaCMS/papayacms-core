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

use Papaya\Application;
use Papaya\Iterator;
use Papaya\Phrases;
use Papaya\UI\Text\Translated as TranslatedText;

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
  implements Application\Access {
  /**
   * @var Phrases
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
   * @param Phrases $phrases
   * @param null $groupName
   */
  public function __construct($traversable, Phrases $phrases = NULL, $groupName = NULL) {
    parent::__construct(new Iterator\TraversableIterator($traversable));
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
    $value = (string)parent::current();
    if ($value !== '') {
      $current = new TranslatedText(
        $value,
        [],
        $this->_phrases,
        $this->_phrasesGroupName
      );
      $current->papaya($this->papaya());
      return $current;
    }
    return $value;
  }

  /**
   * An combined getter/setter for the Papaya Application object
   *
   * @param Application $application
   *
   * @return \Papaya\Application\CMS|Application
   */
  public function papaya(Application $application = NULL) {
    if (NULL !== $application) {
      $this->_applicationObject = $application;
    } elseif (NULL === $this->_applicationObject) {
      $this->_applicationObject = Application::getInstance();
    }
    return $this->_applicationObject;
  }
}

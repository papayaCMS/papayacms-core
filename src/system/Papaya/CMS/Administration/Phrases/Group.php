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
namespace Papaya\CMS\Administration\Phrases;

use Papaya\CMS\Administration\Phrases;
use Papaya\UI;
use Papaya\Utility;

/**
 * Grouped access to phrases. This is a factory for phrase objects. The methods create
 * objects with access to the translations engine. If needed the objects fetch the
 * translation.
 *
 * @package Papaya-Library
 * @subpackage Phrases
 */
class Group {
  /**
   * @var \Papaya\CMS\Administration\Phrases
   */
  private $_phrases;

  /**
   * @var string
   */
  private $_name;

  /**
   * Group constructor.
   *
   * @param Phrases $phrases
   * @param string $name
   */
  public function __construct(Phrases $phrases, $name) {
    Utility\Constraints::assertNotEmpty($name);
    $this->_phrases = $phrases;
    $this->_name = $name;
  }

  /**
   * A string object
   *
   * @param string $phrase
   * @param array $arguments
   *
   * @return UI\Text\Translated
   */
  public function get($phrase, array $arguments = []) {
    $result = new UI\Text\Translated(
      $phrase, $arguments, $this->_phrases, $this->_name
    );
    return $result;
  }

  /**
   * A string list object
   *
   * @param array|\Traversable $phrases
   *
   * @return UI\Text\Translated\Collection
   */
  public function getList($phrases) {
    Utility\Constraints::assertArrayOrTraversable($phrases);
    $result = new UI\Text\Translated\Collection(
      $phrases, $this->_phrases, $this->_name
    );
    return $result;
  }
}

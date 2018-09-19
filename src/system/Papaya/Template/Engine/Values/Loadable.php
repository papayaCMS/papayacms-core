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
namespace Papaya\Template\Engine\Values;

/**
 * Interface for classes that allow to convert a variable to a DOMElement
 * values tree usable by the template engines.
 *
 * @property \Papaya\BaseObject\Options\Collection $parameters
 * @property \Papaya\BaseObject\Collection $loaders
 * @property \DOMDocument $values
 *
 * @package Papaya-Library
 * @subpackage Template
 */
interface Loadable {
  /**
   * @param mixed $values
   *
   * @return false|\Papaya\XML\Element|\Papaya\XML\Document
   */
  public function load($values);
}

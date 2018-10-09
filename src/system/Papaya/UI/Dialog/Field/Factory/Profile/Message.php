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
namespace Papaya\UI\Dialog\Field\Factory\Profile;

use Papaya\UI;

/**
 * Field factory profile for an information, confirmation, warning or error
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Message extends UI\Dialog\Field\Factory\Profile {
  /**
   * @see \Papaya\UI\Dialog\Field\Factory\Profile::getField()
   *
   * @return UI\Dialog\Field\Message
   *
   * @throws UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  public function getField() {
    $field = new UI\Dialog\Field\Message(
      (int)$this->options()->parameters,
      $this->options()->default
    );
    return $field;
  }
}

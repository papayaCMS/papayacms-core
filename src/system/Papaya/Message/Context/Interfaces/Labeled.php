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
namespace Papaya\Message\Context\Interfaces;

/**
 * Interface for an addition label for contexts.
 *
 * Message contexts which implemeted this interface will get a title/label,
 * depending on the dispatcher.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
interface Labeled
  extends \Papaya\Message\Context\Data {
  /**
   * Get label for the context
   *
   * @return string
   */
  public function getLabel();
}

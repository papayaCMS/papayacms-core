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
namespace Papaya\Spam;

/**
 * Spam filter classes calculate a spam rating for a given token list. The rating is
 * between 0 (ham) and 1 (spam).
 *
 * @package Papaya-Library
 * @subpackage Spam
 */
interface Filter {
  /**
   * Classify the token list as spam or ham. The return value will be a value
   * between 0 (ham) and 1 (spam).
   *
   * @param string $text
   * @param array|string $tokens an array containing tokens and count
   * @param int $languageId
   *
   * @return float
   */
  public function classify($text, array $tokens, $languageId);

  /**
   * Return the details for the last call off classify.
   *
   * @return array()
   */
  public function getDetails();
}

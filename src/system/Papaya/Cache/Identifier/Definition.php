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

namespace Papaya\Cache\Identifier;

/**
 * An interface to get an cache condition status. This status is used to decide if an element
 * is cacheable and with which condition data.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
interface Definition {
  /**
   * The data of the condition is contained in the url
   *
   * @var int
   */
  const SOURCE_URL = 1;

  /**
   * The data of the condition is from the generic request data
   *
   * @var int
   */
  const SOURCE_REQUEST = 2;

  /**
   * The data of the condition is stored in the session
   *
   * @var int
   */
  const SOURCE_SESSION = 4;

  /**
   * The condition loads data from the database like the maximum of a last_modified column
   *
   * @var int
   */
  const SOURCE_DATABASE = 8;

  /**
   * The condition needs data from the initializing object.
   *
   * @var int
   */
  const SOURCE_VARIABLES = 16;

  /**
   * This function can return three kinds of values:
   *
   * FALSE - not cacheable
   * TRUE - cacheable but, this definition is irrelevant
   * array - cache condition data (to distinguish cached data)
   *
   * @return bool|array
   */
  public function getStatus();

  /**
   * Returns and bitmask of source constants. This defines which data is needed to generate
   * and validate the cache identifer. Indirectly this defines which kind of cache can be used.
   *
   * If the data source is only URL the browser cache is usable. In all other cases a roundtrip to
   * the server is needed and different levels of bootstrap need to be initialized.
   *
   * @return int
   */
  public function getSources();
}

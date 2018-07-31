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
* Papaya Session Options, encapsulates storage und validation of session options
*
* @property integer $fallback
* @property string $cache
*
* @package Papaya-Library
* @subpackage Session
*/
class PapayaSessionOptions
   extends \Papaya\BaseObject\Options\Defined {

  /**
  * Fallback mode: only use cookie, no fallback
  * @var integer
  */
  const FALLBACK_NONE = 0;

  /**
  * Fallback mode: use parameters (query stirng or request body) if no cookie is available.
  * @var integer
  */
  const FALLBACK_PARAMETER = 1;

  /**
  * Fallback mode: use path rewrite (put the sid like a path directly behind the host).
  * @var integer
  */
  const FALLBACK_REWRITE = 2;

  /**
  * Cache mode: no cache, no caching at all
  * @var integer
  */
  const CACHE_NONE = 'nocache';

  /**
  * Cache mode: private, caching only in the browser (for a single user)
  * @var integer
  */
  const CACHE_PRIVATE = 'private';

  /**
  * Option definitions: The key is the option name, the element a list of possible values.
  *
  * FALLBACK: session id fallback mode (if cookie can not be used)
  * CACHE: output caching on http clients (proxy, browser)
  *
  * @var array
  */
  protected $_definitions = array(
    'FALLBACK' => array(self::FALLBACK_NONE, self::FALLBACK_PARAMETER, self::FALLBACK_REWRITE),
    'CACHE' => array(self::CACHE_NONE, self::CACHE_PRIVATE)
  );

  /**
  * Dialog option values
  * @var array
  */
  protected $_options = array(
    'FALLBACK' => self::FALLBACK_REWRITE,
    'CACHE' => self::CACHE_PRIVATE
  );
}

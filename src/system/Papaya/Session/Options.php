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

namespace Papaya\Session;

use Papaya\BaseObject\Options\Defined as DefinedOptions;

/**
 * Papaya Session Options, encapsulates storage und validation of session options
 *
 * @property int $fallback
 * @property string $cache
 *
 * @package Papaya-Library
 * @subpackage Session
 */
class Options
  extends DefinedOptions {

  public const SECURE_SESSION = 'SECURE_SESSION';
  public const SECURE_EDITOR_SESSION = 'SECURE_EDITOR_SESSION';
  public const ID_FALLBACK = 'FALLBACK';
  public const CACHE = 'CACHE';
  public const NAME = 'NAME';
  public const PATH = 'PATH';
  public const DOMAIN = 'DOMAIN';
  public const HTTP_ONLY = 'HTTP_ONLY';

  /**
   * Fallback mode: only use cookie, no fallback
   *
   * @var int
   */
  public const FALLBACK_NONE = 0;

  /**
   * Fallback mode: use parameters (query stirng or request body) if no cookie is available.
   *
   * @var int
   */
  public const FALLBACK_PARAMETER = 1;

  /**
   * Fallback mode: use path rewrite (put the sid like a path directly behind the host).
   *
   * @var int
   */
  public const FALLBACK_REWRITE = 2;

  /**
   * Cache mode: no cache, no caching at all
   *
   * @var int
   */
  public const CACHE_NONE = 'nocache';

  /**
   * Cache mode: private, caching only in the browser (for a single user)
   *
   * @var int
   */
  public const CACHE_PRIVATE = 'private';

  /**
   * Option definitions: The key is the option name, the element a list of possible values.
   * The definition can be a scalar or an array of values.
   *
   * FALLBACK: session id fallback mode (if cookie can not be used)
   * CACHE: output caching on http clients (proxy, browser)
   *
   * @var array
   */
  private static $_definitions = [
    self::NAME => '',
    self::PATH => '',
    self::DOMAIN => '',
    self::HTTP_ONLY => [TRUE, FALSE],
    self::SECURE_SESSION => [TRUE, FALSE],
    self::SECURE_EDITOR_SESSION => [TRUE, FALSE],
    self::ID_FALLBACK => [self::FALLBACK_NONE, self::FALLBACK_PARAMETER, self::FALLBACK_REWRITE],
    self::CACHE => [self::CACHE_NONE, self::CACHE_PRIVATE],
  ];

  /**
   * Dialog option values
   *
   * @var array
   */
  protected $_options = [
    self::HTTP_ONLY => FALSE,
    self::SECURE_SESSION => FALSE,
    self::SECURE_EDITOR_SESSION => FALSE,
    self::ID_FALLBACK => self::FALLBACK_REWRITE,
    self::CACHE => self::CACHE_PRIVATE,
  ];

  /**
   * Options constructor.
   *
   * @param array|null $options
   */
  public function __construct(array $options = NULL) {
    parent::__construct(self::$_definitions, $options);
  }
}

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
namespace Papaya\CMS\Content;

/**
 * Define a series of class constants for options, needed by different content objects.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
interface Options {
  /**
   * Permission inheritance, use only own permission defined for this page
   *
   * @var int
   */
  const INHERIT_PERMISSIONS_OWN = 1;

  /**
   * Permission inheritance, use only inherited permission for this page
   *
   * @var int
   */
  const INHERIT_PERMISSIONS_PARENT = 2;

  /**
   * Permission inheritance, add own permission of this page to inherited ones
   *
   * @var int
   */
  const INHERIT_PERMISSIONS_ADDITIONAL = 3;

  /**
   * Cache/Expires mode, use system option value
   *
   * @var int
   */
  const CACHE_SYSTEM = 1;

  /**
   * Cache/Expires mode, use special value defined for this page
   *
   * @var int
   */
  const CACHE_INDIVIDUAL = 2;

  /**
   * Cache/Expires mode, no caching
   *
   * @var int
   */
  const CACHE_NONE = 0;

  /**
   * URL scheme, use system option PAPAYA_DEFAULT_PROTOCOL
   *
   * @var int
   */
  const SCHEME_SYSTEM = 0;

  /**
   * URL scheme, allow only http
   *
   * @var int
   */
  const SCHEME_HTTP = 1;

  /**
   * URL scheme, allow only https
   *
   * @var int
   */
  const SCHEME_HTTPS = 2;
}

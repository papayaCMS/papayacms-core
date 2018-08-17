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

namespace Papaya\Response;
/**
 * Abstract superclass for response content
 *
 * Encapsulates the handling of the content part of a response. This could be a simple string,
 * a stream or a file.
 *
 * @package Papaya-Library
 * @subpackage Response
 */
interface Content {

  /**
   * Return the content length in bytes for the http header.
   *
   * If -1 is returned, the content length header will not be set, which means
   * transfer encoding chunked is used.
   *
   * @return integer
   */
  public function length();

  /**
   * Outputs the content to the standard output.
   *
   * @return void
   */
  public function output();
}

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
namespace Papaya\Request\Parameters;

use Papaya\Application;

/**
 * Provide access to request parameters.
 *
 * @package Papaya\Request\Parameters
 */
interface Access extends Application\Access {
  /**
   * Parameter method post (read request body parameters)
   *
   * @var int
   */
  const METHOD_POST = 0;

  /**
   * Parameter method get (read query string parameters)
   *
   * @var int
   */
  const METHOD_GET = 1;

  /**
   * Parameter method post (read query string and request body)
   *
   * @var int
   */
  const METHOD_MIXED = 2;

  /**
   * Parameter method get (read request body and query string)
   *
   * @var int
   */
  const METHOD_MIXED_GET = 3;

  /**
   * Parameter method post (read query string and request body)
   *
   * @var int
   */
  const METHOD_MIXED_POST = 2;

  /**
   * Get/Set parameter handling method. This will be used to define the parameter sources.
   *
   * @param int $method
   *
   * @return int
   */
  public function parameterMethod($method = NULL);

  /**
   * Get/Set the parameter group name.
   *
   * This puts all field parameters (except the hidden fields) into a parameter group.
   *
   * @param string|null $groupName
   *
   * @return string|null
   */
  public function parameterGroup($groupName = NULL);

  /**
   * Access request parameters
   *
   * This method gives you access to request parameters.
   *
   * @param \Papaya\Request\Parameters $parameters
   *
   * @return \Papaya\Request\Parameters
   */
  public function parameters(\Papaya\Request\Parameters $parameters = NULL);
}

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
namespace Papaya\Request\Parameters\Access;

use Papaya\Application;
use Papaya\Request;
use Papaya\Utility;

/**
 * An basic framework object including request parameters handling
 *
 * @package Papaya-Library
 * @subpackage Objects
 *
 * @method Application\CMSApplication papaya(Application $papaya = NULL)
 */
trait Integration {
  use Application\Access\Aggregation;

  /**
   * Parameter request method
   *
   * @var null|string
   */
  private $_parameterMethod = Request\Parameters\Access::METHOD_MIXED_POST;

  /**
   * Parameter group name
   *
   * @var null|string
   */
  private $_parameterGroup;

  /**
   * Request parameters object
   *
   * @var Request\Parameters
   */
  private $_parameters;

  /**
   * Get/Set parameter handling method. This will be used to define the parameter sources.
   *
   * @param int $method
   *
   * @return int
   */
  public function parameterMethod($method = NULL) {
    if (NULL !== $method) {
      Utility\Constraints::assertInteger($method);
      $this->_parameterMethod = $method;
    }
    return $this->_parameterMethod;
  }

  /**
   * Get/Set the parameter group name.
   *
   * This puts all field parameters (except the hidden fields) into a parameter group.
   *
   * @param string|null $groupName
   *
   * @return string|null
   */
  public function parameterGroup($groupName = NULL) {
    if (NULL !== $groupName) {
      Utility\Constraints::assertString($groupName);
      Utility\Constraints::assertNotEmpty($groupName);
      $this->_parameterGroup = $groupName;
    }
    return $this->_parameterGroup;
  }

  /**
   * Access request parameters
   *
   * This method gives you access to request parameters.
   *
   * @param Request\Parameters $parameters
   *
   * @return Request\Parameters
   */
  public function parameters(Request\Parameters $parameters = NULL) {
    if (NULL !== $parameters) {
      $this->_parameters = $parameters;
    } elseif (NULL === $this->_parameters) {
      $sourceMapping = [
        Request\Parameters\Access::METHOD_GET => Request::SOURCE_QUERY,
        Request\Parameters\Access::METHOD_POST => Request::SOURCE_BODY,
        Request\Parameters\Access::METHOD_MIXED_POST => Request::SOURCE_QUERY | Request::SOURCE_BODY,
        Request\Parameters\Access::METHOD_MIXED_GET => Request::SOURCE_QUERY | Request::SOURCE_BODY
      ];
      if (NULL !== $this->_parameterGroup) {
        $this->_parameters = $this->papaya()->request->getParameterGroup(
          $this->_parameterGroup, $sourceMapping[$this->_parameterMethod]
        );
      } else {
        $this->_parameters = $this->papaya()->request->getParameters(
          $sourceMapping[$this->_parameterMethod]
        );
      }
    }
    return $this->_parameters;
  }
}

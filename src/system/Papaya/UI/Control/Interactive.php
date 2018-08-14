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

namespace Papaya\UI\Control;
/**
 * Abstract superclass implementing basic features for handling request parameters in ui.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Interactive
  extends \Papaya\UI\Control
  implements \Papaya\Request\Parameters\Access {

  /**
   * Parameter request method
   *
   * @var NULL|string
   */
  protected $_parameterMethod = self::METHOD_MIXED_POST;

  /**
   * Parameter group name
   *
   * @var NULL|string
   */
  protected $_parameterGroup;

  /**
   * Request parameters object
   *
   * @var \Papaya\Request\Parameters
   */
  private $_parameters;

  /**
   * Get/Set parameter handling method. This will be used to define the parameter sources.
   *
   * @param integer $method
   * @return integer
   */
  public function parameterMethod($method = NULL) {
    if (NULL !== $method) {
      \Papaya\Utility\Constraints::assertInteger($method);
      $this->_parameterMethod = $method;
    }
    return $this->_parameterMethod;
  }

  /**
   * Get/Set the parameter group name.
   *
   * This puts all field parameters (except the hidden fields) into a parameter group.
   *
   * @param string|NULL $groupName
   * @return string|NULL
   */
  public function parameterGroup($groupName = NULL) {
    if (NULL !== $groupName) {
      \Papaya\Utility\Constraints::assertString($groupName);
      \Papaya\Utility\Constraints::assertNotEmpty($groupName);
      $this->_parameterGroup = $groupName;
    }
    return $this->_parameterGroup;
  }

  /**
   * Access request parameters
   *
   * This method gives you access to request parameters.
   *
   * @param \Papaya\Request\Parameters $parameters
   * @return \Papaya\Request\Parameters
   */
  public function parameters(\Papaya\Request\Parameters $parameters = NULL) {
    if (NULL !== $parameters) {
      $this->_parameters = $parameters;
    } elseif (NULL === $this->_parameters) {
      $sourceMapping = array(
        self::METHOD_GET => \Papaya\Request::SOURCE_QUERY,
        self::METHOD_POST => \Papaya\Request::SOURCE_BODY,
        self::METHOD_MIXED_POST => \Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY,
        self::METHOD_MIXED_GET => \Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY
      );
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

  /**
   * Check if the current request uses POST
   *
   * @return boolean
   */
  public function isPostRequest() {
    return ('post' === $this->papaya()->request->getMethod());
  }
}
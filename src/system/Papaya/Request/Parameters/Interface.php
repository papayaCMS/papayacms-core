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

interface PapayaRequestParametersInterface extends \PapayaObjectInterface {

  /**
  * Parameter method post (read request body parameters)
  * @var integer
  */
  const METHOD_POST = 0;

  /**
  * Parameter method get (read query string parameters)
  * @var integer
  */
  const METHOD_GET = 1;

  /**
  * Parameter method post (read query string and request body)
  * @var integer
  */
  const METHOD_MIXED = 2;

  /**
  * Parameter method get (read request body and query string)
  * @var integer
  */
  const METHOD_MIXED_GET = 3;

  /**
  * Parameter method post (read query string and request body)
  * @var integer
  */
  const METHOD_MIXED_POST = 2;

  /**
  * Get/Set parameter handling method. This will be used to define the parameter sources.
  *
  * @param integer $method
  * @return integer
  */
  public function parameterMethod($method = NULL);

  /**
  * Get/Set the parameter group name.
  *
  * This puts all field parameters (except the hidden fields) into a parameter group.
  *
  * @param string|NULL $groupName
  * @return string|NULL
  */
  public function parameterGroup($groupName = NULL);

  /**
  * Access request parameters
  *
  * This method gives you access to request parameters.
  *
  * @param \PapayaRequestParameters $parameters
  * @return \PapayaRequestParameters
  */
  public function parameters(\PapayaRequestParameters $parameters = NULL);

}

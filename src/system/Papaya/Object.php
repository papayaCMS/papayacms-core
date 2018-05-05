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
* Papaya Object - papaya basic object
*
* @package Papaya-Library
* @subpackage Objects
*/
abstract class PapayaObject implements \PapayaObjectInterface {

  /**
  * Application object
  * @var string
  */
  protected $_applicationObject = NULL;

  /**
  * Get application object
  *
  * @deprecated {@see \PapayaObject::papaya()}
  * @return \PapayaApplication
  */
  public function getApplication() {
    return $this->papaya();
  }

  /**
  * Set application object
  *
  * @deprecated {@see \PapayaObject::papaya()}
  * @param \PapayaApplication $application
  */
  public function setApplication($application) {
    $this->papaya($application);
  }

  /**
   * An combined getter/setter for the Papaya Application object
   *
   * @param \PapayaApplication $application
   * @return \PapayaApplicationCms
   */
  public function papaya(\PapayaApplication $application = NULL) {
    if (isset($application)) {
      $this->_applicationObject = $application;
    }
    if (is_null($this->_applicationObject)) {
      $this->_applicationObject = \PapayaApplication::getInstance();
    }
    return $this->_applicationObject;
  }
}

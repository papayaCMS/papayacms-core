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
namespace Papaya\Application\Access {

  use Papaya\Application;

  /**
   * Provides access to the papaya Application
   * instance.
   *
   * This is an implementation for {@see Papaya\Application\Access}
   *
   * @package Papaya\Application\Access
   */
  trait Aggregation {
    private $_papayaApplicationObject;

    /**
     * An combined getter/setter for the Papaya Application object
     *
     * @param Application $application
     *
     * @return Application\CMSApplication|Application
     */
    public function papaya(Application $application = NULL) {
      if (NULL !== $application) {
        $this->_papayaApplicationObject = $application;
      } elseif (NULL === $this->_papayaApplicationObject) {
        $this->_papayaApplicationObject = Application::getInstance();
      }
      return $this->_papayaApplicationObject;
    }
  }
}

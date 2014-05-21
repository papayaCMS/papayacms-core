<?php
/**
* Papaya Controller class for error pages with template file
*
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Controller
* @version $Id: File.php 34804 2010-09-02 18:01:10Z weinert $
*/

/**
* Papaya Controller class for error pages with template file
* @package Papaya-Library
* @subpackage Controller
*/
class PapayaControllerErrorFile extends PapayaControllerError {

  /**
  * Set template data from file
  * @param string $fileName
  * @return boolean
  */
  public function setTemplateFile($fileName) {
    if (!empty($fileName) &&
        file_exists($fileName) &&
        is_file($fileName) &&
        is_readable($fileName)) {
      $this->_template = file_get_contents($fileName);
      return TRUE;
    }
    return FALSE;
  }
}
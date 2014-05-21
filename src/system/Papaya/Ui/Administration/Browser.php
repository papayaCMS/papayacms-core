<?php
/**
* Papaya Interface Administration Browser
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Browser.php 39725 2014-04-07 17:19:34Z weinert $
*/

/**
* Papaya Interface Administration Browser
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiAdministrationBrowser extends PapayaObject {

  /**
  * Owner object for papaya base functions.
  * @var object
  */
  protected $owner;

  /**
  * Param group name.
  * @var string
  */
  protected $paramName = 'brw';

  /**
  * Request params.
  * @var array
  */
  protected $params = array();

  /**
  * Field (name) which stores the selected value from generated browser list.
  * @var string
  */
  protected $fieldName = 'browser';

  /**
  * Needed hidden fields.
  * @var array
  */
  public $hiddenFields = array();

  /**
  * Stored data.
  * @var array
  */
  public $data = array();

  /**
  * Possible list modes for browser.
  * @var array
  */
  protected $listModes = array('list', 'tile', 'thumbs');

  /**
  * Constructor
  * @param object $owner
  * @param array $params
  * @param string $paramName
  * @param array $data default empty array
  * @param string $fieldName default NULL
  * @param array $hidden hidden fields, default NULL
  */
  public function __construct(
    $owner, $params, $paramName, $data = array(), $fieldName = NULL, $hidden = NULL
  ) {
    $this->owner = $owner;
    $this->params = $params;
    $this->paramName = $paramName;
    $this->data = $data;
    if (isset($fieldName) && !empty($fieldName)) {
      $this->fieldName = $fieldName;
    }
    if (isset($hidden) && !empty($hidden)) {
      $this->hiddenFields = $hidden;
    }
  }

  /**
  * Main output method to get browser output xml.
  *
  * @return string
  */
  public function getXml() {
    return '';
  }

  /*******************
  * Helper
  ************************************************************/

  /**
  * Returns a link with valid params and param name.
  * @param array $furtherparams optional
  * @return string
  */
  public function getLink(array $furtherparams = array()) {
    $link = new PapayaUiReference();
    $link->papaya($this->papaya());
    $params = PapayaUtilArray::merge($this->params, $furtherparams);
    $link->setParameters($params, $this->paramName);
    return $link->getRelative($this->papaya()->request->getUrl());
  }
}
<?php
/**
* Configuration manager for themes
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
* @version $Id: Configuration.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Configuration class for themes
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiAdministrationBrowserThemeConfiguration {

  /**
  * DOM Document for parsing XML.
  * @var DOMDocument
  */
  private $_domDocument;

  /**
  * XML configuration file of theme.
  * @var string
  */
  private $_themeConfigurationFile = 'theme.xml';

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
   * Retrieves themes configurations.
   * Returned array structure:
   * array(
   *   'name' => {THEME NAME}
   *   'templates' => {TEMPLATE DIRECTORY}
   *   'version' => {VERSION NUMBER}
   *   'date' => {ISO DATE}
   *   'author' => {AUTHOR NAME}
   *   'description' => {DESCRIPTION TEXT}
   *   'thumbMedium' => {THUMB PATH}
   *   'thumbLarge' => {THUMB PATH}
   * )
   *
   * @param string $themePath
   * @return array
   */
  public function getThemeConfiguration($themePath) {
    $result = array();
    $document = $this->getDOMDocumentObject();
    $xmlFile = $themePath . '/' . $this->_themeConfigurationFile;
    if ($this->loadXml($xmlFile)) {
      $xpath = new DOMXPath($document);
      $result['name'] = $xpath->evaluate('string(//papaya-theme/name)', $document);
      $result['templates'] = $xpath->evaluate(
        'string(//papaya-theme/templates/@directory)',
        $document
      );
      $result['version'] = $xpath->evaluate(
        'string(//papaya-theme/version/@number)',
        $document
      );
      $result['date'] = $xpath->evaluate(
        'string(//papaya-theme/version/@date)',
        $document
      );
      $result['author'] = $xpath->evaluate('string(//papaya-theme/author)', $document);
      $result['description'] = $xpath->evaluate('string(//papaya-theme/description)', $document);
      $result['thumbMedium'] = $xpath->evaluate(
        "string(//papaya-theme/thumbs/thumb[@size = 'medium']/@src)",
        $document
      );
      $result['thumbLarge'] = $xpath->evaluate(
        "string(//papaya-theme/thumbs/thumb[@size = 'large']/@src)",
        $document
      );
    }
    return $result;
  }

  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * Retrieves a DOMDocument object.
  * @return DOMDocument
  */
  public function getDOMDocumentObject() {
    if (!(isset($this->_domDocument) && $this->_domDocument instanceof DOMDocument)) {
      $this->_domDocument = new DOMDocument;
      $this->_domDocument->preserveWhiteSpace = FALSE;
    }
    return $this->_domDocument;
  }

  /**
  * Loads the xml configuration if exists.
  *
  * @param string $xmlFilePath path & file of xml configuration file
  * @return boolean result
  */
  public function loadXml($xmlFilePath) {
    $result = FALSE;
    if (file_exists($xmlFilePath)) {
      $document = $this->getDOMDocumentObject();
      $result = (FALSE !== $document->load($xmlFilePath));
    }
    return $result;
  }
}
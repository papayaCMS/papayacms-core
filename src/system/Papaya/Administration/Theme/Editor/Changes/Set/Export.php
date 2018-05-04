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
* Import theme set values from an uploaded file
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationThemeEditorChangesSetExport
  extends PapayaUiControlCommand {

  /**
   * @var PapayaContentThemeSet
   */
  private $_themeSet = NULL;

  /**
   * @var PapayaThemeHandler
   */
  private $_themeHandler = NULL;

  /**
   * @param \PapayaContentThemeSet $themeSet
   * @param \PapayaThemeHandler $themeHandler
   */
  public function __construct(\PapayaContentThemeSet $themeSet, \PapayaThemeHandler $themeHandler) {
    $this->_themeSet = $themeSet;
    $this->_themeHandler = $themeHandler;
  }

  /**
   * @param \PapayaXmlElement $parent
   */
  public function appendTo(\PapayaXmlElement $parent) {
    $this->_themeSet->load($this->parameters()->get('set_id', 0));
    $themeName = $this->_themeSet['theme'];
    $response = $this->papaya()->response;
    $response->setStatus(200);
    $response->sendHeader(
      sprintf(
        'Content-Disposition: attachment; filename="%s.xml"',
        str_replace(
          array('\\', '"'),
          array('\\\\', '\\"'),
          $themeName.' '.$this->_themeSet['title']
        )
      )
    );
    $response->setContentType('application/octet-stream');
    $response->content(
      new \PapayaResponseContentString(
        $this
          ->_themeSet
          ->getValuesXml(
            $this->_themeHandler->getDefinition($themeName)
          )
          ->saveXml()
      )
    );
    $response->send();
    $response->end();
  }
}

<?php
/**
* Use page and categoriy ids as cache identifer conditions
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
* @subpackage Plugins
* @version $Id: Page.php 39416 2014-02-27 17:02:47Z weinert $
*/

/**
* Use page and categoriy ids as cache identifer conditions
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaCacheIdentifierDefinitionPage
  extends PapayaObject
  implements PapayaCacheIdentifierDefinition {

  /**
   * Return data for the specified page
   *
   * @see PapayaCacheIdentifierDefinition::getStatus()
   * @return boolean|array
   */
  public function getStatus() {
    $isPreview = $this->papaya()->request->getParameter(
      'preview', FALSE, NULL, PapayaRequest::SOURCE_PATH
    );
    if ($isPreview) {
      return FALSE;
    }
    $data = array(
      'scheme' => PapayaUtilServerProtocol::get(),
      'host' => PapayaUtilServerName::get(),
      'port' => PapayaUtilServerPort::get(),
      'category_id' => $this->papaya()->request->getParameter(
        'category_id', 0, NULL, PapayaRequest::SOURCE_PATH
      ),
      'page_id' => $this->papaya()->request->getParameter(
        'page_id', 0, NULL, PapayaRequest::SOURCE_PATH
      ),
      'language' => $this->papaya()->request->getParameter(
        'language', '', NULL, PapayaRequest::SOURCE_PATH
      ),
      'output_mode' => $this->papaya()->request->getParameter(
        'output_mode',
        $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html'),
        NULL,
        PapayaRequest::SOURCE_PATH
      )
    );
    return empty($data) ? TRUE : array(get_class($this) => $data);
  }

  /**
   * page id and category id are from the url path.
   *
   * @see PapayaCacheIdentifierDefinition::getSources()
   * @return integer
   */
  public function getSources() {
    return self::SOURCE_URL;
  }
}
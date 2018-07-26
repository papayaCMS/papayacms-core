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
* Grouped access to phrases. This is a factory for phrase objects. The methods create
* objects with access to the translations engine. If needed the objects fetch the
* translation.
*
* @package Papaya-Library
* @subpackage Phrases
*/
class PapayaPhrasesGroup {

  private $_phrases = NULL;
  private $_name = '';

  public function __construct(\Papaya\Phrases $phrases, $name) {
    \PapayaUtilConstraints::assertNotEmpty($name);
    $this->_phrases = $phrases;
    $this->_name = $name;
  }

  /**
   * A string object
   *
   * @param string $phrase
   * @param array $arguments
   * @return \PapayaUiStringTranslated
   */
  public function get($phrase, array $arguments = array()) {
    $result = new \PapayaUiStringTranslated(
      $phrase, $arguments, $this->_phrases, $this->_name
    );
    return $result;
  }

  /**
   * A string list object
   *
   * @param array|\Traversable $phrases
   * @return \PapayaUiStringTranslatedList
   */
  public function getList($phrases) {
    $result = new \PapayaUiStringTranslatedList(
      $phrases, $this->_phrases, $this->_name
    );
    return $result;
  }

}

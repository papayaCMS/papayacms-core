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
* Field factory profiles for a field with two radio boxes displaying "yes" and "no"
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactoryProfileSelectBoolean
  extends PapayaUiDialogFieldFactoryProfileSelect {

  /**
   * Create a select field with two elements displayed as radio boxes
   *
   * @param array|Traversable $elements
   * @return PapayaUiDialogFieldSelect
   */
  protected function createField($elements) {
    return new \PapayaUiDialogFieldSelectRadio(
      $this->options()->caption,
      $this->options()->name,
      new \PapayaUiStringTranslatedList(array('no', 'yes'))
    );
  }
}

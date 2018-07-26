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
 * Class PapayaPluginEditableOptions
 */
class PapayaPluginEditableOptions extends PapayaPluginEditableData {

  /**
   * Checksum buffer filled in {@see \PapayaPluginEditableOptions::modified()}
   * @var string|NULL
   */
  private $_checksum;

  public function __construct(PapayaPluginOptions $options) {
    parent::__construct(iterator_to_array($options));
    $this->_checksum = $this->getChecksum();
  }

  /**
   * Check if the contained data was modified.
   *
   * @return boolean
   */
  public function modified() {
    return $this->_checksum !== $this->getChecksum();
  }
}

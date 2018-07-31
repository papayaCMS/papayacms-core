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

namespace Papaya\Plugin\Editable;
/**
 * Class \Papaya\Plugin\Editable\PapayaPluginEditableOptions
 */
class Options extends Data {

  /**
   * Checksum buffer filled in {@see \Papaya\Plugin\Editable\PapayaPluginEditableOptions::modified()}
   *
   * @var string|NULL
   */
  private $_checksum;

  public function __construct(\Papaya\Plugin\Options $options) {
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

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

use Papaya\Utility;

/**
 * An editable module content, the content needs to be provided as array
 * serialized as an XML string.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Content extends Data {
  /**
   * Checksum buffer filled in {@see \Papaya\Plugin\Editable\Content::modified()}
   *
   * @var string|null
   */
  private $_checksum;

  /**
   * Set serialized data from a string. The format is a simple xml.
   *
   * @param string $xml
   */
  public function setXML($xml) {
    $this->clear();
    $this->merge(Utility\Text\XML::unserializeArray($xml));
    $this->_checksum = $this->getChecksum();
  }

  /**
   * Get serialized data as a string. The format is a simple xml.
   *
   * @return string
   */
  public function getXML() {
    return Utility\Text\XML::serializeArray((array)$this);
  }

  /**
   * Check if the contained data was modified. The data is considered modified if it was not
   * set using {@see \Papaya\Plugin\Editable\Content::setXML()} or the generated checksum is
   * different.
   *
   * @return bool
   */
  public function modified() {
    if (NULL !== $this->_checksum) {
      return $this->_checksum !== $this->getChecksum();
    }
    return TRUE;
  }
}

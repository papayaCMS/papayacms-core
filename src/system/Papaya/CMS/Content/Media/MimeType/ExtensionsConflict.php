<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS\Content\Media\MimeType {

  class ExtensionsConflict extends \Papaya\Database\Exception {

    /**
     * @var array
     */
    private $_extensionUsage;

    /**
     * @param array $extensionUsage [type => [ext1, ext2, ...], ...]
     */
    public function __construct(array $extensionUsage) {
      $this->_extensionUsage = $extensionUsage;
      parent::__construct(
        sprintf(
          'The following extension(s) are already used by other mime types: %s',
          $this->getExtensionUsageString()
        ),
        0,
        self::SEVERITY_ERROR
      );
    }

    public function getExtensionUsage() {
      return $this->_extensionUsage;
    }

    public function getExtensionUsageString() {
      return implode(
        ', ',
        array_map(
          static function (array $extensions, $mimeType) {
            return implode(', ', $extensions).' ('.$mimeType.')';
          },
          $this->_extensionUsage,
          array_keys($this->_extensionUsage)
        )
      );
    }
  }
}


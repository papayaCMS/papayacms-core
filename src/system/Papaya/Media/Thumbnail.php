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

namespace Papaya\Media {

  use Papaya\File\Reference;
  use Papaya\Utility\File;

  class Thumbnail implements Reference {

    private $_localFileName;
    /**
     * @var string|null
     */
    private $_type;
    /**
     * @var int|null
     */
    private $_size;
    /**
     * @var string
     */
    private $_name;

    public function __construct($localFileName, $name = '', $type = NULL) {
      $this->_localFileName = (string)$localFileName;
      $this->_type = $type;
      $this->setName($name ?: 'file');
    }

    public function getName() {
      return $this->_name;
    }

    public function setName(string $name) {
      $this->_name = empty($name) ? basename($this->_localFileName) : $name;
    }

    public function getType() {
      if ($this->_type === NULL) {
        list(, , $imageType) = getimagesize($this->_localFileName);
        $this->_type = image_type_to_mime_type($imageType);
      }
      return $this->_type;
    }

    public function getSize() {
      if ($this->_size === NULL) {
        $this->_size = filesize($this->_localFileName);
      }
      return $this->_size;
    }

    public function getLocalFileName() {
      return $this->_localFileName;
    }

    public function getURL() {
      return File::normalizeName($this->_name, 50).'.thumb.'.basename($this->getLocalFileName());
    }

    public function getMediaURI() {
      return basename($this->getLocalFileName());
    }

    public function __toString() {
      return $this->getURL();
    }

  }
}

<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\UI\Content\Teaser {

  use Papaya\XML\Element as XMLElement;

  class Thumbnails extends Images {

    /**
     * @var XMLElement
     */
    private $_teasers;

    public function __construct(XMLElement $teasers, $width, $height, $resizeMode = 'max') {
      parent::__construct($width, $height, $resizeMode);
      $this->_teasers = $teasers;
    }

    public function appendTo(XMLElement $parent, XMLElement $teasers = NULL) {
      return parent::appendTo($parent, $teasers ?: $this->_teasers);
    }
  }
}


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

namespace Papaya\Template\Tag {

  use Papaya\Template\Tag as TemplateTag;
  use Papaya\Utility\Arrays as ArrayUtilities;
  use Papaya\XML\Document as XMLDocument;
  use Papaya\XML\Element as XMLElement;

  class Image extends TemplateTag {

    /**
     * @var string
     */
    private $_source;

    /**
     * @var int
     */
    private $_width;

    /**
     * @var int
     */
    private $_height;

    /**
     * @var string
     */
    private $_alternativeText;

    /**
     * @var string
     */
    private $_resize;

    /**
     * @var string
     */
    private $_subtitle;

    /**
     * @var string
     */
    private $_tagPattern = '(
      <(?<tag>(?:papaya|ndim):(?:[a-z]\w+))
      \\s*?
      (?<parameters>[^>]*)/?>
    )x';

    /**
     * @var string
     */
    private $_tagParametersPattern = '(
      (?<name>\w+)=(?<quote>[\'"])(?<value>.*?)\g{quote}
    )x';

    /**
     * @var string
     */
    private $_mediaPropertyPattern = '(
      ^(?<src>[^.,]+(?:\.\w+)?)
      (?:,
        (?<width>\d+)
        (?:,
          (?<height>\d+)
          (?:,
            (?<resize>\w+)
          )?
        )?
      )?$
    )x';

    /**
     * Constructor
     *
     * @param string $mediaPropertyString this is the string the dialog type image(?)
     *                                    contains like "32242...,max,200,300"
     * @param int $width optional, default value 0
     * @param int $height optional, default value 0
     * @param string $alt optional, default value ''
     * @param string $resize optional, default value ''
     * @param string $subtitle optional, default value ''
     */
    public function __construct(
      $mediaPropertyString, $width = 0, $height = 0, $alt = '', $resize = '', $subtitle = ''
    ) {
      if (!($data = $this->parseMediaTag($mediaPropertyString))) {
        $matches = [];
        if (preg_match($this->_mediaPropertyPattern, $mediaPropertyString, $matches)) {
          $data = $matches;
        }
      }
      $this->_source = ArrayUtilities::get($data, 'src', $mediaPropertyString);
      $this->_width = ($width > 0) ? $width : ArrayUtilities::get($data, 'width', 0);
      $this->_height = ($height > 0) ? $height : ArrayUtilities::get($data, 'height', 0);
      $this->_resize = trim($resize) !== '' ? $resize : ArrayUtilities::get($data, 'resize', '');
      $this->_alternativeText = trim($alt) !== '' ? $alt : ArrayUtilities::get($data, 'alt', '');
      $this->_subtitle = trim($alt) !== '' ? $alt : ArrayUtilities::get($data, 'subtitle', '');
    }

    /**
     * Append the generated papaya:media element to a parent node
     *
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $attributes = [];
      if (!empty($this->_source)) {
        $attributes['src'] = $this->_source;
      } else {
        return;
      }
      if ($this->_width > 0) {
        $attributes['width'] = $this->_width;
      }
      if ($this->_height > 0) {
        $attributes['height'] = $this->_height;
      }
      if ('' !== \trim($this->_alternativeText)) {
        $attributes['alt'] = $this->_alternativeText;
      }
      if ('' !== \trim($this->_resize)) {
        $attributes['resize'] = $this->_resize;
      }
      if ('' !== \trim($this->_subtitle)) {
        $attributes['subtitle'] = $this->_subtitle;
      }
      $document = $parent->ownerDocument;
      $imageTag = $document->createElementNS(XMLDocument::XMLNS_PAPAYA, 'papaya:media');
      foreach ($attributes as $name => $value) {
        $imageTag->setAttribute($name, $value);
      }
      $parent->appendChild($imageTag);
    }

    /**
     * Parse parameters into an array if this is a papaya tag.
     *
     * @param string $mediaTag
     * @return array|NULL
     */
    private function parseMediaTag($mediaTag) {
      $matches = [];
      $data = [];
      if (
        preg_match($this->_tagPattern, $mediaTag, $matches) &&
        preg_match_all($this->_tagParametersPattern, $matches['parameters'], $matches, PREG_SET_ORDER)
      ) {
        foreach ($matches as $match) {
          $data[$match['name']] = $match['value'];
        }
        return $data;
      }
      return NULL;
    }
  }
}

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

namespace Papaya\UI\Listview\Subitem;
/**
 * A simple listview subitem displaying an image.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property integer $align
 * @property string $image
 * @property string|\Papaya\UI\Text $hint
 * @property array $actionParameters
 * @property \Papaya\UI\Reference $reference
 */
class Image extends Text {

  /**
   * buffer for image index or filename
   *
   * @var string
   */
  protected $_image = '';
  /**
   * buffer for text variable
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_hint = '';

  /**
   * Basic reference/link
   *
   * @var \Papaya\UI\Reference
   */
  protected $_reference = NULL;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'image' => array('_image', '_image'),
    'hint' => array('_hint', '_hint'),
    'actionParameters' => array('_actionParameters', 'setActionParameters'),
    'reference' => array('reference', 'reference')
  );

  /**
   * Create subitem object, set text content and alignment.
   *
   * @param \Papaya\UI\Text|string $image
   * @param string $hint
   * @param array $actionParameters
   */
  public function __construct($image, $hint = '', array $actionParameters = NULL) {
    parent::__construct('', $actionParameters);
    $this->_image = $image;
    $this->_hint = $hint;
  }

  /**
   * Append subitem xml data to parent node.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $subitem = $parent->appendElement(
      'subitem',
      array(
        'align' => \Papaya\UI\Option\Align::getString($this->getAlign())
      )
    );
    if (!empty($this->_image)) {
      $glyph = $subitem->appendElement(
        'glyph',
        array(
          'src' => $this->papaya()->images[(string)$this->_image],
          'hint' => (string)$this->_hint
        )
      );
      if (!empty($this->_actionParameters)) {
        $glyph->setAttribute('href', $this->getUrl());
      }
    }
  }
}

<?php
/**
* A simple listview subitem displaying an image.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Image.php 39420 2014-02-27 17:40:37Z weinert $
*/

/**
* A simple listview subitem displaying an image.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property integer $align
* @property string $image
* @property string|PapayaUiString $hint
* @property array $actionParameters
* @property PapayaUiReference $reference
*/
class PapayaUiListviewSubitemImage extends PapayaUiListviewSubitemText {

  /**
  * buffer for image index or filename
  *
  * @var string
  */
  protected $_image = '';
  /**
  * buffer for text variable
  *
  * @var string|PapayaUiString
  */
  protected $_hint = '';

  /**
  * Basic reference/link
  *
  * @var PapayaUiReference
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
   * @param PapayaUiString|string $image
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
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $subitem = $parent->appendElement(
      'subitem',
      array(
        'align' => PapayaUiOptionAlign::getString($this->getAlign())
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
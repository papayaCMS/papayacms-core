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
* A panel containing an iframe showing an given reference.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|PapayaUiString $caption
* @property string $name
* @property string $height
* @property \PapayaUiReference $reference
* @property \PapayaUiToolbars $toolbars
*/
class PapayaUiPanelFrame extends \PapayaUiPanel {

  /**
  * The url reference object.
  *
  * @var \PapayaUiReference
  */
  protected $_reference = NULL;

  /**
  * A name/identifier for the frame, that can be used in link targets.
  *
  * @var string
  */
  protected $_name = '';

  /**
  * The height of the iframe
  *
  * @var string
  */
  protected $_height = '400';

  /**
  * Declared public properties, see property annotaiton of the class for documentation.
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'caption' => array('_caption', 'setCaption'),
    'name' => array('_name', '_name'),
    'height' => array('_height', '_height'),
    'reference' => array('reference', 'reference'),
    'toolbars' => array('toolbars', 'toolbars')
  );

  /**
  * Initialize object and store parameters.
  *
  * @param string|\PapayaUiString $caption
  * @param string $name
  * @param string $height
  */
  public function __construct($caption, $name, $height = '400') {
    $this->setCaption($caption);
    $this->_name = $name;
    $this->_height = $height;
  }

  /**
  * Append iframe to panel xml element.
  *
  * @see papaya-lib/system/Papaya/Ui/PapayaUiPanel#appendTo($parent)
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $panel = parent::appendTo($parent);
    $panel->appendElement(
      'iframe',
      array(
        'id' => (string)$this->_name,
        'src' => $this->reference()->getRelative(),
        'height' => (string)$this->_height
      )
    );
    return $panel;
  }

  /**
  * Getter/Setter for the reference object.
  *
  * @param \PapayaUiReference $reference
  * @return \PapayaUiReference
  */
  public function reference(\PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new \PapayaUiReference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}

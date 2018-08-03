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

namespace Papaya\UI\Panel;
/**
 * A panel containing an iframe showing an given reference.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property string $name
 * @property string $height
 * @property \Papaya\UI\Reference $reference
 * @property \Papaya\UI\Toolbars $toolbars
 */
class Frame extends \Papaya\UI\Panel {

  /**
   * The url reference object.
   *
   * @var \Papaya\UI\Reference
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
   * @param string|\Papaya\UI\Text $caption
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
   * @see \Papaya\UI\Panel#appendTo($parent)
   */
  public function appendTo(\Papaya\XML\Element $parent) {
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
   * @param \Papaya\UI\Reference $reference
   * @return \Papaya\UI\Reference
   */
  public function reference(\Papaya\UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new \Papaya\UI\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}

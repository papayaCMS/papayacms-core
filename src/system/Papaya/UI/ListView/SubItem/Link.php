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
namespace Papaya\UI\ListView\SubItem;

use Papaya\UI;
use Papaya\XML;

/**
 * A simple listview subitem displaying text.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $align
 * @property string|\Papaya\UI\Text $text
 * @property array $actionParameters
 * @property UI\Reference $reference
 */
abstract class Link extends UI\ListView\SubItem {

  /**
   * Basic reference/link
   *
   * @var UI\Reference
   */
  protected $_reference;

  /**
   * @var null
   */
  protected $_actionParameters;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'align' => ['getAlign', 'setAlign'],
    'actionParameters' => ['_actionParameters', 'setActionParameters'],
    'reference' => ['reference', 'reference']
  ];

  /**
   * Create subitem object, set text content and alignment.
   *
   * @param array $actionParameters
   */
  public function __construct(array $actionParameters = NULL) {
    $this->setActionParameters($actionParameters);
  }

  /**
   * Getter/Setter for the reference subobject, this will be initialized from the listview
   * if not set.
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      // directly return the reference, so it is possible to recognise if it was set.
      /* @noinspection PhpUndefinedMethodInspection */
      return $this->collection()->getListview()->reference();
    }
    return $this->_reference;
  }

  /**
   * Use the action parameter and the reference from the items to get an url for the output xml.
   *
   * If you assigned a reference object the action parameters will be applied without an additional
   * parameter group. If the reference is fetched from the listview, the listview parameter group
   * will be used.
   *
   * @return string
   */
  protected function getURL() {
    $reference = clone $this->reference();
    if (NULL !== $this->_reference) {
      $reference->setParameters($this->_actionParameters);
    } else {
      /* @noinspection PhpUndefinedMethodInspection */
      $reference->setParameters(
        $this->_actionParameters,
        $this->collection()->getListview()->parameterGroup()
      );
    }
    return $reference->getRelative();
  }
}

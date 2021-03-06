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
namespace Papaya\UI\Dialog\Field\XHTML;

use Papaya\UI;
use Papaya\XML;

/**
 * A field that outputs a link inside the dialog.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Link extends UI\Dialog\Field {
  /**
   * Link url
   *
   * @var string|UI\Text
   */
  protected $_url = '';

  /**
   * Link caption
   *
   * @var string|UI\Text
   */
  protected $_urlCaption = '';

  /**
   * Create object and assign needed values.
   *
   * @param string|UI\Text $url
   * @param string|UI\Text $caption
   */
  public function __construct($url, $caption = NULL) {
    $this->_url = $url;
    if (!empty($caption)) {
      $this->_urlCaption = $caption;
    }
  }

  /**
   * Append xhtml field to dialog xml dom.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field
      ->appendElement('xhtml')
      ->appendElement('a', ['href' => $this->_url], (string)$this->_urlCaption);
  }
}

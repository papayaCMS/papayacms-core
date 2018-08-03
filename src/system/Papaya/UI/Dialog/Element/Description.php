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

namespace Papaya\UI\Dialog\Element;
/**
 * Superclass for dialog element description. In the most cases this is a separate page opened
 * directly or in an popup, so it needs a reference
 *
 * For simple text information the dialog fields use the "hint".
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @codeCoverageIgnore
 */
class Description extends \Papaya\UI\Control\Collection {

  protected $_itemClass = Description\Item::class;

  protected $_tagName = 'description';
}

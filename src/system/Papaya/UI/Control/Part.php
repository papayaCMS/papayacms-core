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
namespace Papaya\UI\Control;

use Papaya\Application;
use Papaya\BaseObject;

/**
 * Abstract superclass implementing basic features for user interface control parts.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Part
  implements Application\Access, \Papaya\XML\Appendable {
  use Application\Access\Aggregation;
  use BaseObject\DeclaredProperties;

  /**
   * @var array
   */
  protected $_declaredProperties = [];

  public function getPropertyDeclaration() {
    return $this->_declaredProperties;
  }
}

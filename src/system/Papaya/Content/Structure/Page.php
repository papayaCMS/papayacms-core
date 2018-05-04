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
* Content structure page element
*
* Content structure values are organized in groups and pages. A page can contain multiple groups
* and a group multiple values.
*
* @package Papaya-Library
* @subpackage Content
*
* @property string $title
* @property string $name
*/
class PapayaContentStructurePage extends PapayaContentStructureNode {

  private $_groups = NULL;

  public function __construct() {
    parent::__construct(
      array(
        'name' => 'page',
        'title' => ''
      )
    );
  }

  /**
   * Groups defined for this page
   *
   * @param \PapayaContentStructureGroups $groups
   * @return \PapayaContentStructureGroups
   */
  public function groups(\PapayaContentStructureGroups $groups = NULL) {
    if (isset($groups)) {
      $this->_groups = $groups;
    } elseif (NULL === $this->_groups) {
      $this->_groups = new \PapayaContentStructureGroups($this);
    }
    return $this->_groups;
  }

  /**
   * Get the identifier for this page
   *
   * @return string
   */
  public function getIdentifier() {
    return $this->name;
  }
}

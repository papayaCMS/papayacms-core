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

namespace Papaya\Content\Structure;

/**
 * Content structure values group list
 *
 * Content structure values are organized in groups and pages. A page can contain multiple groups
 * and a group multiple values.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Groups extends \Papaya\BaseObject\Collection {

  private $_page;

  public function __construct(Page $page) {
    parent::__construct(Group::class);
    $this->_page = $page;
  }

  /**
   * Load group data from xml
   *
   * @param \Papaya\XML\Element $pageNode
   */
  public function load(\Papaya\XML\Element $pageNode) {
    /** @var \Papaya\XML\Document $document */
    $document = $pageNode->ownerDocument;
    /** @var \Papaya\XML\Element $node */
    foreach ($document->xpath()->evaluate('group', $pageNode) as $node) {
      $this[] = $group = new Group($this->_page);
      $group->name = $node->getAttribute('name');
      $group->title = $node->getAttribute('title');
      $group->values()->load($node);
    }
  }
}

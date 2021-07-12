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
namespace Papaya\CMS\Content\Structure;

use Papaya\BaseObject;
use Papaya\XML;

/**
 * Content structure pages, defines the main items list of a constent structure.
 *
 * Content structures are organized in groups and pages. A page can contain multiple groups
 * and a group multiple values.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Pages extends BaseObject\Collection {
  public function __construct() {
    parent::__construct(Page::class);
  }

  /**
   * Load page data from xml
   *
   * @param XML\Element $structure
   */
  public function load(XML\Element $structure) {
    /** @var XML\Document $document */
    $document = $structure->ownerDocument;
    /** @var XML\Element $node */
    foreach ($document->xpath()->evaluate('page', $structure) as $node) {
      $this[] = $page = new Page();
      $page->name = $node->getAttribute('name');
      $page->title = $node->getAttribute('title');
      $page->groups()->load($node);
    }
  }
}

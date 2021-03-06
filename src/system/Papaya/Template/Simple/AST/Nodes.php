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
namespace Papaya\Template\Simple\AST;

use Papaya\BaseObject;
use Papaya\Template\Simple;

class Nodes
  extends BaseObject\Collection
  implements Simple\AST {
  /**
   * Nodes constructor.
   *
   * @param array $nodes
   */
  public function __construct(array $nodes = []) {
    parent::__construct(Node::class);
    foreach ($nodes as $node) {
      $this[] = $node;
    }
  }

  /**
   * Tell the nodes about the visitor.
   *
   * @param Simple\Visitor $visitor
   */
  public function accept(Simple\Visitor $visitor) {
    /** @var Simple\AST $node */
    foreach ($this as $node) {
      $node->accept($visitor);
    }
  }
}

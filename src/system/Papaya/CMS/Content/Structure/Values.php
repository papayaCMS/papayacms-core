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
 * Content structure values list
 *
 * Content structure values are organized in groups and pages. A page can contain multiple groups
 * and a group multiple values.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Values extends BaseObject\Collection {
  private $_group;

  /**
   * Values constructor.
   *
   * @param Group $group
   */
  public function __construct(Group $group) {
    parent::__construct(Value::class);
    $this->_group = $group;
  }

  /**
   * Load value data from xml
   *
   * @param XML\Element $groupNode
   */
  public function load(XML\Element $groupNode) {
    /** @var XML\Document $document */
    $document = $groupNode->ownerDocument;
    /** @var XML\Element $node */
    foreach ($document->xpath()->evaluate('value', $groupNode) as $node) {
      $this[] = $value = new Value($this->_group);
      $value->name = $node->getAttribute('name');
      $value->title = $node->getAttribute('title');
      $value->default = $node->getAttribute('default');
      if ($node->hasAttribute('type')) {
        $value->type = $node->getAttribute('type');
      }
      if ($node->hasAttribute('hint')) {
        $value->hint = $node->getAttribute('hint');
      } else {
        $value->hint = $document->xpath()->evaluate('string(hint)', $node);
      }
      $value->fieldType = $node->getAttribute('field');
      if ($node->hasAttribute('field-parameter')) {
        $value->fieldParameters = $node->getAttribute('field-parameter');
      } else {
        $parameterNodes = $document->xpath()->evaluate('field-parameter', $node);
        if ($parameterNodes->length > 0) {
          $fieldParameters = [];
          /** @var XML\Element $parameterNode */
          foreach ($parameterNodes as $parameterNode) {
            $key = $parameterNode->getAttribute('key');
            $text = $parameterNode->textContent;
            if (empty($key)) {
              $fieldParameters[$text] = $text;
            } else {
              $fieldParameters[$key] = $text;
            }
          }
          $value->fieldParameters = $fieldParameters;
        }
      }
    }
  }
}

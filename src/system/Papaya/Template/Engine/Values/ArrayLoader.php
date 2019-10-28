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
namespace Papaya\Template\Engine\Values {

  use Papaya\Utility\Constraints;
  use Papaya\XML\Document;
  use Papaya\XML\Element;

  class ArrayLoader implements Loadable {

    /**
     * @param array|\Traversable $values
     * @return false|Document|Element
     */
    public function load($values) {
      Constraints::assertArrayOrTraversable($values);
      $document = new Document();
      $this->appendArray($document->appendElement('_'), $values);
      return $document->documentElement;
    }

    /**
     * @param Element $parent
     * @param mixed $data
     */
    private function appendArray(Element $parent, $data) {
      foreach ($data as $key => $value) {
        $nodeName = \preg_replace('([^A-Za-z_-])', '', $key) ?: NULL;
        if (!$nodeName) {
          continue;
        }
        if (\is_array($value) || $value instanceof \Traversable) {
          $this->appendArray($parent->appendElement($nodeName), $value);
        } else {
          $parent->appendElement($nodeName, (string)$value);
        }
      }
    }
  }
}

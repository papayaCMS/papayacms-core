<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Database\Schema\Structure {

  use Papaya\BaseObject\Collection;
  use Papaya\XML\Appendable;
  use Papaya\XML\Element;

  /**
   * @property string $name
   */
  class FieldsStructure extends Collection implements Appendable {

    public function __construct() {
      parent::__construct(FieldStructure::class, self::MODE_ASSOCIATIVE);
    }

    /**
     * @param \Papaya\XML\Element $parent
     */
    public function appendTo(Element $parent) {
      $parent->appendElement('fields', ...iterator_to_array($this, FALSE));
    }

    /**
     * @param string $name
     * @param null|IndexStructure $value
     * @return string
     */
    protected function prepareKey($name, $value = NULL) {
      if (isset($value) && $name === NULL) {
        $name = $value->name;
      }
      return strtolower($name);
    }
  }
}


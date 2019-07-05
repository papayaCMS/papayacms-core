<?php /**
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
 */ /** @noinspection ALL */

namespace Papaya\Database\Schema\Structure {

  use Papaya\BaseObject\DeclaredProperties;
  use Papaya\BaseObject\Interfaces\Properties\Declared;

  /**
   * @property string $name
   */
  class KeyFieldStructure implements Declared {

    use DeclaredProperties;

    private $_name;
    private $_size;

    public function __construct($name, $size) {
      if (trim($name) === '') {
        throw new \InvalidArgumentException('Key name can not be empty.');
      }
      $this->_name = $name;
    }

    public static function createFromXML(\DOMElement $node) {
      $xpath = new \DOMXpath($node instanceof \DOMDocument ? $node : $node->ownerDocument);
      $key = new self($node->localName === 'primary-key' ? 'PRIMARY' : $node->getAttribute('name'));
      foreach ($xpath->evaluate('field', $node) as $fieldNode) {

      }
      return $key;
    }

    /**
     * @return array
     */
    public static function getPropertyDeclaration() {
      return [
        'name' => ['_name']
      ];
    }
  }
}

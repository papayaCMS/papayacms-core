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
 */

/** @noinspection UnusedConstructorDependenciesInspection */

namespace Papaya\Database\Schema\Structure {

  use Papaya\BaseObject\DeclaredProperties;
  use Papaya\BaseObject\Interfaces\Properties\Declared;

  /**
   * @property string $name
   * @property FieldsStructure $fields
   * @property KeysStructure $keys
   */
  class TableStructure implements Declared {

    use DeclaredProperties;

    /**
     * @var string
     */
    private $_tableName;

    private $_usePrefix;

    /**
     * @var FieldsStructure
     */
    private $_fields;

    /**
     * @var KeysStructure
     */
    private $_keys;

    /**
     * @param string $name
     * @param bool $usePrefix
     */
    public function __construct($name, $usePrefix = TRUE) {
      if (trim($name) === '') {
        throw new \UnexpectedValueException('Table name can not be empty.');
      }
      $this->_tableName = $name;
      $this->_usePrefix = (bool)$usePrefix;
      $this->_fields = new FieldsStructure();
      $this->_keys = new KeysStructure();
    }

    public static function createFromXML(\DOMNode $node) {
      $xpath = new \DOMXpath($node instanceof \DOMDocument ? $node : $node->ownerDocument);
      /** @var \DOMElement|NULL $tableNode */
      $tableNode = $xpath->evaluate('//table')->item(0);
      if (!$tableNode) {
        throw new \UnexpectedValueException('Can not find "table" element.');
      }
      $table = new self(
        $tableNode->getAttribute('name'),
        $tableNode->getAttribute('prefix') === 'yes'
      );
      foreach ($xpath->evaluate('//table/fields/field', $node) as $fieldNode) {
        $table->fields[] = FieldStructure::createFromXML($fieldNode);
      }
      foreach ($xpath->evaluate('//table/keys/primary-key', $node) as $index => $fieldNode) {
        if ($index > 0) {
          throw new \UnexpectedValueException('Table has more then one primary key.');
        }
        $table->keys[] = KeyStructure::createFromXML($fieldNode);
      }
      foreach ($xpath->evaluate('//table/keys/key', $node) as $index => $fieldNode) {
        $table->keys[] = KeyStructure::createFromXML($fieldNode);
      }
      return $table;
    }

    /**
     * @return array
     */
    public static function getPropertyDeclaration() {
      return [
        'name' => ['_tableName'],
        'usePrefix' => ['_usePrefix'],
        'fields' => ['_fields'],
        'keys' => ['_keys']
      ];
    }
  }
}

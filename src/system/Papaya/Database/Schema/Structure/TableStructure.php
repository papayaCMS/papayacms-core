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
  use Papaya\BaseObject\Interfaces\Properties;
  use Papaya\XML\Document;

  /**
   * @property string $name
   * @property FieldsStructure|FieldStructure[] $fields
   * @property IndicesStructure|IndexStructure[] $indices
   */
  class TableStructure implements Properties {

    use DeclaredProperties;

    /**
     * @var string
     */
    private $_tableName;

    /**
     * @var bool
     */
    private $_usePrefix;

    /**
     * @var FieldsStructure
     */
    private $_fields;

    /**
     * @var IndicesStructure
     */
    private $_indices;

    /**
     * @param string $name
     * @param bool $usePrefix
     */
    public function __construct($name, $usePrefix = TRUE) {
      $this->setName($name);
      $this->_usePrefix = (bool)$usePrefix;
      $this->_fields = new FieldsStructure();
      $this->_indices = new IndicesStructure();
    }

    public function __clone() {
      $this->_fields = clone $this->_fields;
      $this->_indices = clone $this->_indices;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
      if (trim($name) === '') {
        throw new \UnexpectedValueException('Table name can not be empty.');
      }
      $this->_tableName = $name;
    }

    /**
     * @param \DOMNode $node
     * @return TableStructure
     */
    public static function createFromXML(\DOMNode $node) {
      $xpath = new \DOMXpath($node instanceof \DOMDocument ? $node : $node->ownerDocument);
      /** @var \DOMElement|NULL $tableNode */
      $tableNode = $xpath->evaluate('//table')->item(0);
      if (!$tableNode) {
        throw new \UnexpectedValueException('Can not find "table" element.');
      }
      $table = new self(
        $tableNode->getAttribute('name'),
        $tableNode->getAttribute('prefix') === 'yes' || $tableNode->getAttribute('use-prefix') === 'yes'
      );
      foreach ($xpath->evaluate('//table/fields/field', $node) as $fieldNode) {
        $table->fields[] = FieldStructure::createFromXML($fieldNode);
      }
      foreach ($xpath->evaluate('//table/keys/primary-key|//table/indices/primary-index', $node) as $index => $fieldNode) {
        if ($index > 0) {
          throw new \UnexpectedValueException('Table has more then one primary key.');
        }
        $table->indices[] = IndexStructure::createFromXML($fieldNode);
      }
      foreach ($xpath->evaluate('//table/keys/key|//table/indices/index', $node) as $index => $fieldNode) {
        $table->indices[] = IndexStructure::createFromXML($fieldNode);
      }
      return $table;
    }

    /**
     * @return Document
     */
    public function getXMLDocument() {
      $document = new Document();
      $document->formatOutput = TRUE;
      $node = $document->appendElement(
        'table',
        [ 'name' => $this->_tableName],
        $this->_fields,
        $this->_indices
      );
      if ($this->_usePrefix) {
        $node->setAttribute('use-prefix','yes');
      }
      return $document;
    }

    /**
     * @return array
     */
    public function getPropertyDeclaration() {
      return [
        'name' => ['_tableName', 'setName'],
        'usePrefix' => ['_usePrefix'],
        'fields' => ['_fields'],
        'indices' => ['_indices']
      ];
    }
  }
}

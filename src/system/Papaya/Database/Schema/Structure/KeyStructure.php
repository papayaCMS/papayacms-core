<?php /** @noinspection ALL */

namespace Papaya\Database\Schema\Structure {

  use Papaya\BaseObject\DeclaredProperties;
  use Papaya\BaseObject\Interfaces\Properties\Declared;

  /**
   * @property string $name
   * @property KeyFieldsStructure $fields
   * @property bool $isUnique
   * @property bool $isFullText
   */
  class KeyStructure implements Declared {

    const PRIMARY = 'PRIMARY';

    use DeclaredProperties;

    /**
     * @var string
     */
    private $_name;
    /**
     * @var KeyFieldsStructure
     */
    private $_fields;
    /**
     * @var bool
     */
    private $_isUnique;
    /**
     * @var bool
     */
    private $_isFullText;

    public function __construct($name, $isUnique = FALSE, $isFullText = FALSE) {
      if (trim($name) === '') {
        throw new \UnexpectedValueException('Key name can not be empty.');
      }
      $this->_name = $name;
      $this->_isUnique = $isUnique && !$isFullText;
      $this->_isFullText = (bool)$isFullText;
      $this->_fields = new KeyFieldsStructure();
    }

    public static function createFromXML(\DOMElement $node) {
      $xpath = new \DOMXpath($node instanceof \DOMDocument ? $node : $node->ownerDocument);
      $key = new self(
        $node->localName === 'primary-key' ? 'PRIMARY' : $node->getAttribute('name'),
        $node->getAttribute('unique') === 'yes',
        $node->getAttribute('fulltext') === 'yes'
      );
      foreach ($xpath->evaluate('field', $node) as $fieldNode) {
        $key->fields[] = KeyFieldStructure::createFromXML($fieldNode);
      }
      return $key;
    }

    /**
     * @return array
     */
    public static function getPropertyDeclaration() {
      return [
        'name' => ['_name'],
        'isUnique' => ['_isUnique'],
        'isFullText'=> ['_isFullText'],
        'fields' => ['_fields']
      ];
    }
  }
}

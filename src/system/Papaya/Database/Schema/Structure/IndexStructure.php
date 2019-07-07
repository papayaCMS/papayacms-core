<?php /** @noinspection ALL */

namespace Papaya\Database\Schema\Structure {

  use Papaya\BaseObject\DeclaredProperties;
  use Papaya\BaseObject\Interfaces\Properties\Declared;
  use Papaya\XML\Appendable;
  use Papaya\XML\Element;

  /**
   * @property string $name
   * @property IndexFieldsStructure $fields
   * @property bool $isUnique
   * @property bool $isFullText
   */
  class IndexStructure implements Declared, Appendable {

    const PRIMARY = 'PRIMARY';

    use DeclaredProperties;

    /**
     * @var string
     */
    private $_name;
    /**
     * @var IndexFieldsStructure
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
      $this->_name = strtoupper($name) === self::PRIMARY ? self::PRIMARY : $name;
      $this->_isUnique = $isUnique && !$isFullText;
      $this->_isFullText = (bool)$isFullText;
      $this->_fields = new IndexFieldsStructure();
    }

    /**
     * @return bool
     */
    public function isPrimary() {
      return $this->_name === self::PRIMARY;
    }

    public static function createFromXML(\DOMElement $node) {
      $xpath = new \DOMXpath($node instanceof \DOMDocument ? $node : $node->ownerDocument);
      $key = new self(
        $node->localName === 'primary-key' ? self::PRIMARY : $node->getAttribute('name'),
        $node->getAttribute('unique') === 'yes',
        $node->getAttribute('fulltext') === 'yes'
      );
      foreach ($xpath->evaluate('field', $node) as $fieldNode) {
        $key->fields[] = IndexFieldStructure::createFromXML($fieldNode);
      }
      return $key;
    }

    /**
     * @param \Papaya\XML\Element $parent
     */
    public function appendTo(Element $parent) {
      $node = $parent->appendElement(
        $this->isPrimary() ? 'primary-key' : 'key',
        $this->_fields
      );
      if (!$this->isPrimary()) {
        $node->setAttribute('name', $this->_name);
        if ($this->_isFullText) {
          $node->setAttribute('fulltext', 'yes');
        } elseif ($this->_isUnique) {
          $node->setAttribute('unique', 'yes');
        }
      }
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

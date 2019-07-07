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
/** @noinspection ALL */

namespace Papaya\Database\Schema\Structure {

  use Papaya\BaseObject\DeclaredProperties;
  use Papaya\BaseObject\Interfaces\Properties\Declared;
  use Papaya\XML\Appendable;
  use Papaya\XML\Element;

  /**
   * @property string $name
   * @property string $type
   * @property int|int[] $size
   * @property bool $allowsNull
   * @property bool $isAutoIncrement
   * @property string $defaultValue
   */
  class FieldStructure implements Declared, Appendable {

    use DeclaredProperties;

    const TYPE_TEXT = 'text';
    const TYPE_INTEGER = 'integer';
    const TYPE_DECIMAL = 'decimal';

    /**
     * size limit each field type [maximum, default] or TRUE
     *
     * @var array[]
     */
    private static $_TYPES = [
      self::TYPE_TEXT => [-1, 10000],
      self::TYPE_INTEGER => [8, 4],
      self::TYPE_DECIMAL => TRUE
    ];

    /**
     * @var array
     */
    private static $_TYPE_ALIASES = [
      'string' => self::TYPE_TEXT,
      'int' => self::TYPE_INTEGER,
      'float' => self::TYPE_DECIMAL
    ];

    /**
     * @var string
     */
    private $_name;
    /**
     * @var string
     */
    private $_type;
    /**
     * @var int
     */
    private $_size;
    private $_defaultValue = '';
    private $_allowsNull = FALSE;
    private $_isAutoIncrement = FALSE;

    /**
     * @param string $name
     * @param string $type
     * @param int|int[]|string $size
     */
    public function __construct(
      $name, $type, $size, $isAutoIncement = FALSE, $allowsNull = FALSE, $defaultValue = NULL
    ) {
      $this->setName($name);
      $this->setType($type);
      $this->setSize($size);
      $this->setIsAutoIncrement($isAutoIncement);
      $this->setAllowsNull($allowsNull);
      if (isset($defaultValue)) {
        $this->setDefaultValue($defaultValue);
      }
    }

    /**
     * @param \DOMElement $node
     * @return FieldStructure
     */
    public static function createFromXML(\DOMElement $node) {
      $field = new self(
        $node->getAttribute('name'),
        $node->getAttribute('type'),
        $node->getAttribute('size'),
        $node->getAttribute('autoinc') === 'yes' ||
        $node->getAttribute('auto-increment') === 'yes',
        $node->getAttribute('null') === 'yes' ||
        $node->getAttribute('allows-null') === 'yes',
        $node->getAttribute('default')
      );
      return $field;
    }

    /**
     * @param \Papaya\XML\Element $parent
     */
    public function appendTo(Element $parent) {
      $node = $parent->appendElement(
        'field',
        [
          'name' => $this->_name,
          'type' => $this->_type,
          'size' => is_array($this->_size) ? implode(',', $this->_size) : $this->_size
        ]
      );
      if ($this->_isAutoIncrement) {
        $node->setAttribute('auto-increment', 'yes');
      }
      if ($this->_allowsNull) {
        $node->setAttribute('allows-null', 'yes');
      }
      if (!empty($this->_defaultValue)) {
        $node->setAttribute('default', (string)$this->_defaultValue);
      }
    }

    /**
     * @param string $name
     */
    private function setName($name) {
      $name = trim($name);
      if ($name === '') {
        throw new \UnexpectedValueException('Field name can not be empty.');
      }
      $this->_name = $name;
    }

    /**
     * @param string $type
     */
    private function setType($type) {
      if (isset(self::$_TYPE_ALIASES[$type])) {
        $type = self::$_TYPE_ALIASES[$type];
      }
      if (!isset(self::$_TYPES[$type])) {
        throw new \UnexpectedValueException(sprintf('Invalid field type "%s".', $type));
      }
      $this->_type = $type;
      $this->setSize(0);
      $this->setDefaultValue('');
    }

    /**
     * @param int|int[]|string $size
     */
    private function setSize($size) {
      if ($this->_type === self::TYPE_DECIMAL) {
        $parts = is_array($size) ? $size : explode(',', (string)$size);
        $before = (isset($parts[0]) && $parts[0] > 0) ? (int)$parts[0] : 1;
        $after = (isset($parts[1])) ? (int)$parts[1] : 0;
        if ($after > $before) {
          $before += $after;
        }
        $this->_size = [$before, $after];
      } else {
        $size = (int)$size;
        list($maximum, $default) = self::$_TYPES[$this->_type];
        if ($size === 0) {
          $this->_size = $default;
        } elseif ($size > $maximum && $maximum >= 0) {
          $this->_size = $maximum;
        } else {
          $this->_size = $size;
        }
      }
    }

    /**
     * @param bool $isAutoIncrement
     */
    private function setIsAutoIncrement($isAutoIncrement) {
      if ($isAutoIncrement && $this->_type !== self::TYPE_INTEGER) {
        throw new \UnexpectedValueException('Only integer fields can be auto increment.');
      }
      if ($this->_isAutoIncrement = (bool)$isAutoIncrement) {
        $this->setAllowsNull(FALSE);
      }
    }

    /**
     * @param bool $allowsNull
     */
    private function setAllowsNull($allowsNull) {
      if ($allowsNull && $this->_isAutoIncrement) {
        throw new \UnexpectedValueException('Auto increment field can not be NULL.');
      }
      $this->_allowsNull = (bool)$allowsNull;
    }

    /**
     * @param string|int|float $value
     */
    private function setDefaultValue($value) {
      switch ($this->_type) {
      case self::TYPE_INTEGER :
        $this->_defaultValue = (int)$value;
      case self::TYPE_DECIMAL :
        $this->_defaultValue = (float)$value;
        if (is_nan($this->_defaultValue)) {
          $this->_defaultValue = 0;
        }
        break;
      default:
        $this->_defaultValue = (string)$value;
      }
    }

    /**
     * @return array
     */
    public static function getPropertyDeclaration() {
      return [
        'name' => ['_name'],
        'type' => ['_type'],
        'size' => ['_size'],
        'allowsNull' => ['_allowsNull', 'setAllowsNull'],
        'isAutoIncrement' => ['_isAutoIncrement', 'setIsAutoIncrement'],
        'defaultValue' => ['_defaultValue', 'setDefaultValue']
      ];
    }
  }
}

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

  /**
   * @property string $name
   * @property string $type
   * @property int|int[] $size
   * @property bool $allowsNull
   * @property bool $isAutoIncrement
   * @property string $defaultValue
   */
  class FieldStructure implements Declared {

    use DeclaredProperties;

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_DECIMAL = 'decimal';

    /**
     * size limit each field type [minimum, maximum, default] or TRUE
     *
     * @var array[]
     */
    private static $_TYPES = [
      self::TYPE_STRING => [1, -1, 10000],
      self::TYPE_INTEGER => [1, 8, 4],
      self::TYPE_DECIMAL => TRUE
    ];

    /**
     * @var array
     */
    private static $_TYPE_ALIASES = [
      'text' => self::TYPE_STRING,
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
     * @param int $size
     */
    public function __construct($name, $type, $size) {
      $this->setName($name);
      $this->setType($type);
      $this->setSize($size);
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
      );
      $field->setDefaultValue(
        $node->getAttribute('default')
      );
      $field->setAllowsNull(
        $node->getAttribute('null') === 'yes' ||
        $node->getAttribute('allows-null') === 'yes'
      );
      $field->setIsAutoIncrement(
        $node->getAttribute('autoinc') === 'yes' ||
        $node->getAttribute('auto-increment') === 'yes'
      );
      return $field;
    }

    /**
     * @param string $name
     */
    private function setName($name) {
      $name = trim($name);
      if ($name === '') {
        throw new \InvalidArgumentException('Field name can not be empty.');
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
        throw new \UnexpectedValueException(sprintf('Invalid field type "%s"', $type));
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
        list($before, $after) = is_array($size) ? $size : explode(',', (string)$size);
        $before = ($before > 0) ? (int)$before : 1;
        $after = (int)$after;
        if ($after > $before) {
          $before += $after;
        }
        $this->_size = [$before, $after];
      } else {
        $size = (int)$size;
        list($minimum, $maximum, $default) = self::$_TYPES[$this->_type];
        if ($size === 0) {
          $this->_size = $default;
        } elseif ($size < $minimum) {
          $this->_size = $minimum;
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
    public function setIsAutoIncrement($isAutoIncrement) {
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
    public function setAllowsNull($allowsNull) {
      if ($allowsNull && $this->_isAutoIncrement) {
        throw new \UnexpectedValueException('Auto increment field can not be NULL.');
      }
      $this->allowsNull = (bool)$allowsNull;
    }

    /**
     * @param string|int|float $value
     */
    public function setDefaultValue($value) {
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

    /**
     * @param string $name
     * @param null|FieldStructure $value
     * @return string
     */
    protected function prepareKey($name, $value = NULL) {
      if (isset($value) && $name === NULL) {
        $name = $value->name;
      }
      if (trim($name) === '') {
        throw new \InvalidArgumentException(
          sprintf('Invalid field name: "%s"', $name)
        );
      }
      return strtolower($name);
    }
  }
}

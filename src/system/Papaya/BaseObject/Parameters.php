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
namespace Papaya\BaseObject {

  use Papaya\Filter;
  use Papaya\Utility;

  /**
   * Extends the ArrayObject to allow lists as keys and a get with filtering and casting
   *
   * @package Papaya-Library
   * @subpackage Objects
   */
  class Parameters
    extends \ArrayObject {
    /**
     * Check if the value exists
     *
     * @param string|array $offset
     *
     * @return bool
     */
    public function has($offset): bool {
      return $this->offsetExists($offset);
    }

    /**
     * Get the value defined by the given offset. If the value does not exist or is NULL,
     * return the default value.
     *
     * If a filter is provided use it to filter the value before returning it.
     *
     * If the default value has a type, cast the value to this type. If the default value is an
     * object the result will be a string. Make sure the provided object implements \__toString().
     *
     * @param mixed $offset
     * @param mixed $defaultValue
     * @param Filter|NULL $filter
     *
     * @return mixed
     */
    public function get($offset, $defaultValue = NULL, Filter $filter = NULL) {
      $value = $this->getValueByOffset($offset);
      if (NULL !== $value && $filter instanceof Filter) {
        $value = $filter->filter($value);
      }
      if (NULL === $value) {
        return $defaultValue;
      }
      if (NULL === $defaultValue) {
        return $value;
      }
      if (\is_array($defaultValue)) {
        return \is_array($value) ? $value : $defaultValue;
      }
      if (\is_object($defaultValue) && \method_exists($defaultValue, '__toString')) {
        return \is_string($value) ? $value : (string)$defaultValue;
      }
      if (\is_scalar($defaultValue) && \is_scalar($value)) {
        $type = \gettype($defaultValue);
        \settype($value, $type);
        return $value;
      }
      return $defaultValue;
    }

    /**
     * Empty the internal array.
     */
    public function clear(): void {
      $this->exchangeArray([]);
    }

    /**
     * Merge the given array or Traversable into the internal array.
     *
     * @param iterable $value
     */
    public function merge(iterable $value): void {
      Utility\Constraints::assertArrayOrTraversable($value);
      $this->exchangeArray(Utility\Arrays::merge($this, $value));
    }

    /**
     * Set each value from an array or traversable
     *
     * @param array|\Traversable $values
     *
     * @internal param array|\Traversable $value
     */
    public function assign($values) {
      Utility\Constraints::assertArrayOrTraversable($values);
      foreach ($values as $key => $value) {
        $this[$key] = $value;
      }
    }

    /**
     * ArrayAccess interface, check if the offset exists
     *
     * @param mixed $offset
     *
     * @return bool
     * @see \ArrayObject::offsetExists()
     *
     */
    public function offsetExists($offset): bool {
      if (empty($offset) && 0 !== $offset) {
        return FALSE;
      }
      if (\is_array($offset) && \count($offset) > 0) {
        $first = \array_shift($offset);
        if (!parent::offsetExists($first)) {
          return FALSE;
        }
        $data = parent::offsetGet($first);
        foreach ($offset as $key) {
          if (\is_array($data) && \array_key_exists($key, $data)) {
            $data = &$data[$key];
          } else {
            return FALSE;
          }
        }
        return TRUE;
      }
      return parent::offsetExists($offset);
    }

    /**
     * ArrayAccess interface, return the value specified by the key, return NULL if the value
     * does not exist.
     *
     * @param mixed $offset
     *
     * @return mixed
     * @see \ArrayObject::offsetGet()
     *
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset) {
      return $this->getValueByOffset($offset);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    private function getValueByOffset($offset) {
      if (\is_array($offset) && \count($offset) > 0) {
        $first = \array_shift($offset);
        $data = parent::offsetExists($first) ? parent::offsetGet($first) : NULL;
        foreach ($offset as $key) {
          if (\is_array($data) && \array_key_exists($key, $data)) {
            $data = &$data[$key];
          } else {
            $data = NULL;
            break;
          }
        }
        return $data;
      }
      if (!\is_array($offset) && parent::offsetExists($offset)) {
        return parent::offsetGet($offset);
      }
      return NULL;
    }

    /**
     * ArrayAccess interface, change a value
     *
     * @param mixed $offset
     * @param mixed $value
     * @see \ArrayObject::offsetSet()
     *
     */
    public function offsetSet($offset, $value): void {
      if ($value instanceof \Traversable) {
        $value = \iterator_to_array($value);
      }
      if (\is_array($offset)) {
        if (\count($offset) > 1) {
          $first = \array_shift($offset);
          $last = \array_pop($offset);
          $top = parent::offsetExists($first) ? parent::offsetGet($first) : [];
          $current = &$top;
          foreach ($offset as $key) {
            if (empty($key) && 0 !== $key) {
              $current[] = [];
              \end($current);
              $key = \key($current);
            } elseif (!(isset($current[$key]) && \is_array($current[$key]))) {
              $current[$key] = [];
            }
            $current = &$current[$key];
          }
          if (empty($last) && 0 !== $last) {
            /* @noinspection NotOptimalIfConditionsInspection */
            if (!\is_array($current)) {
              $current = [];
            }
            $current[] = $value;
          } elseif (\is_array($current)) {
            $current[$last] = $value;
          } else {
            $current = [$last => $value];
          }
          parent::offsetSet(empty($first) && 0 !== $first ? NULL : $first, $top);
        } else {
          parent::offsetSet($offset[0], $value);
        }
      } else {
        parent::offsetSet($offset, $value);
      }
    }

    /**
     * ArrayAccess interface, delete a value from the internal array
     *
     * @param mixed $offset
     * @see \ArrayObject::offsetUnset()
     *
     */
    public function offsetUnset($offset): void {
      if (empty($offset) && 0 !== $offset) {
        return;
      }
      if (\is_array($offset)) {
        if (\count($offset) > 1) {
          $first = \array_shift($offset);
          $last = \array_pop($offset);
          if (!parent::offsetExists($first)) {
            return;
          }
          $top = parent::offsetGet($first);
          $current = &$top;
          foreach ($offset as $key) {
            if (isset($current[$key]) && \is_array($current[$key])) {
              $current = &$current[$key];
            } else {
              return;
            }
          }
          if (\is_array($current) && isset($current[$last])) {
            unset($current[$last]);
            parent::offsetSet($first, $top);
          }
        } else {
          $offset = $offset[0];
        }
      }
      if (\is_scalar($offset) && parent::offsetExists($offset)) {
        parent::offsetUnset($offset);
      }
    }

    /**
     * Compile a checksum hash for the parameter data
     *
     * @return string
     */
    public function getChecksum() {
      $data = (array)$this;
      \ksort($data);
      Utility\Arrays::normalize($data);
      return \md5(\serialize($data));
    }

    /**
     * Clones the current object and fills non-existing/NULL values
     *
     * @param array|\Traversable $defaults
     * @return self
     */
    public function withDefaults($defaults) {
      Utility\Constraints::assertArrayOrTraversable($defaults);
      $result = clone $this;
      foreach ($defaults as $default => $defaultValue) {
        if (!isset($result[$default])) {
          $result[$default] = $defaultValue;
        }
      }
      return $result;
    }
  }
}

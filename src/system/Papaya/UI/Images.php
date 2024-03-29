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
namespace Papaya\UI;

/**
 * Papaya Interface Images, and encapsulation for image lists (used in administration interfaces)
 *
 * An instance of this class is put in the application registry for administration interfaces.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Images implements \ArrayAccess, \IteratorAggregate {
  /**
   * Ignore duplicates (keep existing)
   *
   * @var int
   */
  const DUPLICATES_IGNORE = 0;

  /**
   * Overwrite duplicates (replace existing)
   *
   * @var int
   */
  const DUPLICATES_OVERWRITE = 1;

  /**
   * Internal image list
   *
   * @var array(string=>string)
   */
  private $_images = [];

  /**
   * Initialize object and add images if provided
   *
   * @param array $images
   */
  public function __construct(array $images = NULL) {
    if (NULL !== $images) {
      $this->add($images);
    }
  }

  /**
   * Add images to internal list
   *
   * @param array(string=>string) $images
   * @param int $mode
   */
  public function add(array $images, $mode = self::DUPLICATES_IGNORE) {
    foreach ($images as $id => $image) {
      if (!(self::DUPLICATES_IGNORE === $mode && isset($this->_images[$id]))) {
        $this->_images[$id] = $image;
      }
    }
  }

  /**
   * Remove images from internal list
   *
   * @param array(string) $images
   */
  public function remove(array $images) {
    foreach ($images as $id) {
      if (isset($this->_images[$id])) {
        unset($this->_images[$id]);
      }
    }
  }

  /**
   * ArrayAccess: validate if an image is available
   *
   * @param string $offset
   *
   * @return bool
   */
  public function offsetExists($offset): bool {
    return isset($this->_images[$offset]);
  }

  /**
   * ArrayAccess: get image
   *
   * @param string $offset
   *
   * @return mixed|string
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($offset) {
    return empty($this->_images[$offset]) ? $offset : $this->_images[$offset];
  }

  /**
   * ArrayAccess: set image
   *
   * @param string $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value): void {
    $this->add([$offset => $value], self::DUPLICATES_OVERWRITE);
  }

  /**
   * ArrayAccess: remove image from internal list
   *
   * @param string $offset
   */
  public function offsetUnset($offset): void {
    $this->remove([$offset]);
  }

  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->_images);
  }
}

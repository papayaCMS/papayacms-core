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
namespace Papaya\Profiler\Storage;

use Papaya\Profiler;

/**
 * Stores the Xhrof profiling data into a file usable by the standard report app.
 *
 * @package Papaya-Library
 * @subpackage Profiler
 */
class File implements Profiler\Storage {
  /**
   * @var string
   */
  private $_suffix = 'xhprof';

  /**
   * @var string
   */
  private $_directory;

  /**
   * Create storage object and store configuration options
   *
   * @param string $directory
   * @param string $suffix
   */
  public function __construct($directory, $suffix = NULL) {
    $this->_directory = $directory;
    if (NULL !== $suffix || '' !== \trim($suffix)) {
      $this->_suffix = $this->prepareSuffix($suffix);
    }
  }

  /**
   * Store xhprof profiling data into a file
   *
   * @param array $data
   * @param string $type
   *
   * @return string
   */
  public function saveRun($data, $type) {
    $id = $this->getId();
    $file = $this->getFilename($id, $type);
    \file_put_contents($file, \serialize($data));
    return $id;
  }

  /**
   * Compile filename for profiling data
   *
   * @param string $id
   * @param string $type
   *
   * @return string
   */
  private function getFilename($id, $type) {
    $file = $id.'.'.$type.'.'.$this->_suffix;
    return $this->prepareDirectory($this->_directory).'/'.$file;
  }

  /**
   * create id for profling run
   *
   * @return string
   */
  protected function getId() {
    return \uniqid('papaya', TRUE);
  }

  /**
   * Cleanup directory option and validate it.
   *
   * @param string $directory
   *
   * @throws \UnexpectedValueException
   *
   * @return string
   */
  private function prepareDirectory($directory) {
    if (empty($directory)) {
      throw new \UnexpectedValueException(
        'No profiling directory defined.'
      );
    }
    $directory = \Papaya\Utility\File\Path::cleanup($directory);
    if (\file_exists($directory) && \is_dir($directory) && \is_readable($directory)) {
      return $directory;
    }
    throw new \UnexpectedValueException(
      \sprintf('Profiling directory "%s" is not writeable.', $directory)
    );
  }

  /**
   * Validate profiling file extension.
   *
   * @param string $suffix
   *
   * @throws \UnexpectedValueException
   *
   * @return string
   */
  private function prepareSuffix($suffix) {
    if (\preg_match('(^[a-z\d]+$)D', $suffix)) {
      return $suffix;
    }
    throw new \UnexpectedValueException(
      \sprintf('Invalid profiling file suffix "%s"', $suffix)
    );
  }
}

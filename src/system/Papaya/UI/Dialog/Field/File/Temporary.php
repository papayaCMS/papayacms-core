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
namespace Papaya\UI\Dialog\Field\File;

use Papaya\Request;
use Papaya\UI;
use Papaya\XML;

/**
 * A file input that moves the uploaded file to the temp directory an returns the path
 * to the temporary file.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Temporary extends UI\Dialog\Field {
  /**
   * An input field is always an single line text input field.
   *
   * However here are variants and not all of them require special php logic. The
   * type is included in the xml so the xslt template can access it and add special handling like
   * css classes for defensive javascript.
   *
   * @var string
   */
  protected $_type = 'file';

  /**
   * @var Request\Parameter\File
   */
  private $_file;
  /**
   * @var array
   */
  private $_acceptedFileTypes = [];

  /**
   * Initialize object, set caption, field name and maximum length
   *
   * @param string|\Papaya\UI\Text $caption
   * @param string $name
   */
  public function __construct($caption, $name) {
    $this->setCaption($caption);
    $this->setName($name);
  }

  public function acceptFileTypes(...$types) {
    $this->_acceptedFileTypes = $types;
  }

  /**
   * Append field and input to DOM
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      [
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'accept' => empty($this->_acceptedFileTypes) ? null : implode(', ', $this->_acceptedFileTypes)
      ]
    );
  }

  /**
   * Fetch the file data and validate that here is an uploaded file
   *
   * @todo add some handling for upload errors
   *
   * return boolean
   */
  public function validate() {
    if (NULL !== $this->_validationResult) {
      return $this->_validationResult;
    }
    if ($this->file()->isValid()) {
      return $this->_validationResult = TRUE;
    }
    return $this->_validationResult = !$this->getMandatory();
  }

  /**
   * Here is no data that can be put into the dialog data directly.
   * Use {@see \Papaya\UI\Dialog\Field\File\Temporary::file()}
   *
   * return TRUE
   */
  public function collect() {
    return TRUE;
  }

  /**
   * Getter/Setter for the file values subobject. It encapsulates the data from the $_FILES
   * super global array
   *
   * @param Request\Parameter\File $file
   *
   * @return Request\Parameter\File
   */
  public function file(Request\Parameter\File $file = NULL) {
    if (NULL !== $file) {
      $this->_file = $file;
    } elseif (NULL === $this->_file) {
      $this->_file = new Request\Parameter\File(
        $this->_getParameterName($this->getName())
      );
    }
    return $this->_file;
  }
}

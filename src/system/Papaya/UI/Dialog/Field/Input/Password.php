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
namespace Papaya\UI\Dialog\Field\Input;

use Papaya\Filter;
use Papaya\UI;

/**
 * A single line input for password - the characters are not shown and the value is never read from
 * data() - only from parameters
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Password extends UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'password';

  /**
   * Initialize object, set caption, field name and maximum length
   *
   * @param string|\Papaya\UI\Text $caption
   * @param string $name
   * @param int $length
   * @param Filter|null $filter
   *
   * @internal param mixed $default
   */
  public function __construct($caption, $name, $length = 1024, Filter $filter = NULL) {
    parent::__construct(
      $caption, $name, $length, NULL, $filter ?: new Filter\Password()
    );
  }

  /**
   * Get the current field value.
   *
   * If the dialog object has a matching parameter it is used. Unlike the other input fields
   * data is ignored to avoid displaying stored passwords. the default value will be ignored, too.
   *
   * If neither dialog parameter or data is available, the default value is returned.
   *
   * @return mixed
   */
  public function getCurrentValue() {
    $name = $this->getName();
    if (
      '' !== \trim($name) &&
      !$this->getDisabled() &&
      $this->hasCollection() &&
      $this->collection()->hasOwner() &&
      $this->collection()->owner()->parameters()->has($name)
    ) {
      return $this->collection()->owner()->parameters()->get($name);
    }
    return NULL;
  }
}

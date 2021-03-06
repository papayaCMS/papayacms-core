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
use Papaya\Utility;
use Papaya\XML;

/**
 * An image captcha input field
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Captcha extends UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'captcha';

  /**
   * String name/identifier of the dynamic image
   *
   * @var string
   */
  private $_captchaImage;

  /**
   * Buffer for the captcha status
   *
   * @var bool|null
   */
  private $_isCaptchaValid;

  /**
   * Creates dialog field for
   *
   * @param string $caption
   * @param string $name
   * @param string $captchaImage
   *
   * @internal param string $dynamicImage dynamic image identifier
   */
  public function __construct($caption, $name, $captchaImage = 'captcha') {
    parent::__construct($caption, $name, 1024, NULL);
    $this->setMandatory(TRUE);
    Utility\Constraints::assertNotEmpty($captchaImage);
    $this->_captchaImage = $captchaImage;
    $this->setFilter(new Filter\Equals(TRUE));
  }

  /**
   * Getter for the dynamic image - captcha image generator identifer
   *
   * @return string
   */
  public function getCaptchaImage() {
    return $this->_captchaImage;
  }

  /**
   * Append the captcha field xml to the dom
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $identifier = $this->createCaptchaIdentifier();
    $field->appendElement(
      'input',
      [
        'type' => $this->getType(),
        'name' => $this->_getParameterName($this->getName().'['.$identifier.']', TRUE),
      ]
    );
    $reference = new UI\Reference(clone $this->papaya()->request->getURL());
    $reference->setRelative($this->_captchaImage.'.image.jpg');
    $reference->setParameters(['img' => ['identifier' => $identifier]]);
    $field->appendElement('image', ['src' => $reference->getRelative()]);
    return $field;
  }

  /**
   * Fetch the current value
   *
   * @return bool
   */
  public function getCurrentValue() {
    if ($this->hasCollection() && $this->collection()->hasOwner()) {
      return $this->validateCaptcha();
    }
    return TRUE;
  }

  /**
   * Validate the captcha against the request parameter value (user input), invalidate
   * the captcha if it matches. Store the value in an internal member variable, because
   * of the invalidation, the validation can not be repeated.
   *
   * @return bool
   */
  private function validateCaptcha() {
    if (NULL === $this->_isCaptchaValid) {
      $token = $this->collection()->owner()->parameters()->get(
        $this->getName(), []
      );
      if (!empty($token)) {
        $value = \reset($token);
        $identifier = \key($token);
        $captcha = $this->papaya()->session->getValue('PAPAYA_SESS_CAPTCHA');
        if (isset($captcha[$identifier]) && ($captcha[$identifier] === $value)) {
          unset($captcha[$identifier]);
          $this->papaya()->session->setValue('PAPAYA_SESS_CAPTCHA', $captcha);
          return $this->_isCaptchaValid = TRUE;
        }
      }
      return $this->_isCaptchaValid = FALSE;
    }
    return $this->_isCaptchaValid;
  }

  /**
   * Create a random identifier to use as an name for the actual captcha value generated by the
   * dynamic image module.
   *
   * @return string
   */
  public function createCaptchaIdentifier() {
    return \md5(Utility\Random::getId());
  }
}

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
namespace Papaya\Theme;

use Papaya\Content;
use Papaya\Utility;
use Papaya\XML;

/**
 * Load and provide access to the theme definition stored in theme.xml inside the theme directory.
 *
 * @package Papaya-Library
 * @subpackage Theme
 *
 * @property string $name
 * @property string $title
 * @property string $version
 * @property string $versionDate
 * @property string $author
 * @property string $description
 * @property string $templatePath
 * @property array('medium' => string, 'large' => string) $thumbnails
 */
class Definition extends Content\Structure {
  /**
   * Theme data
   *
   * @var array
   */
  private $_properties = [
    'name' => '',
    'title' => '',
    'version' => '',
    'version_date' => '',
    'author' => '',
    'description' => '',
    'template_path' => ''
  ];

  /**
   * Theme thunbnails
   *
   * @var array
   */
  private $_thumbnails = [
    'medium' => '',
    'large' => ''
  ];

  /**
   * Load theme data from an xml file
   *
   * @param string $location
   */
  public function load($location) {
    $dom = new XML\Document();
    $dom->load($location);
    $xpath = $dom->xpath();
    $this->_properties['name'] = \basename(\dirname($location));
    $this->_properties['title'] = $xpath->evaluate('string(/papaya-theme/name)');
    $this->_properties['version'] = $xpath->evaluate('string(/papaya-theme/version/@number)');
    $this->_properties['version_date'] = $xpath->evaluate('string(/papaya-theme/version/@date)');
    $this->_properties['author'] = $xpath->evaluate('string(/papaya-theme/author)');
    $this->_properties['description'] = $xpath->evaluate('string(/papaya-theme/description)');
    $this->_properties['template_path'] = $xpath->evaluate(
      'string(/papaya-theme/templates/@directory)'
    );
    /** @var \Papaya\XML\Element $thumbNode */
    foreach ($xpath->evaluate('/papaya-theme/thumbs/thumb') as $thumbNode) {
      $size = $thumbNode->getAttribute('size');
      if (isset($this->_thumbnails[$size])) {
        $this->_thumbnails[$size] = $thumbNode->getAttribute('src');
      }
    }
    if ($xpath->evaluate('count(/papaya-theme/dynamic-values)') > 0) {
      parent::load($xpath->evaluate('/papaya-theme/dynamic-values')->item(0));
    }
  }

  public function __isset($name) {
    $identifier = Utility\Text\Identifier::toUnderscoreLower($name);
    return ('thumbnails' === $identifier || isset($this->_properties[$identifier]));
  }

  /**
   * Get a theme property
   *
   * @param string $name
   *
   * @throws \UnexpectedValueException
   *
   * @return array
   */
  public function __get($name) {
    $identifier = Utility\Text\Identifier::toUnderscoreLower($name);
    if (isset($this->_properties[$identifier])) {
      return $this->_properties[$identifier];
    }
    if ('thumbnails' === $identifier) {
      return $this->_thumbnails;
    }
    throw new \UnexpectedValueException(
      \sprintf(
        'Can not read unknown property "%s::$%s".',
        \get_class($this),
        $name
      )
    );
  }

  /**
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value) {
    throw new \UnexpectedValueException(
      \sprintf(
        'Can not write property "%s::$%s".',
        \get_class($this),
        $name
      )
    );
  }

  /**
   * @param string $name
   */
  public function __unset($name) {
    throw new \UnexpectedValueException(
      \sprintf(
        'Can not unset property "%s::$%s".',
        \get_class($this),
        $name
      )
    );
  }
}

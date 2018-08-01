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

namespace Papaya\Cache\Identifier\Definition;
/**
 * Use page and category ids as cache identifier conditions
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Page
  extends \Papaya\Application\BaseObject
  implements \Papaya\Cache\Identifier\Definition {

  /**
   * Return data for the specified page
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   * @return BooleanValue|array
   */
  public function getStatus() {
    $isPreview = $this->papaya()->request->getParameter(
      'preview', FALSE, NULL, \Papaya\Request::SOURCE_PATH
    );
    if ($isPreview) {
      return FALSE;
    }
    $data = array(
      'scheme' => \Papaya\Utility\Server\Protocol::get(),
      'host' => \Papaya\Utility\Server\Name::get(),
      'port' => \Papaya\Utility\Server\Port::get(),
      'category_id' => $this->papaya()->request->getParameter(
        'category_id', 0, NULL, \Papaya\Request::SOURCE_PATH
      ),
      'page_id' => $this->papaya()->request->getParameter(
        'page_id', 0, NULL, \Papaya\Request::SOURCE_PATH
      ),
      'language' => $this->papaya()->request->getParameter(
        'language', '', NULL, \Papaya\Request::SOURCE_PATH
      ),
      'output_mode' => $this->papaya()->request->getParameter(
        'output_mode',
        $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html'),
        NULL,
        \Papaya\Request::SOURCE_PATH
      )
    );
    return empty($data) ? TRUE : array(get_class($this) => $data);
  }

  /**
   * page id and category id are from the url path.
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return integer
   */
  public function getSources() {
    return self::SOURCE_URL;
  }
}

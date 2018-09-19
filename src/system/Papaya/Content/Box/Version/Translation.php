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
namespace Papaya\Content\Box\Version;

/**
 * Provide data encapsulation for a single content box version translation details.
 *
 * Allows to load/save the box translation.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $boxId
 * @property int $languageId
 * @property string $title
 * @property array $content
 * @property-read int $created
 * @property-read int $modified
 * @property int $viewId
 * @property-read string $viewTitle
 * @property-read string $moduleGuid
 * @property-read string $moduleTitle
 */
class Translation extends \Papaya\Content\Box\Translation {
  protected $_tableNameBoxTranslations = \Papaya\Content\Tables::BOX_VERSION_TRANSLATIONS;
}

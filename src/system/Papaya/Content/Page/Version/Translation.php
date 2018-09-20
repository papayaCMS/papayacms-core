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
namespace Papaya\Content\Page\Version;

use Papaya\Content;

/**
 * Provide data encapsulation for a single content page version translation details.
 *
 * Allows to load/save the page translation.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $pageId
 * @property int $languageId
 * @property string $title
 * @property array $content
 * @property-read int $created
 * @property-read int $modified
 * @property string $metaTitle
 * @property string $metaKeywords
 * @property string $metaDescription
 * @property int $viewId
 * @property-read string $viewTitle
 * @property-read string $moduleGuid
 * @property-read string $moduleTitle
 */
class Translation extends Content\Page\Translation {
  protected $_tableName = Content\Tables::PAGE_VERSION_TRANSLATIONS;
}

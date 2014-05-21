<?php
/**
* Provide data encapsulation for a single content page translation details.
*
* Allows to load/save the page translation.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Content
* @version $Id: Translation.php 38477 2013-05-07 14:27:34Z weinert $
*/

/**
* Provide data encapsulation for the content page translation details.
*
* Allows to load/save the page translation.
*
* @package Papaya-Library
* @subpackage Content
*
* @property integer $pageId
* @property integer $languageId
* @property string $title
* @property array $content
* @property-read integer $created
* @property-read integer $modified
* @property string $metaTitle
* @property string $metaKeywords
* @property string $metaDescription
* @property integer $viewId
* @property-read string $viewTitle
* @property-read string $moduleGuid
* @property-read string $moduleTitle
*/
class PapayaContentPagePublicationTranslation extends PapayaContentPageTranslation {

  protected $_tableNamePageTranslations = PapayaContentTables::PAGE_PUBLICATION_TRANSLATIONS;
}

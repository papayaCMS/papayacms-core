<?php
/**
* Edit theme sets (dynamic values for a theme)
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Administration
* @version $Id: Editor.php 37235 2012-07-17 14:21:44Z weinert $
*/

/**
* Edit theme sets (dynamic values for a theme)
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationThemeEditor extends PapayaAdministrationPage {

  protected $_parameterGroup = 'theme';

  protected function createContent() {
    return new PapayaAdministrationThemeEditorChanges();
  }

  protected function createNavigation() {
    return new PapayaAdministrationThemeEditorNavigation();
  }
}
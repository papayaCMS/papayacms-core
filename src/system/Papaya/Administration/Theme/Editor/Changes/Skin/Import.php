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

namespace Papaya\Administration\Theme\Editor\Changes\Skin;

use Papaya\Content;
use Papaya\Theme;
use Papaya\UI;
use Papaya\XML;

/**
 * Import theme skin values from an uploaded file
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Import
  extends UI\Control\Command\Dialog {
  /**
   * @var Content\Theme\Skin
   */
  private $_themeSet;

  /**
   * @var Theme\Handler
   */
  private $_themeHandler;

  public function __construct(Content\Theme\Skin $themeSet, Theme\Handler $themeHandler) {
    $this->_themeSet = $themeSet;
    $this->_themeHandler = $themeHandler;
  }

  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see \Papaya\UI\Control\Command\Dialog::createDialog()
   * @return \Papaya\UI\Dialog
   */
  public function createDialog() {
    $skinId = $this->parameters()->get('skin_id', 0);
    $dialog = parent::createDialog();
    $dialog->caption = new UI\Text\Translated('Import');
    $dialog->setEncoding('multipart/form-data');
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      [
        'cmd' => 'skin_import',
        'theme' => $this->parameters()->get('theme', ''),
        'skin_id' => $skinId
      ]
    );
    $dialog->fields[] = $uploadField = new UI\Dialog\Field\File\Temporary(
      new UI\Text\Translated('File'), 'values/file'
    );
    $uploadField->setMandatory(TRUE);
    if ($skinId > 0) {
      $dialog->fields[] = $field = new UI\Dialog\Field\Select\Radio(
        new UI\Text\Translated('Replace current skin.'),
        'values/confirm_replace',
        [
          TRUE => new UI\Text\Translated('Yes'),
          FALSE => new UI\Text\Translated('No')
        ]
      );
      $field->setDefaultValue(FALSE);
    }
    $dialog->buttons[] = new UI\Dialog\Button\Submit(
      new UI\Text\Translated('Upload')
    );
    $this->callbacks()->onExecuteSuccessful = function() use ($uploadField) {
      return $this->onValidationSuccess($uploadField);
    };
    return $dialog;
  }

  /**
   * @param UI\Dialog\Field\File\Temporary $uploadField
   * @return bool
   */
  public function onValidationSuccess(UI\Dialog\Field\File\Temporary $uploadField) {
    $theme = $this->parameters()->get('theme', '');
    if (!empty($theme)) {
      $file = $uploadField->file();
      $errors = new XML\Errors();
      return $errors->encapsulate(
        function() use ($file, $theme) {
          $dom = new XML\Document();
          $dom->load($file['temporary']);
          if ($dom->documentElement) {
            /** @var \Papaya\XML\Element $documentElement */
            $documentElement = $dom->documentElement;
            $skinId = $this->parameters()->get('skin_id', 0);
            if ($skinId > 0 && $this->parameters()->get('values/confirm_replace')) {
              if ($this->_themeSet->load($skinId)) {
                $this->_themeSet->setValuesXML(
                  $this->_themeHandler->getDefinition($theme),
                  $documentElement
                );
              }
            } else {
              $this->_themeSet->assign(
                [
                  'title' => new UI\Text\Translated('* Imported Set'),
                  'theme' => $theme
                ]
              );
              $this->_themeSet->setValuesXML(
                $this->_themeHandler->getDefinition($theme),
                $documentElement
              );
            }
            if ($this->_themeSet->save()) {
              $this->papaya()->messages->displayInfo('Values imported.');
              return TRUE;
            }
          }
          return FALSE;
        },
        NULL,
        FALSE
      );
    }
    return FALSE;
  }
}

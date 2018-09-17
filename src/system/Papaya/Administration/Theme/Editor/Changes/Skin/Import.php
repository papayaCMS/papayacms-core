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

/**
 * Import theme skin values from an uploaded file
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Import
  extends \Papaya\UI\Control\Command\Dialog {

  /**
   * @var \Papaya\Content\Theme\Skin
   */
  private $_themeSet;
  /**
   * @var \Papaya\Theme\Handler
   */
  private $_themeHandler;

  public function __construct(\Papaya\Content\Theme\Skin $themeSet, \Papaya\Theme\Handler $themeHandler) {
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
    $dialog->caption = new \Papaya\UI\Text\Translated('Import');
    $dialog->setEncoding('multipart/form-data');
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      array(
        'cmd' => 'skin_import',
        'theme' => $this->parameters()->get('theme', ''),
        'skin_id' => $skinId
      )
    );
    $dialog->fields[] = $uploadField = new \Papaya\UI\Dialog\Field\File\Temporary(
      new \Papaya\UI\Text\Translated('File'), 'values/file'
    );
    $uploadField->setMandatory(TRUE);
    if ($skinId > 0) {
      $dialog->fields[] = $field = new \Papaya\UI\Dialog\Field\Select\Radio(
        new \Papaya\UI\Text\Translated('Replace current skin.'),
        'values/confirm_replace',
        array(
          TRUE => new \Papaya\UI\Text\Translated('Yes'),
          FALSE => new \Papaya\UI\Text\Translated('No')
        )
      );
      $field->setDefaultValue(FALSE);
    }
    $dialog->buttons[] = new \Papaya\UI\Dialog\Button\Submit(
      new \Papaya\UI\Text\Translated('Upload')
    );
    $this->callbacks()->onExecuteSuccessful = array($this, 'onValidationSuccess');
    $this->callbacks()->onExecuteSuccessful->context = $uploadField;
    return $dialog;
  }

  /**
   * @param \Papaya\UI\Dialog\Field\File\Temporary $uploadField
   * @return bool
   * @throws \Papaya\XML\Exception
   */
  public function onValidationSuccess(\Papaya\UI\Dialog\Field\File\Temporary $uploadField) {
    $theme = $this->parameters()->get('theme', '');
    if (!empty($theme)) {
      $file = $uploadField->file();
      $errors = new \Papaya\XML\Errors();
      try {
        $errors->activate();
        $dom = new \Papaya\XML\Document();
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
              array(
                'title' => new \Papaya\UI\Text\Translated('* Imported Set'),
                'theme' => $theme
              )
            );
            $this->_themeSet->setValuesXML(
              $this->_themeHandler->getDefinition($theme),
              $documentElement
            );
          }
          if ($this->_themeSet->save()) {
            $this->papaya()->messages->dispatch(
              new \Papaya\Message\Display\Translated(
                \Papaya\Message::SEVERITY_INFO,
                'Values imported.'
              )
            );
            return TRUE;
          }
        }
        //@codeCoverageIgnoreStart
      } catch (\Papaya\XML\Exception $e) {
        $errors->emit();
      }
      //@codeCoverageIgnoreEnd
      $errors->deactivate();
    }
    return FALSE;
  }
}

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
namespace Papaya\CMS\Administration\Theme\Editor\Changes\Skin;

use Papaya\UI;

/**
 * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Change
  extends UI\Control\Command\Dialog\Database\Record {
  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see \Papaya\UI\Control\Command\Dialog::createDialog()
   *
   * @return \Papaya\UI\Dialog
   */
  public function createDialog() {
    $skinId = $this->parameters()->get('skin_id', 0);
    $dialogCaption = 'Add theme skin';
    $buttonCaption = 'Add';
    if ($skinId > 0) {
      if ($this->record()->load($skinId)) {
        $dialogCaption = 'Change theme skin';
        $buttonCaption = 'Save';
      } else {
        $skinId = 0;
      }
    }
    $dialog = new UI\Dialog\Database\Save($this->record());
    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      [
        'cmd' => 'skin_edit',
        'theme' => $this->parameters()->get('theme', ''),
        'skin_id' => $skinId
      ]
    );
    $dialog->caption = new UI\Text\Translated($dialogCaption);
    $dialog->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('Title'), 'title', 200, '', new \Papaya\Filter\Text()
    );
    $field->setMandatory(TRUE);
    $dialog->buttons[] = new UI\Dialog\Button\Submit(
      new UI\Text\Translated($buttonCaption)
    );
    $this->callbacks()->onExecuteSuccessful = function() {
      $this->papaya()->messages->displayInfo('Theme skin saved.');
    };
    $this->callbacks()->onExecuteFailed = function(
      /* @noinspection PhpUnusedParameterInspection */
      $context, UI\Dialog $dialog
    ) {
      $this->papaya()->messages->displayError(
        'Invalid input. Please check the field(s) "%s".',
        [\implode(', ', $dialog->errors()->getSourceCaptions())]
      );
    };
    return $dialog;
  }
}

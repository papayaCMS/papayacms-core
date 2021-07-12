<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Administration\LinkTypes\Editor\Commands;

use Papaya\Filter\LogicalOr;
use Papaya\Filter\Number;
use Papaya\Filter\NumberWithUnit;
use Papaya\UI;
use Papaya\UI\Dialog\Field\Select\Radio as RadioGroupField;
use Papaya\UI\Dialog\Field\Select as SelectField;
use Papaya\UI\Text\Translated as TranslatedText;
use Papaya\UI\Text\Translated\Collection as TranslatedList;

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
    $linkTypeId = $this->parameters()->get('id', 0);
    $dialogCaption = 'Add Link Type';
    $buttonCaption = 'Add';
    if ($linkTypeId > 0) {
      if ($this->record()->load($linkTypeId)) {
        $dialogCaption = 'Edit Link Type';
        $buttonCaption = 'Save';
      } else {
        $linkTypeId = 0;
      }
    }
    $dialog = new UI\Dialog\Database\Save($this->record());
    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      [
        'cmd' => 'edit',
        'id' => $linkTypeId
      ]
    );
    $dialog->caption = new UI\Text\Translated($dialogCaption);
    $dialog->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('Name'), 'name', 200, '', new \Papaya\Filter\Text()
    );
    $dialog->fields[] = $field = new RadioGroupField(
      new TranslatedText('Visible'),
      'is_visible',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No'])
    );
    $dialog->fields[] = $field = $field = new SelectField(
      new TranslatedText('Link Target'),
      'target',
       ['_self' => '_self', '_blank' => '_blank', '_parent' => '_parent']
    );
    $field->setDefaultValue('_self');
    $dialog->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('CSS class'), 'class', 200, '', new \Papaya\Filter\Text()
    );
    $dialog->fields[] = $field = new RadioGroupField(
      new TranslatedText('Popup'),
      'is_popup',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No']
      )
    );
    $dialog->fields[] = $group = new UI\Dialog\Field\Group(
      new TranslatedText('Popup Size And Position')
    );
    $filter = new LogicalOr(new Number(), new NumberWithUnit('%'));
    $group->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('Width'), 'popup_options/width', 10, '', $filter
    );
    $group->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('Height'), 'popup_options/height', 10, '', $filter
    );
    $group->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('Top'), 'popup_options/top', 10, '', $filter
    );
    $group->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('Left'), 'popup_options/left', 10, '', $filter
    );
    $group->fields[] = $field = new RadioGroupField(
      new TranslatedText('Scalable'),
      'popup_options/scalable',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No']
      )
    );
    $dialog->fields[] = $group = new UI\Dialog\Field\Group(
      new TranslatedText('Popup Bar Visibility')
    );
    $group->fields[] = $field = new RadioGroupField(
      new TranslatedText('Location Bar'),
      'popup_options/location',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No']
      )
    );
    $group->fields[] = $field = new RadioGroupField(
      new TranslatedText('Menu Bar'),
      'popup_options/menubar',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No']
      )
    );
    $group->fields[] = $field = new RadioGroupField(
      new TranslatedText('Scrollbars'),
      'popup_options/scrollbars',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No']
      )
    );
    $group->fields[] = $field = new RadioGroupField(
      new TranslatedText('Status Bar'),
      'popup_options/status',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No']
      )
    );
    $group->fields[] = $field = new RadioGroupField(
      new TranslatedText('Toolbar'),
      'popup_options/toolbar',
      new TranslatedList([TRUE => 'Yes', FALSE => 'No']
      )
    );
    $dialog->buttons[] = new UI\Dialog\Button\Submit(
      new UI\Text\Translated($buttonCaption)
    );
    $this->callbacks()->onExecuteSuccessful = function() {
      $this->papaya()->messages->displayInfo('Link type saved.');
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

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

use Papaya\UI;

/**
 * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Remove
  extends UI\Control\Command\Dialog\Database\Record {
  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see \Papaya\UI\Control\Command\Dialog::createDialog()
   * @return \Papaya\UI\Dialog
   */
  public function createDialog() {
    $skinId = $this->parameters()->get('skin_id', 0);
    if ($skinId > 0) {
      $loaded = $this->record()->load($skinId);
    } else {
      $loaded = FALSE;
    }
    $dialog = new UI\Dialog\Database\Delete($this->record());
    $dialog->papaya($this->papaya());
    $dialog->caption = new UI\Text\Translated('Delete theme skin');
    if ($loaded) {
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        [
          'cmd' => 'skin_delete',
          'theme' => $this->parameters()->get('theme', ''),
          'skin_id' => $skinId
        ]
      );
      $dialog->fields[] = new UI\Dialog\Field\Information(
        new UI\Text\Translated('Delete theme skin'),
        'places-trash'
      );
      $dialog->buttons[] = new UI\Dialog\Button\Submit(new UI\Text\Translated('Delete'));
      $this->callbacks()->onExecuteSuccessful = function() {
        $this->papaya()->messages->displayInfo('Theme skin deleted.');
      };
    } else {
      $dialog->fields[] = new UI\Dialog\Field\Message(
        UI\Dialog\Field\Message::SEVERITY_INFO, 'Theme skin not found.'
      );
    }
    return $dialog;
  }
}

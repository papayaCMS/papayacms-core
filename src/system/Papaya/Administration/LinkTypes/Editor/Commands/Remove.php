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
namespace Papaya\Administration\LinkTypes\Editor\Commands {

  use Papaya\UI;
  use Papaya\UI\Dialog;

  /**
   * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
   *
   * @package Papaya-Library
   * @subpackage Administration
   */
  class Remove
    extends UI\Control\Command\Dialog\Database\Record {

    /**
     * @return Dialog
     */
    public function createDialog() {
      $linkTypeId = $this->parameters()->get('id', 0);
      if ($linkTypeId > 0) {
        $loaded = $this->record()->load($linkTypeId);
      } else {
        $loaded = FALSE;
      }
      $dialog = new UI\Dialog\Database\Delete($this->record());
      $dialog->papaya($this->papaya());
      $dialog->caption = new UI\Text\Translated('Delete');
      if ($loaded) {
        $dialog->parameterGroup($this->parameterGroup());
        $dialog->parameters($this->parameters());
        $dialog->hiddenFields()->merge(
          [
            'cmd' => 'delete',
            'id' => $linkTypeId
          ]
        );
        $dialog->fields[] = new UI\Dialog\Field\Information(
          new UI\Text\Translated('Delete link type: %s #%d', [$this->record()['name'], $this->record()['id']]),
          'places-trash'
        );
        $dialog->buttons[] = new UI\Dialog\Button\Submit(new UI\Text\Translated('Delete'));
        $this->callbacks()->onExecuteSuccessful = function () {
          $this->papaya()->messages->displayInfo('Link type deleted.');
        };
      } else {
        $dialog->fields[] = new UI\Dialog\Field\Message(
          UI\Dialog\Field\Message::SEVERITY_INFO, 'Link type not found.'
        );
      }
      return $dialog;
    }
  }
}

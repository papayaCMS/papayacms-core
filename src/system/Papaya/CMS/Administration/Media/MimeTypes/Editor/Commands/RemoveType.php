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
namespace Papaya\CMS\Administration\Media\MimeTypes\Editor\Commands {

  use Papaya\CMS\Content\Media\MimeType;
  use Papaya\UI;
  use Papaya\UI\Dialog;

  /**
   * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
   *
   * @package Papaya-Library
   * @subpackage Administration
   */
  class RemoveType
    extends UI\Control\Command\Dialog\Database\Record {

    /**
     * @return Dialog
     */
    public function createDialog() {
      $typeId = $this->parameters()->get('type_id', 0);
      /** @var MimeType $mimeType */
      $mimeType = $this->record();
      if ($typeId > 0) {
        $loaded = $mimeType->load($typeId);
      } else {
        $loaded = FALSE;
      }
      $dialog = new UI\Dialog\Database\Delete($this->record());
      $dialog->papaya($this->papaya());
      $dialog->caption = new UI\Text\Translated('Delete');
      if ($loaded) {
        $groupId = $mimeType->groupId;
        $dialog->parameterGroup($this->parameterGroup());
        $dialog->parameters($this->parameters());
        $dialog->hiddenFields()->merge(
          [
            'cmd' => 'type_delete',
            'group_id' => $groupId,
            'type_id' => $typeId
          ]
        );
        $dialog->fields[] = new UI\Dialog\Field\Information(
          new UI\Text\Translated('Delete mime type: %s #%d', [$mimeType->type, $mimeType->id]),
          'places-trash'
        );
        $dialog->buttons[] = new UI\Dialog\Button\Submit(new UI\Text\Translated('Delete'));
        $this->callbacks()->onExecuteSuccessful = function () {
          $this->papaya()->messages->displayInfo('Mime type deleted.');
        };
      } else {
        $dialog->fields[] = new UI\Dialog\Field\Message(
          UI\Dialog\Field\Message::SEVERITY_INFO, 'Mime type not found.'
        );
      }
      return $dialog;
    }
  }
}

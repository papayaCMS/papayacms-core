<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Administration\Protocol\Commands {

  use Papaya\Administration\Protocol\ProtocolContent;
  use Papaya\Administration\Protocol\ProtocolPage;
  use Papaya\Content\Protocol\ProtocolEntries;
  use Papaya\Content\Protocol\ProtocolEntry;
  use Papaya\Content\Protocol\ProtocolGroups;
  use Papaya\Database\Condition\Root;
  use Papaya\UI\Control\Command\Dialog as DialogCommand;
  use Papaya\UI\Dialog\Button\Submit as SubmitButton;
  use Papaya\UI\Dialog\Field\Group;
  use Papaya\UI\Dialog\Field\Information as InformationField;
  use Papaya\UI\Dialog\Field\Input\Readonly as ReadonlyInputField;
  use Papaya\UI\Dialog\Field\ListView as ListViewField;
  use Papaya\UI\ListView;
  use Papaya\UI\Text\Date as DateText;
  use Papaya\UI\Text\Translated as TranslatedText;

  class DeleteProtocolEntries extends DialogCommand {

    /**
     * @var ProtocolEntry
     */
    private $_protocolEntry;
    /**
     * @var ProtocolEntries
     */
    private $_protocolEntries;

    public function __construct(ProtocolEntry $protocolEntry) {
      $this->_protocolEntry = $protocolEntry;
    }

    public function createDialog() {
      $dialog = parent::createDialog();
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->caption = new TranslatedText('Cleanup Protocol');
      $groupId = $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, 0);
      $severity = $this->parameters()->get(ProtocolPage::PARAMETER_NAME_SEVERITY, -1);
      $createdBefore = NULL;
      $dialog->hiddenFields->merge(
        [
          ProtocolPage::PARAMETER_NAME_COMMAND => ProtocolContent::COMMAND_CLEANUP,
          ProtocolPage::PARAMETER_NAME_SEVERITY => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_SEVERITY, -1),
          ProtocolPage::PARAMETER_NAME_GROUP => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, 0),
          ProtocolPage::PARAMETER_NAME_PAGE => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_PAGE, 1),
          ProtocolPage::PARAMETER_NAME_ENTRY => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_ENTRY, 0)
        ]
      );
      if ($this->_protocolEntry->isLoaded()) {
        $createdBefore = $this->_protocolEntry->createdAt;
        $dialog->fields[] = new InformationField(
          new TranslatedText(
            'Delete protocol entries before %s.',
            [
              new DateText($this->_protocolEntry->createdAt)
            ]
          )
        );
      } else {
        $dialog->fields[] = new InformationField(
          new TranslatedText(
            'Delete all protocol entries.'
          )
        );
      }
      if ($groupId > 0 || $severity >= 0) {
        $dialog->fields[] = $group = new Group(new TranslatedText('Filter'));
        $group->fields[] = new ListViewField(
          $listView = new ListView()
        );
        if ($severityLabel = ProtocolEntry::getSeverityLabel($severity)) {
          $listView->items[] = $item = new ListView\Item(
            '', new TranslatedText('Severity')
          );
          $item->subitems[] = new ListView\SubItem\Text(
            new TranslatedText($severityLabel)
          );
        }
        if ($groupLabel = ProtocolGroups::getLabel($groupId)) {
          $listView->items[] = $item = new ListView\Item(
            '', new TranslatedText('Group')
          );
          $item->subitems[] = new ListView\SubItem\Text(
            new TranslatedText($groupLabel)
          );
        }
      }
      $dialog->buttons[] = new SubmitButton(new TranslatedText('Delete'));

      $this->hideAfterSuccess(TRUE);
      $this->callbacks()->onExecuteSuccessful = function() use ($severity, $groupId, $createdBefore) {
        if ($this->deleteEntries($severity, $groupId, $createdBefore)) {
          $this->papaya()->messages->displayInfo(
            'Protocol entries deleted.'
          );
          if ($createdBefore === NULL) {
            $this->parameters()[ProtocolPage::PARAMETER_NAME_PAGE] = 1;
          }
        } else {
          $this->papaya()->messages->displayError(
            'Delete database action failed.'
          );
        }
      };
      return $dialog;
    }

    private function deleteEntries($severity, $groupId, $createdBefore = NULL) {
      $conditions = (new Root($this->protocolEntries()))->logicalAnd();
      if ($severity >= 0) {
        $conditions->isEqual('severity', $severity);
      }
      if ($groupId !== ProtocolGroups::UNKNOWN) {
        $conditions->isEqual('group_id', $groupId);
      }
      if (NULL !== $createdBefore) {
        $conditions->isLessThan('created_at', $createdBefore);
      }
      if (count($conditions) > 0) {
        return $this->protocolEntries()->truncate($conditions);
      }
      return $this->protocolEntries()->truncate(TRUE);
    }


    public function protocolEntries(ProtocolEntries $protocolEntries = NULL) {
      if (NULL !== $protocolEntries) {
        $this->_protocolEntries = $protocolEntries;
      } elseif (NULL === $this->_protocolEntries) {
        $this->_protocolEntries = new ProtocolEntries();
        $this->_protocolEntries->papaya($this->papaya());
      }
      return $this->_protocolEntries;
    }
  }
}


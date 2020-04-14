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

namespace Papaya\Administration\Protocol {

  use Papaya\Administration\Page\Part as AdministrationPagePart;
  use Papaya\Administration\UI;
  use Papaya\Content\Protocol\ProtocolEntry;
  use Papaya\UI\Toolbar;
  use Papaya\UI\Control\Command\Controller as CommandsController;
  use Papaya\UI\Text\Translated as TranslatedText;

  class ProtocolContent extends AdministrationPagePart {

    const COMMAND_SHOW = 'show';
    const COMMAND_CLEANUP = 'cleanup';

    /**
     * @var ProtocolEntry
     */
    private $_protocolEntry;

    protected function _createCommands($name = ProtocolPage::PARAMETER_NAME_COMMAND, $default = self::COMMAND_SHOW) {
      $commands = new CommandsController($name, $default);
      $commands->owner($this);
      $commands[self::COMMAND_SHOW] = new Commands\ShowProtocolEntry($this->protocolEntry());
      $commands[self::COMMAND_CLEANUP] = new Commands\DeleteProtocolEntries($this->protocolEntry());
      return $commands;
    }

    public function _initializeToolbar(Toolbar\Collection $toolbar) {
      parent::_initializeToolbar($toolbar);
      $toolbar->elements[] = new Toolbar\Separator();
      if ($this->protocolEntry()->isLoaded()) {
        $toolbar->elements[] = $button = new Toolbar\Button();
        $button->image = 'items.bug';
        $button->caption = new TranslatedText('Bug Report');
        $button->reference = $this->papaya()->references->byString(UI::HELP);
        $button->reference()->setParameters(
          [
            'help' => [
              'log_id' => $this->protocolEntry()->id,
              'ohmode' => 'bugreport'
            ]
          ]
        );
        $toolbar->elements[] = new Toolbar\Separator();
        $toolbar->elements[] = $button = new Toolbar\Button();
        $button->image = 'places.trash';
        $button->caption = new TranslatedText('Delete older');
        $button->reference()->setParameters(
          [
            $this->parameterGroup() => [
              ProtocolPage::PARAMETER_NAME_COMMAND => self::COMMAND_CLEANUP,
              ProtocolPage::PARAMETER_NAME_SEVERITY => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_SEVERITY, -1),
              ProtocolPage::PARAMETER_NAME_GROUP => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, 0),
              ProtocolPage::PARAMETER_NAME_PAGE => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_PAGE, 1),
              ProtocolPage::PARAMETER_NAME_ENTRY => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_ENTRY, 0)
            ]
          ]
        );
      }
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'places.trash';
      $button->caption = new TranslatedText('Delete all');
        $button->reference()->setParameters(
          [
            $this->parameterGroup() => [
              ProtocolPage::PARAMETER_NAME_COMMAND => self::COMMAND_CLEANUP,
              ProtocolPage::PARAMETER_NAME_SEVERITY => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_SEVERITY, -1),
              ProtocolPage::PARAMETER_NAME_GROUP => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, 0),
              ProtocolPage::PARAMETER_NAME_PAGE => 1,
              ProtocolPage::PARAMETER_NAME_ENTRY => 0
            ]
          ]
        );
    }

    public function protocolEntry(ProtocolEntry $protocolEntry = NULL) {
      if (NULL !== $protocolEntry) {
        $this->_protocolEntry = $protocolEntry;
      } elseif (NULL === $this->_protocolEntry) {
        $this->_protocolEntry = new ProtocolEntry();
        $this->_protocolEntry->papaya($this->papaya());
        $this->_protocolEntry->activateLazyLoad(
          [
            'id' => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_ENTRY, 0)
          ]
        );
      }
      return $this->_protocolEntry;
    }

  }
}

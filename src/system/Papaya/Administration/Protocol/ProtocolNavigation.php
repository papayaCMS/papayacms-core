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
  use Papaya\Content\Protocol\ProtocolEntries;
  use Papaya\Content\Protocol\ProtocolEntry;
  use Papaya\Content\Protocol\ProtocolGroups;
  use Papaya\Iterator\Callback as CallbackIterator;
  use Papaya\Iterator\Union;
  use Papaya\UI\Dialog\Options;
  use Papaya\UI\ListView;
  use Papaya\UI\Option\Align;
  use Papaya\UI\Reference;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\UI\Toolbar;
  use Papaya\UI\Toolbar\Paging as ToolbarPaging;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class ProtocolNavigation extends AdministrationPagePart {

    /**
     * @var ProtocolEntries
     */
    private $_protocolEntries;
    /**
     * @var ListView
     */
    private $_listView;

    private $_images = [
      ProtocolEntry::SEVERITY_INFO => 'status-dialog-information',
      ProtocolEntry::SEVERITY_WARNING => 'status-dialog-warning',
      ProtocolEntry::SEVERITY_ERROR => 'status-dialog-error',
      ProtocolEntry::SEVERITY_DEBUG => 'items-page',
    ];
    /**
     * @var ToolbarPaging
     */
    private $_paging;
    /**
     * @var Reference
     */
    private $_reference;

    private $_pagingButtonsLimit = 20;
    private $_pagingEntriesLimit = 40;

    public function appendTo(XMLElement $parent) {
      $this->listview()->toolbars->topLeft->elements[] = $paging = $this->paging();
      $paging->reference()->setParameters(
        [
          $this->parameterGroup() => [
            ProtocolPage::PARAMETER_NAME_GROUP => $this->parameters()[ProtocolPage::PARAMETER_NAME_GROUP],
            ProtocolPage::PARAMETER_NAME_SEVERITY => $this->parameters()[ProtocolPage::PARAMETER_NAME_SEVERITY],
            ProtocolPage::PARAMETER_NAME_ENTRY => 0
          ]
        ]
      );
      $paging->itemsCount = $this->protocolEntries()->absCount();
      $parent->append($this->listView());
    }

    /**
     * @param ListView $listView
     * @return ListView
     */
    public function listView(ListView $listView = NULL) {
      if (NULL !== $listView) {
        $this->_listView = $listView;
      } elseif (NULL === $this->_listView) {
        $this->_listView = new ListView();
        $this->_listView->caption = new TranslatedText('Protocol');
        $this->_listView->builder(
          $builder = new ListView\Items\Builder(
            $this->protocolEntries()
          )
        );
        $selectedId = $this->parameters()->get(ProtocolPage::PARAMETER_NAME_ENTRY, 0);
        $groupId = $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, ProtocolGroups::UNKNOWN);
        $builder->callbacks()->onCreateItem = function (
          $context, ListView\Items $items, array $protocolEntry
        ) use ($selectedId, $groupId) {
          $items[] = $item = new ListView\Item(
            $this->getImageBySeverity($protocolEntry['severity']),
            $protocolEntry['summary'],
            [
              $this->parameterGroup() => [
                ProtocolPage::PARAMETER_NAME_PAGE => $this->parameters()[ProtocolPage::PARAMETER_NAME_PAGE],
                ProtocolPage::PARAMETER_NAME_GROUP => $this->parameters()[ProtocolPage::PARAMETER_NAME_GROUP],
                ProtocolPage::PARAMETER_NAME_SEVERITY => $this->parameters()[ProtocolPage::PARAMETER_NAME_SEVERITY],
                ProtocolPage::PARAMETER_NAME_ENTRY => (int)$protocolEntry['id']
              ]
            ]
          );
          $item->selected = (int)$selectedId === (int)$protocolEntry['id'];
          if ($groupId === ProtocolGroups::UNKNOWN) {
            $item->subitems[] = $subItem = new ListView\SubItem\Text(
              new TranslatedText(ProtocolGroups::getLabel((int)$protocolEntry['group_id']))
            );
            $subItem->align = Align::CENTER;
          }
          $item->subitems[] = $subItem = new ListView\SubItem\Date((int)$protocolEntry['created_at']);
          $subItem->align = Align::CENTER;
        };
      }
      return $this->_listView;
    }

    protected function _initializeToolbar(Toolbar\Collection $toolbar) {
      parent::_initializeToolbar($toolbar);
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'items.log.locked';
      $button->caption = new TranslatedText('Login Tries');
      $button->reference = $this->papaya()->references->byString(UI::ADMINISTRATION_PROTOCOL_LOGIN);
      $toolbar->elements[] = new Toolbar\Separator();
      $toolbar->elements[] = $select = new Toolbar\Select(
        [$this->parameterGroup(), ProtocolPage::PARAMETER_NAME_SEVERITY],
        new TranslatedList(
          new Union(
            Union::MIT_KEYS_ASSOC,
            [-1 => 'All'],
            ProtocolEntry::SEVERITY_LABELS
          )
        )
      );
      $select->caption = new TranslatedText('Severity');
      $select->reference()->setParameters(
        [
          $this->parameterGroup() => [
            ProtocolPage::PARAMETER_NAME_GROUP => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, 0),
            ProtocolPage::PARAMETER_NAME_PAGE => 1,
            ProtocolPage::PARAMETER_NAME_ENTRY => 0
          ]
        ]
      );
      $toolbar->elements[] = $select = new Toolbar\Select(
        [$this->parameterGroup(), ProtocolPage::PARAMETER_NAME_GROUP],
        new TranslatedList(
          new CallbackIterator(
            new ProtocolGroups(),
            static function ($current, $key) {
              return (int)$key !== ProtocolGroups::UNKNOWN ? $current : 'All';
            }
          )
        )
      );
      $select->caption = new TranslatedText('Group');
      $select->reference()->setParameters(
        [
          $this->parameterGroup() => [
            ProtocolPage::PARAMETER_NAME_SEVERITY => $this->parameters()->get(
              ProtocolPage::PARAMETER_NAME_SEVERITY, -1
            ),
            ProtocolPage::PARAMETER_NAME_PAGE => 1,
            ProtocolPage::PARAMETER_NAME_ENTRY => 0
          ]
        ]
      );
      $toolbar->elements[] = new Toolbar\Separator();
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'actions.refresh';
      $button->caption = new TranslatedText('Refresh');
      $button->reference()->setParameters(
        [
          $this->parameterGroup() => [
            ProtocolPage::PARAMETER_NAME_SEVERITY => $this->parameters()->get(
              ProtocolPage::PARAMETER_NAME_SEVERITY, -1
            ),
            ProtocolPage::PARAMETER_NAME_GROUP => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, 0),
            ProtocolPage::PARAMETER_NAME_PAGE => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_PAGE, 1),
            ProtocolPage::PARAMETER_NAME_ENTRY => $this->parameters()->get(ProtocolPage::PARAMETER_NAME_ENTRY, 0)
          ]
        ]
      );
    }

    private function getImageBySeverity($severity) {
      return isset($this->_images[(int)$severity]) ? $this->_images[(int)$severity] : '';
    }

    /**
     * @param ToolbarPaging $paging
     *
     * @return ToolbarPaging
     */
    public function paging(ToolbarPaging $paging = NULL) {
      if (NULL !== $paging) {
        $this->_paging = $paging;
      } elseif (NULL === $this->_paging) {
        $this->_paging = new ToolbarPaging(
          [$this->parameterGroup(), ProtocolPage::PARAMETER_NAME_PAGE], 1
        );
        $this->_paging->papaya($this->papaya());
        $this->_paging->reference(clone $this->reference());
        $this->_paging->buttonLimit = $this->_pagingButtonsLimit;
        $this->_paging->itemsPerPage = $this->_pagingEntriesLimit;
      }
      return $this->_paging;
    }

    /**
     * @param Reference $reference
     * @return Reference
     */
    public function reference(Reference $reference = NULL) {
      if (NULL !== $reference) {
        $this->_reference = $reference;
      } elseif (NULL === $this->_reference) {
        $this->_reference = new Reference();
        $this->_reference->papaya($this->papaya());
      }
      return $this->_reference;
    }

    public function protocolEntries(ProtocolEntries $protocolEntries = NULL) {
      if (NULL !== $protocolEntries) {
        $this->_protocolEntries = $protocolEntries;
      } elseif (NULL === $this->_protocolEntries) {
        $this->_protocolEntries = new ProtocolEntries();
        $this->_protocolEntries->papaya($this->papaya());
        $this->_protocolEntries->enableAbsoluteCount();
        $filter = [];
        $groupId = $this->parameters()->get(ProtocolPage::PARAMETER_NAME_GROUP, 0);
        if ($groupId !== ProtocolGroups::UNKNOWN) {
          $filter['group_id'] = $groupId;
        }
        $severity = $this->parameters()->get(ProtocolPage::PARAMETER_NAME_SEVERITY, -1);
        if ($severity >= 0) {
          $filter['severity'] = $severity;
        }
        $this->_protocolEntries->activateLazyLoad(
          $filter,
          $this->_pagingEntriesLimit,
          (max(1, $this->parameters()->get(ProtocolPage::PARAMETER_NAME_PAGE, 1)) - 1) * $this->_pagingEntriesLimit
        );
      }
      return $this->_protocolEntries;
    }
  }
}

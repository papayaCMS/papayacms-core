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

namespace Papaya\Administration\Media {

  use Papaya\Administration\Page\Part as AdministrationPagePart;
  use Papaya\Administration\UI\ListView\ViewToggle;
  use Papaya\Administration\UI\Navigation\Reference\MimeTypeIcon;
  use Papaya\Content\Media\Files;
  use Papaya\Content\Media\Folder;
  use Papaya\Content\Media\Folders;
  use Papaya\Database;
  use Papaya\Filter\ArrayElement;
  use Papaya\Filter\IntegerValue;
  use Papaya\Filter\KeyValue;
  use Papaya\Iterator\RecursiveTraversableIterator;
  use Papaya\Media\Thumbnail\Calculation;
  use Papaya\UI;
  use Papaya\UI\Dialog;
  use Papaya\UI\ListView;
  use Papaya\UI\Option\Align;
  use Papaya\UI\Text\Date;
  use Papaya\UI\Text\Translated;
  use Papaya\UI\Toolbar;
  use Papaya\Utility\Arrays;
  use Papaya\XML\Element as XMLElement;

  class MediaFilesNavigation extends AdministrationPagePart {

    /**
     * @var Folders
     */
    private $_folders;
    /**
     * @var Files
     */
    private $_files;
    /**
     * @var Folder
     */
    private $_selectedFolder;
    /**
     * @var ListView
     */
    private $_foldersListView;
    /**
     * @var ListView
     */
    private $_filesDialog;
    /**
     * @var mixed
     */
    private $_filesPerPage = 10;

    public function appendTo(XMLElement $parent) {
      switch ($this->parameters()->get(MediaFilesPage::PARAMETER_NAVIGATION_MODE, MediaFilesPage::NAVIGATION_MODE_FOLDERS)) {
        case MediaFilesPage::NAVIGATION_MODE_TAGS:
          break;
        case MediaFilesPage::NAVIGATION_MODE_SEARCH:
          break;
        case MediaFilesPage::NAVIGATION_MODE_FOLDERS:
        default:
          $parent->append($this->foldersListView());
      }
      $files = $this->files();
      if (count($files) > 0) {
        $parent->append($this->filesDialog());
      }
    }

    public function foldersListView(ListView $foldersListView = NULL) {
      if (NULL !== $foldersListView) {
        $this->_foldersListView = $foldersListView;
      } elseif (NULL === $this->_foldersListView) {
        $this->_foldersListView = $listView = new ListView();
        $listView->papaya($this->papaya());
        $listView->caption = new Translated('Folders');
        $listView->parameterGroup($this->parameterGroup());
        $listView->items[] = $item = new ListView\Item(
          'places.desktop', new Translated('Desktop'), [MediaFilesPage::PARAMETER_FOLDER => 0]
        );
        $item->subitems[] = new ListView\SubItem\EmptyValue();
        $folders = new RecursiveTraversableIterator($this->folders(), RecursiveTraversableIterator::SELF_FIRST);
        foreach ($folders as $folder) {
          $isSelected = $this->selectedFolder()->id === $folder['id'];
          $listView->items[] = $item = new ListView\Item(
            $isSelected ? 'status.folder-open' : 'items.folder',
            $folder['title'],
            [
              MediaFilesPage::PARAMETER_COMMAND => MediaFilesPage::COMMAND_EDIT_FOLDER,
              MediaFilesPage::PARAMETER_FOLDER => $folder['id']
            ]
          );
          $item->indentation = $folders->getDepth() + 1;
          $item->selected = $isSelected;
          switch ($folder['permission_mode']) {
            case Folder::PERMISSION_MODE_DEFINE:
              $item->subitems[] = $subItem = new ListView\SubItem\Image('items.permission', new Translated('Defines permissions'));
              $subItem->align = Align::CENTER;
              break;
            case Folder::PERMISSION_MODE_INHERIT:
              $item->subitems[] = $subItem = new ListView\SubItem\Image('status.permission-inherited', new Translated('Extends permissions'));
              $subItem->align = Align::CENTER;
              break;
            default:
              $item->subitems[] = new ListView\SubItem\EmptyValue();
          }
        }
      }
      return $this->_foldersListView;
    }

    public function filesDialog(Dialog $filesDialog = NULL) {
      if (NULL !== $filesDialog) {
        $this->_filesDialog = $filesDialog;
      } elseif (NULL === $this->_filesDialog) {
        $this->_filesDialog = $dialog = new Dialog();
        $dialog->papaya($this->papaya());
        $dialog->caption = new Translated('Files');
        $dialog->parameterGroup($this->parameterGroup());
        $dialog->fields[] = $field = new Dialog\Field\ListView($listView = new ListView());
        $listView->toolbars->topLeft->elements[] = $paging = new Toolbar\Paging(
          [$this->parameterGroup(), MediaFilesPage::PARAMETER_FILES_OFFSET],
          $this->files()->absCount(),
          Toolbar\Paging::MODE_OFFSET
        );
        $paging->reference()->setParameters($this->parameters(), $this->parameterGroup());
        $listView->toolbars->topRight->elements[] = $limitToggle = new Toolbar\Select\Buttons(
          [$this->parameterGroup(), MediaFilesPage::PARAMETER_FILES_LIMIT],
          [10 => 10, 20 => 20, 50 => 50, 100 => 100]
        );
        $limitToggle->defaultValue = $this->_filesPerPage;
        $limitToggle->reference()->setParameters($this->parameters(), $this->parameterGroup());
        $limitToggle->reference()->setParameters(
          [MediaFilesPage::PARAMETER_FILES_OFFSET => 0],
          $this->parameterGroup()
        );
        $listView->toolbars->topRight->elements[] = new Toolbar\Separator();
        $listView->toolbars->topRight->elements[] = $viewToggle = new ViewToggle(
          [$this->parameterGroup(), MediaFilesPage::PARAMETER_FILES_VIEW]
        );
        $viewToggle->reference()->setParameters($this->parameters(), $this->parameterGroup());
        $listView->mode = $viewToggle->currentValue;
        $viewMode = $viewToggle->currentValue;
        if (ListView::MODE_DETAILS === $viewMode) {
          $sortParameter = [$this->parameterGroup(), MediaFilesPage::PARAMETER_FILES_SORT];
          $listView->columns[] = $column = new ListView\Column\SortableColumn(new Translated('Name'), $sortParameter);
          $column->reference()->setParameters($this->parameters(), $this->parameterGroup());
          $listView->columns[] = $column = new ListView\Column\SortableColumn(new Translated('Size'), $sortParameter, '', Align::CENTER);
          $column->reference()->setParameters($this->parameters(), $this->parameterGroup());
          $listView->columns[] = $column = new ListView\Column\SortableColumn(new Translated('Uploaded / Created'), $sortParameter, '', Align::CENTER);
          $column->reference()->setParameters($this->parameters(), $this->parameterGroup());
        }
        $listView->builder(
          $builder = new ListView\Items\Builder($this->files())
        );
        $listView->parameterGroup($this->parameterGroup());
        $builder->callbacks()->onCreateItem = function (
          $context, ListView\Items $items, $file
        ) use ($dialog, $viewMode) {
          switch ($viewMode) {
            case ListView::MODE_THUMBNAILS:
            case ListView::MODE_TILES:
              $thumbnailSize = $viewMode === ListView::MODE_THUMBNAILS ? 100 : 48;
              $generator = $this->papaya()->media->createThumbnailGenerator(
                $file['id'], $file['revision'], $file['name']
              );
              if (
              $thumbnail = $generator->createThumbnail(
                $generator->createCalculation($thumbnailSize, $thumbnailSize, Calculation::MODE_CONTAIN)
              )
              ) {
                $icon = '../' . $thumbnail->getURL();
              } else {
                $icon = new MimeTypeIcon($file['icon'], 48);
              }
              $subTitle = new Date($file['date']);
              break;
            case ListView::MODE_DETAILS:
            default:
              $icon = new MimeTypeIcon($file['icon']);
              $subTitle = '';
              break;
          }
          $items[] = $item = new ListView\Item\Checkbox(
            $icon,
            $file['name'],
            $dialog,
            MediaFilesPage::PARAMETER_FILES,
            $file['id'],
            TRUE
          );
          $item->text = $subTitle;
          $item->actionParameters = [
            MediaFilesPage::PARAMETER_FILES_VIEW => $this->parameters()->get(MediaFilesPage::PARAMETER_FILES_VIEW, ''),
            MediaFilesPage::PARAMETER_FILES_SORT => $this->parameters()->get(MediaFilesPage::PARAMETER_FILES_SORT),
            MediaFilesPage::PARAMETER_FILES_LIMIT => $this->parameters()->get(MediaFilesPage::PARAMETER_FILES_LIMIT, $this->_filesPerPage),
            MediaFilesPage::PARAMETER_FILES_OFFSET => $this->parameters()->get(MediaFilesPage::PARAMETER_FILES_OFFSET, 0),
            MediaFilesPage::PARAMETER_FOLDER => $this->selectedFolder()->id,
            MediaFilesPage::PARAMETER_COMMAND => MediaFilesPage::COMMAND_EDIT_FILE,
            MediaFilesPage::PARAMETER_FILE => $file['id']
          ];
          $item->selected = $this->parameters()->get(MediaFilesPage::PARAMETER_FILE, '') === $file['id'];
          if (ListView::MODE_DETAILS === $viewMode) {
            $item->subitems[] = new ListView\SubItem\Bytes($file['size']);
            $item->subitems[] = new ListView\SubItem\Date($file['date']);
          }
        };
        if (count($this->files()) > 0) {
          $dialog->buttons[] = $button = new Dialog\Button\NamedSubmit(
            new Translated('Delete'), MediaFilesPage::PARAMETER_FILES_ACTION, MediaFilesPage::FILES_ACTION_DELETE
          );
          $button->setImage('places.trash');
          $dialog->buttons[] = $button = new Dialog\Button\NamedSubmit(
            new Translated('Cut'), MediaFilesPage::PARAMETER_FILES_ACTION, MediaFilesPage::FILES_ACTION_CUT
          );
          $button->setImage('actions.edit-cut');
          $dialog->buttons[] = $button = new Dialog\Button\NamedSubmit(
            new Translated('Move'), MediaFilesPage::PARAMETER_FILES_ACTION, MediaFilesPage::FILES_ACTION_MOVE
          );
          $button->setImage('items.image.move');
          $dialog->buttons[] = $button = new Dialog\Button\NamedSubmit(
            new Translated('Tag'), MediaFilesPage::PARAMETER_FILES_ACTION, MediaFilesPage::FILES_ACTION_TAG
          );
          $button->setImage('items.tag');
        }
      }
      return $this->_filesDialog;
    }

    public function folders(Folders $folders = NULL) {
      if (NULL !== $folders) {
        $this->_folders = $folders;
      } elseif (NULL === $this->_folders) {
        $this->_folders = new Folders();
        $this->_folders->papaya($this->papaya());
        $folderIds = $this->selectedFolder()->ancestors ?: [0];
        $folderIds[] = $this->selectedFolder()->id;
        $this->_folders->activateLazyLoad(
          [
            'parent_id' => $folderIds,
            'language_id' => $this->papaya()->administrationLanguage->id
          ]
        );
      }
      return $this->_folders;
    }

    public function files(Files $files = NULL) {
      if (NULL !== $files) {
        $this->_files = $files;
      } elseif (NULL === $this->_files) {
        $this->_files = new Files();
        $this->_files->papaya($this->papaya());
        $sorts = [
          Files::SORT_BY_NAME, Files::SORT_BY_SIZE, Files::SORT_BY_DATE
        ];
        $sort = Arrays::firstNotNull(
          $this->parameters()->get(
            MediaFilesPage::PARAMETER_FILES_SORT,
            ['key' => Files::SORT_BY_NAME, ListView\Column\SortableColumn::SORTED_ASCENDING],
            new KeyValue(
              new IntegerValue(0, 2),
              new ArrayElement(
                [ListView\Column\SortableColumn::SORTED_ASCENDING, ListView\Column\SortableColumn::SORTED_DESCENDING]
              )
            )
          )
        );
        $this->_files->setSorting(
          $sorts[$sort['key']],
          $sort['value'] === ListView\Column\SortableColumn::SORTED_DESCENDING
            ? Database\Interfaces\Order::DESCENDING
            : Database\Interfaces\Order::ASCENDING
        );
        $this->_files->activateLazyLoad(
          [
            'folder_id' => $this->selectedFolder()->id,
            'language_id' => $this->papaya()->administrationLanguage->id
          ],
          $this->parameters()->get(MediaFilesPage::PARAMETER_FILES_LIMIT, $this->_filesPerPage),
          $this->parameters()->get(MediaFilesPage::PARAMETER_FILES_OFFSET, 0)
        );
      }
      return $this->_files;
    }

    public function selectedFolder(Folder $folder = NULL) {
      if (NULL !== $folder) {
        $this->_selectedFolder = $folder;
      } elseif (NULL === $this->_selectedFolder) {
        $this->_selectedFolder = new Folder();
        $this->_selectedFolder->papaya($this->papaya());
        $this->_selectedFolder->activateLazyLoad(
          ['id' => $this->parameters()->get(MediaFilesPage::PARAMETER_FOLDER, 0)]
        );
      }
      return $this->_selectedFolder;
    }

    public function _initializeToolbar(UI\Toolbar\Collection $toolbar) {
      parent::_initializeToolbar($toolbar);
      $toggle = new Toolbar\Select\Buttons(
        [$this->parameterGroup(), MediaFilesPage::PARAMETER_NAVIGATION_MODE],
        [
          MediaFilesPage::NAVIGATION_MODE_FOLDERS => [new Translated('Folders'), 'items.folder'],
          MediaFilesPage::NAVIGATION_MODE_TAGS => [new Translated('Tags'), 'items.tag'],
          MediaFilesPage::NAVIGATION_MODE_SEARCH => [new Translated('Search'), 'actions.search']
        ]
      );
      $toggle->defaultValue = MediaFilesPage::NAVIGATION_MODE_FOLDERS;
      $toolbar->elements[] = $toggle;
      $toolbar->elements[] = new Toolbar\Separator();
    }
  }
}

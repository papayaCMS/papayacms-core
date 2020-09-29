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
  use Papaya\Administration\UI\Navigation\Reference\MimeTypeIcon;
  use Papaya\Content\Media\Files;
  use Papaya\Content\Media\Folder;
  use Papaya\Content\Media\Folders;
  use Papaya\Controller\Media;
  use Papaya\Iterator\RecursiveTraversableIterator;
  use Papaya\UI;
  use Papaya\UI\Dialog;
  use Papaya\UI\ListView;
  use Papaya\UI\Option\Align;
  use Papaya\UI\Text\Translated;
  use Papaya\UI\Toolbar;
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
          switch($folder['permission_mode']) {
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
        $listView->columns[] = new ListView\Column(new Translated('Name'));
        $listView->columns[] = new ListView\Column(new Translated('Size'), Align::CENTER);
        $listView->columns[] = new ListView\Column(new Translated('Uploaded / Created'), Align::CENTER);
        $listView->builder(
          $builder = new ListView\Items\Builder($this->files())
        );
        $builder->callbacks()->onCreateItem = static function(
          $context, ListView\Items $items, $file
        ) use ($dialog) {
          $items[] = $item = new ListView\Item\Checkbox(
            new MimeTypeIcon($file['icon']), $file['name'], $dialog, MediaFilesPage::PARAMETER_FILE_LIST, $file['id'], TRUE
          );
          $item->actionParameters = [
            MediaFilesPage::PARAMETER_FILE => $file['id']
          ];
          $item->subitems[] = new ListView\SubItem\Bytes($file['size']);
          $item->subitems[] = new ListView\SubItem\Date($file['created']);
        };
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
        $this->_files->activateLazyLoad(
          [
            'folder_id' => $this->selectedFolder()->id,
            'language_id' => $this->papaya()->administrationLanguage->id
          ]
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

    public function _initializeToolbar(UI\Toolbar\Collection $toolbar)
    {
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

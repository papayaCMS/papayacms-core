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

namespace Papaya\CMS\Administration\Media {

  use Papaya\CMS\Administration\Media\Commands\ChangeFile;
  use Papaya\CMS\Administration\Media\Commands\ChangeFolder;
  use Papaya\CMS\Administration\Page\Part as AdministrationPagePart;
  use Papaya\CMS\Content\Media\File;
  use Papaya\CMS\Content\Media\Folder;
  use Papaya\UI\Control\Command\Controller;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Toolbar;

  class MediaFilesContent extends AdministrationPagePart {

    /**
     * @var Folder
     */
    private $_folder;
    /**
     * @var mixed
     */
    private $_file;

    protected function _createCommands(
      $name = MediaFilesPage::PARAMETER_COMMAND,
      $default = MediaFilesPage::PARAMETER_FILES_VIEW
    ): Controller {
      $commands = parent::_createCommands($name, $default);
      $commands['edit-folder'] = new ChangeFolder($this->folder(), MediaFilesPage::COMMAND_EDIT_FOLDER);
      $commands['add-folder'] = new ChangeFolder($this->folder(), MediaFilesPage::COMMAND_ADD_FOLDER);
      $commands['edit-file'] = new ChangeFile($this->file(), MediaFilesPage::COMMAND_EDIT_FILE);
      return $commands;
    }

    public function folder(Folder $folder = NULL): Folder {
      if (NULL !== $folder) {
        $this->_folder = $folder;
      } elseif (NULL === $this->_folder) {
        $this->_folder = new Folder();
        $this->_folder->papaya($this->papaya());
        $this->_folder->activateLazyLoad(
          [
            'id' => $this->parameters()->get(MediaFilesPage::PARAMETER_FOLDER, 0),
            'language_id' => $this->papaya()->administrationLanguage->id
          ]
        );
      }
      return $this->_folder;
    }

    public function file(File $file = NULL): File {
      if (NULL !== $file) {
        $this->_file = $file;
      } elseif (NULL === $this->_file) {
        $this->_file = new File();
        $this->_file->papaya($this->papaya());
        $this->_file->activateLazyLoad(
          [
            'id' => $this->parameters()->get(MediaFilesPage::PARAMETER_FILE, ''),
            'language_id' => $this->papaya()->administrationLanguage->id
          ]
        );
      }
      return $this->_file;
    }

    public function _initializeToolbar(Toolbar\Collection $toolbar): void {
      parent::_initializeToolbar($toolbar);
      $toolbar->elements[] = new Toolbar\Separator();
      if ($this->getCurrentMode() === MediaFilesPage::NAVIGATION_MODE_FOLDERS) {
        if ($this->folder()->id > 0) {
          $toolbar->elements[] = new Toolbar\Button(
            'actions.upload',
            new TranslatedText('Upload'),
            [
              $this->parameterGroup() => [
                MediaFilesPage::PARAMETER_FOLDER => $this->folder()->parentId,
                MediaFilesPage::PARAMETER_COMMAND => MediaFilesPage::COMMAND_ADD_FOLDER
              ]
            ]
          );
          $toolbar->elements[] = new Toolbar\Separator();
          $toolbar->elements[] = new Toolbar\Button(
            'items.folder.add',
            new TranslatedText('Add Folder'),
            [
              $this->parameterGroup() => [
                MediaFilesPage::PARAMETER_FOLDER => $this->folder()->parentId,
                MediaFilesPage::PARAMETER_COMMAND => MediaFilesPage::COMMAND_ADD_FOLDER
              ]
            ]
          );
        }
        $toolbar->elements[] = new Toolbar\Button(
          'items.folder-child.add',
          new TranslatedText('Add Child Folder'),
          [
            $this->parameterGroup() => [
              MediaFilesPage::PARAMETER_FOLDER => $this->folder()->id,
              MediaFilesPage::PARAMETER_COMMAND => MediaFilesPage::COMMAND_ADD_FOLDER
            ]
          ]
        );
      }
    }

    private function getCurrentMode(): string {
      return $this->parameters()->get(
        MediaFilesPage::PARAMETER_NAVIGATION_MODE,
        MediaFilesPage::NAVIGATION_MODE_FOLDERS
      );
    }
  }
}

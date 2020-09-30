<?php
/*
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

  use Papaya\Administration\Page as AdministrationPage;
  use Papaya\Administration\PageParameters;
  use Papaya\Content\Media\Folder;

  class MediaFilesPage extends AdministrationPage {

    const PARAMETER_COMMAND = 'cmd';
    const COMMAND_EDIT_FOLDER = 'edit-folder';
    const COMMAND_EDIT_FILE = 'edit-file';
    const PARAMETER_FOLDER = 'folder-id';
    const PARAMETER_FILE = 'file-id';
    const PARAMETER_FILES = 'files';
    const PARAMETER_FILES_VIEW = 'view';
    const PARAMETER_NAVIGATION_MODE = 'navigation';
    const NAVIGATION_MODE_FOLDERS = 'folders';
    const NAVIGATION_MODE_TAGS = 'tags';
    const NAVIGATION_MODE_SEARCH = 'search';

    protected $_parameterGroup = 'media';

    protected function createContent() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_CONTENT, '50%');
      return new MediaFilesContent($this);
    }

    protected function createNavigation() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_NAVIGATION, '50%');
      return new MediaFilesNavigation($this);
    }
  }
}

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
namespace Papaya\CMS\Administration\Settings\Commands {

  use Papaya\CMS\CMSConfiguration as CMSSettings;
  use Papaya\File\System\Factory as FileSystemFactory;
  use Papaya\UI\Control\Command;
  use Papaya\UI\ListView;
  use Papaya\UI\Option\Align;
  use Papaya\UI\Text\Translated;
  use Papaya\XML\Element;

  class ValidatePaths extends Command {

    /**
     * @var mixed
     */
    private $_listView;
    /**
     * @var mixed|FileSystemFactory
     */
    private $_fileSystem;

    public function appendTo(Element $parent) {
      $paths = [
        [
          $this->papaya()->options->get(CMSSettings::PATH_CACHE, ''),
          'Cache path'
        ],
        [
          dirname($this->papaya()->options->get(CMSSettings::PATH_MEDIAFILES, '')),
          'Media main path'
        ],
        [
          $this->papaya()->options->get(CMSSettings::PATH_MEDIAFILES, ''),
          'Media files path'
        ],
        [
          $this->papaya()->options->get(CMSSettings::PATH_THUMBFILES, ''),
          'Thumbnail path'
        ]
      ];
      $listView = $this->listView();
      foreach ($paths as list($path, $caption)) {
        if (empty($path)) {
          $listView->items[] = $item = new ListView\Item('status.dialog-error', new Translated($caption));
          $item->papaya($this->papaya());
          $item->subitems[] = $subItem = new ListView\SubItem\Text(
            new Translated('Not configured')
          );
          $subItem->setColumnSpan(3);
        } else {
          $directory = $this->fileSystem()->getDirectory($path);
          if (!$directory->exists()) {
            $directory->create();
          }
          $listView->items[] = $item = new ListView\Item('items.folder', new Translated($caption));
          $item->papaya($this->papaya());
          $item->text = $path;
          $item->subitems[] = $subItem = new ListView\SubItem\Image(
            $directory->exists() ? 'status.sign-ok' : 'status.sign-problem'
          );
          $item->subitems[] = $subItem = new ListView\SubItem\Image(
            $directory->isReadable() ? 'status.sign-ok' : 'status.sign-problem'
          );
          $item->subitems[] = $subItem = new ListView\SubItem\Image(
            $directory->isWritable() ? 'status.sign-ok' : 'status.sign-problem'
          );
        }
      }
      $listView->appendTo($parent);
    }

    /**
     * @param ListView|null $listView
     * @return ListView
     */
    public function listView(ListView $listView = NULL) {
      if (NULL !== $listView) {
        $this->_listView = $listView;
      } elseif (NULL === $this->_listView) {
        $this->_listView = $listView = new ListView();
        $listView->papaya($this->papaya());
        $listView->columns[] = new ListView\Column(new Translated('Path'));
        $listView->columns[] = new ListView\Column(new Translated('Exists'), Align::CENTER);
        $listView->columns[] = new ListView\Column(new Translated('Readable'), Align::CENTER);
        $listView->columns[] = new ListView\Column(new Translated('Writeable'), Align::CENTER);
      }
      return $this->_listView;
    }

    public function fileSystem(FileSystemFactory $fileSystem = NULL) {
      if (NULL !== $fileSystem) {
        $this->_fileSystem = $fileSystem;
      } elseif (NULL === $this->_fileSystem) {
        $this->_fileSystem = new FileSystemFactory();
      }
      return $this->_fileSystem;
    }
  }
}



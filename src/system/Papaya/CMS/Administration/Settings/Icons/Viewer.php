<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Administration\Settings\Icons {

  use Papaya\CMS\Administration\UI as AdministrationUI;
  use Papaya\UI;
  use Papaya\Iterator;
  use Papaya\Utility\File\Path;
  use Papaya\XML;

  class Viewer extends \Papaya\CMS\Administration\Page\Part {
    /**
     * @var mixed
     */
    private static $_pattern = '(
      (?<size>\\d+x\\d+)/(?<group>[^/]+)/(?<name>[^/]+)(?:\.(?<type>svg|png))$
    )x';

    /**
     * @var iterable
     */
    private $_files;

    public function files(iterable $files = NULL): iterable {
      if (isset($files)) {
        $this->_files = $files;
      } elseif (NULL === $this->_files) {
        $path = Path::cleanup(__DIR__.'/../../Assets/Icons');
        $this->_files = new Iterator\RecursiveTraversableIterator(
          new \RecursiveDirectoryIterator(
            $path,
            \RecursiveDirectoryIterator::CURRENT_AS_PATHNAME |
            \RecursiveDirectoryIterator::SKIP_DOTS
          ),
          \RecursiveIteratorIterator::LEAVES_ONLY
        );
      }
      return $this->_files;
    }

    public function appendTo(XML\Element $parent) {
      parent::appendTo($parent);
      $files = $this->files();
      $groups = [];
      foreach ($files as $file) {
        $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
        if (preg_match(self::$_pattern, $file, $match)) {
          $group = strtolower($match['group']);
          $name = strtolower($match['name']);
          $size = strtolower($match['size']);
          $type = strtolower($match['type']);
          $groups[$group][$name][$type][$size] = $file;
        }
      }
      $listView = new UI\ListView();
      $listView->mode = UI\ListView::MODE_TILES;
      $listView->caption = new UI\Text\Translated('Combination Examples');
      $examples = [
        'disabled' => 'items.folder.disabled',
        'emblem: add' => 'items.folder.add',
        'emblem: remove' => 'items.folder.remove',
        'disabled, emblem: add' => 'items.folder.disabled,add',
        'emblem: add, disabled' => 'items.folder.add,disabled',
        'emblems: public, blocked' => 'items.page.public,blocked',
      ];
      foreach ($examples as $name => $example) {
        $iconRoute = implode('.', [AdministrationUI::ICON, $example]);
        $listView->items[] = $item = new UI\ListView\Item(
          $iconRoute, $name
        );
        $item->hint = $iconRoute;
      }
      $parent->append($listView);

      foreach ($groups as $groupName => $group) {
        $listView = new UI\ListView();
        $listView->mode = UI\ListView::MODE_TILES;
        $imageCount = 0;
        foreach ($group as $iconName => $sizesAvailable) {
          $sizes = ['16x16', '22x22', '48x48'];
          $types = ['svg', 'png'];
          foreach ($types as $type) {
            if (!isset($sizesAvailable[$type])) {
              $sizesAvailable[$type] = [];
            }
          }
          $imageCount++;
          $iconRoute = implode('.', [AdministrationUI::ICON, $groupName, $iconName]);
          $listView->items[] = $item = new UI\ListView\Item(
            implode('.', [AdministrationUI::ICON, $groupName, $iconName]), $iconName
          );
          $item->hint = $iconRoute;
          $text = '';
          if ($sizesAvailable['svg'] && $sizesAvailable['png']) {
            $text .= \sprintf(
              'SVG: %s, PNG: %s',
              \implode(', ', array_keys($sizesAvailable['svg'] ?? [])),
              \implode(', ', array_keys($sizesAvailable['png'] ?? []))
            );
          } else {
            $text .= implode(
              ', ',
              array_merge(
                array_keys($sizesAvailable['svg'] ?? []),
                array_keys($sizesAvailable['png'] ?? [])
              )
            );
          }
          $item->text = $text;
        }
        $listView->caption = \sprintf(
          '%s (%s: %d)',
          $groupName,
          new UI\Text\Translated('Icons'),
          $imageCount
        );

        $parent->append($listView);
      }
    }
  }
}

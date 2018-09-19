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
namespace Papaya\Administration\Pages\Dependency\Synchronization;

use Papaya\Administration;
use Papaya\Content\Page;

/**
 * Synchronize assigned tags of the page
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Tags
  implements Administration\Pages\Dependency\Synchronization {
  /**
   * buffer variable for the page tags content object
   *
   * @var Page\Tags
   */
  private $_tags;

  /**
   * Synchronize the tags of the page dependencies
   *
   * @param array $targetIds
   * @param int $originId
   * @param array|null $languages
   *
   * @return bool
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    if ($this->tags()->load($originId)) {
      $tagIds = [];
      foreach ($this->tags() as $tag) {
        $tagIds[] = $tag['id'];
      }
      foreach ($targetIds as $targetId) {
        if (!$this->synchronizeTags($targetId, $tagIds)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Getter/Setter for the tags subobject
   *
   * @param Page\Tags $tags
   *
   * @return Page\Tags
   */
  public function tags(Page\Tags $tags = NULL) {
    if (NULL !== $tags) {
      $this->_tags = $tags;
    } elseif (NULL === $this->_tags) {
      $this->_tags = new Page\Tags();
    }
    return $this->_tags;
  }

  /**
   * Syncronize/set the tags of one taget page
   *
   * @param int $targetId
   * @param array $tagIds
   *
   * @return bool
   */
  public function synchronizeTags($targetId, array $tagIds) {
    if ($this->tags()->clear($targetId)) {
      if (!empty($tagIds)) {
        return $this->tags()->insert($targetId, $tagIds);
      }
      return TRUE;
    }
    return FALSE;
  }
}

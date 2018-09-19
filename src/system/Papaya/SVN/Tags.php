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
namespace Papaya\SVN;

class Tags implements \IteratorAggregate, \Countable {
  /**
   * SVN client
   *
   * @var \Papaya\SVN\Client|null
   */
  private $_svnClient;

  /**
   * Get/set the SVN client
   *
   * @param \Papaya\SVN\Client $client
   *
   * @return \Papaya\SVN\Client
   */
  public function svnClient(\Papaya\SVN\Client $client = NULL) {
    if (isset($client)) {
      $this->_svnClient = $client;
    }
    if (\is_null($this->_svnClient)) {
      $this->_svnClient = new \Papaya\SVN\Client\Extension();
    }
    return $this->_svnClient;
  }

  /**
   * @var string
   */
  private $_tagDirectoryURL;

  /**
   * @var int
   */
  private $_newerThanRevision;

  /**
   * @var int
   */
  private $_highestRevisionSeen;

  /**
   * @var array of tag urls
   */
  private $_newTags;

  /**
   * Find the tags in the $tagDirectoryURL that are newer than
   * $newerThanRevision . The SVN repository is not accessed until
   * the resulting object is accessed.
   *
   * @param string $tagDirectoryURL
   * @param int $newerThanRevision
   */
  public function __construct($tagDirectoryURL, $newerThanRevision = 0) {
    \Papaya\Utility\Constraints::assertString($tagDirectoryURL);
    $this->_tagDirectoryURL = $tagDirectoryURL;
    \Papaya\Utility\Constraints::assertInteger($newerThanRevision);
    $this->_newerThanRevision = $newerThanRevision;
    $this->_highestRevisionSeen = $newerThanRevision;
  }

  /**
   * Return the highest SVN revision seen while finding tags.
   *
   * @return int
   */
  public function highestRevisionSeen() {
    $this->find();
    return $this->_highestRevisionSeen;
  }

  /**
   * Used to lazily do the actual work.
   */
  private function find() {
    if (!\is_null($this->_newTags)) {
      return;
    }
    $this->_newTags = [];
    $this->_tagDirectoryURL =
      \Papaya\Utility\File\Path::ensureTrailingSlash($this->_tagDirectoryURL);
    $tagList = $this->svnClient()->ls($this->_tagDirectoryURL);
    foreach ($tagList as $tag) {
      $revision = (int)$tag['created_rev'];
      if ($revision <= $this->_newerThanRevision) {
        continue;
      }
      if ($revision > $this->_highestRevisionSeen) {
        $this->_highestRevisionSeen = $revision;
      }
      if ('dir' === $tag['type']) {
        $tagURL = $this->_tagDirectoryURL.$tag['name'];
        $this->_newTags[] = $tagURL;
      }
    }
  }

  /**
   * Return an iterator over the tags.
   *
   * @return \ArrayIterator
   */
  public function getIterator() {
    $this->find();
    return new \ArrayIterator($this->_newTags);
  }

  /**
   * Return the tag count.
   *
   * @return int
   */
  public function count() {
    $this->find();
    return \count($this->_newTags);
  }
}

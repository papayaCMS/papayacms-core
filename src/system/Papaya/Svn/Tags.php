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

class PapayaSvnTags implements \IteratorAggregate, \Countable {

  /**
  * SVN client
  * @var PapayaSvnClient|NULL
  */
  private $_svnClient = NULL;

  /**
  * Get/set the SVN client
  *
  * @param \PapayaSvnClient $client
  * @return \PapayaSvnClient
  */
  public function svnClient(\PapayaSvnClient $client = NULL) {
    if (isset($client)) {
      $this->_svnClient = $client;
    }
    if (is_null($this->_svnClient)) {
      $this->_svnClient = new \PapayaSvnClientExtension();
    }
    return $this->_svnClient;
  }

  /**
  * @var string
  */
  private $_tagDirectoryUrl;
  /**
  * @var integer
  */
  private $_newerThanRevision;
  /**
  * @var integer
  */
  private $_highestRevisionSeen;
  /**
  * @var array of tag urls
  */
  private $_newTags = NULL;

  /**
  * Find the tags in the $tagDirectoryUrl that are newer than
  * $newerThanRevision . The SVN repository is not accessed until
  * the resulting object is accessed.
  *
  * @param string $tagDirectoryUrl
  * @param integer $newerThanRevision
  */
  public function __construct($tagDirectoryUrl, $newerThanRevision = 0) {
    \PapayaUtilConstraints::assertString($tagDirectoryUrl);
    $this->_tagDirectoryUrl = $tagDirectoryUrl;
    \PapayaUtilConstraints::assertInteger($newerThanRevision);
    $this->_newerThanRevision = $newerThanRevision;
    $this->_highestRevisionSeen = $newerThanRevision;
  }

  /**
  * Return the highest SVN revision seen while finding tags.
  * @return integer
  */
  public function highestRevisionSeen() {
    $this->find();
    return $this->_highestRevisionSeen;
  }

  /**
  * Used to lazily do the actual work.
  */
  private function find() {
    if (!is_null($this->_newTags)) {
      return;
    }
    $this->_newTags = array();
    $this->_tagDirectoryUrl =
      \PapayaUtilFilePath::ensureTrailingSlash($this->_tagDirectoryUrl);
    $tagList = $this->svnClient()->ls($this->_tagDirectoryUrl);
    foreach ($tagList as $tag) {
      $revision = (int)$tag['created_rev'];
      if ($revision <= $this->_newerThanRevision) {
        continue;
      }
      if ($revision > $this->_highestRevisionSeen) {
        $this->_highestRevisionSeen = $revision;
      }
      if ($tag['type'] === 'dir') {
        $tagUrl = $this->_tagDirectoryUrl.$tag['name'];
        $this->_newTags[] = $tagUrl;
      }
    }
  }

  /**
  * Return an iterator over the tags.
  * @return \ArrayIterator
  */
  public function getIterator() {
    $this->find();
    return new \ArrayIterator($this->_newTags);
  }

  /**
  * Return the tag count.
  * @return integer
  */
  public function count() {
    $this->find();
    return count($this->_newTags);
  }

}

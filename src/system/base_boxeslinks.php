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

use Papaya\Cache;

/**
* Link Box with page
*
* @package Papaya
* @subpackage Core
*/
class base_boxeslinks extends base_db {

  const INHERIT_NONE = 0;
  const INHERIT_ALL = 1;
  const INHERIT_BOXES = 2;
  const INHERIT_GROUPS = 3;

  /**
  * Papaya database table box
  * @var string $tableBox
  */
  var $tableBox = PAPAYA_DB_TBL_BOX;
  /**
  * Papaya database table box public
  * @var string $tableBoxPublic
  */
  var $tableBoxPublic = PAPAYA_DB_TBL_BOX_PUBLIC;
  /**
  * Papaya database table box translation
  * @var string $tableBoxTrans
  */
  var $tableBoxTrans = PAPAYA_DB_TBL_BOX_TRANS;
  /**
  * Papaya database table box public translation
  * @var string $tableBoxPublicTrans
  */
  var $tableBoxPublicTrans = PAPAYA_DB_TBL_BOX_PUBLIC_TRANS;
  /**
  * Papaya database table box group
  * @var string $tableBoxgroup
  */
  var $tableBoxgroup = PAPAYA_DB_TBL_BOXGROUP;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Papaya database table view links
  * @var string $tableViewLinks
  */
  var $tableViewLinks = PAPAYA_DB_TBL_VIEWLINKS;
  /**
  * Papaya database table box links
  * @var string $tableLink
  */
  var $tableLink = PAPAYA_DB_TBL_BOXLINKS;
  /**
  * Papaya database table topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table topic translations
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * Box data
  * @var array $data
  */
  var $data = array();

  /**
   * @var papaya_parser $parser
   */
  public $parser;

  /**
   * Private checksum containing the loaded parameters for $data
   *
   * @var mixed
   */
  private $_checksum = NULL;

  /**
  * Box groups list
  * @var array $boxGroupsList
  */
  var $boxGroupsList = array();

  public $topicId = 0;
  protected $groupPageId = 0;

  /**
  * PHP 5 constructor
  *
  * @param object $aOwner
  * @param string $paramName optional, default value 'bl'
  * @access public
  */
  function __construct($aOwner, $paramName='bl') {
    $this->parentObj = $aOwner;
    $this->paramName = $paramName;
  }

  /**
   * Set the page id for the boxes.
   *
   * @param integer $pageId
   * @param null|integer $groupPageId
   */
  public function setPageId($pageId, $groupPageId = NULL) {
    $this->topicId = $pageId;
    if (isset($groupPageId)) {
      $this->groupPageId = $groupPageId;
    }
  }

  /**
  * Load box group list
  *
  * @access public
  * @return bool
  */
  function loadBoxGroupList() {
    $this->boxGroupsList = array();
    $sql = "SELECT boxgroup_id, boxgroup_title, boxgroup_name, boxgroup_linkable
              FROM %s
             ORDER BY boxgroup_title";
    $params = array($this->tableBoxgroup);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->boxGroupsList[$row['boxgroup_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Load data list
   *
   * @param integer $lngId language id
   * @param integer $viewModeId view mode id
   * @param null|integer $now
   * @access public
   * @return bool
   */
  function loadDataList($lngId, $viewModeId, $now = NULL) {
    $this->data = array();
    if (isset($now)) {
      $publishedFilter = sprintf(
        "AND (b.box_public_from = 0 OR b.box_public_from <= '%d')".
        " AND (b.box_public_to = 0 OR b.box_public_to >= '%d')",
        $now,
        $now
      );
    } else {
      $publishedFilter = '';
    }
    if ($this->topicId > 0) {
      if ($viewModeId > 0) {
        $sql = "SELECT bl.topic_id, bl.box_id, bl.box_sort,
                       b.box_name, b.boxgroup_id,
                       b.box_deliverymode,
                       b.box_cachemode, b.box_cachetime,
                       b.box_expiresmode, b.box_expirestime,
                       bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                       v.view_name,
                       m.module_guid, m.module_useoutputfilter,
                       m.module_path, m.module_file, m.module_class
                  FROM %s bl,
                       %s b,
                       %s bt,
                       %s v,
                       %s vl,
                       %s m
                 WHERE bl.topic_id = '%d'
                   AND bl.boxgroup_id = 0
                   AND b.box_id = bl.box_id
                   AND bt.box_id = bl.box_id
                   AND bt.lng_id = '%d'
                   AND v.view_id = bt.view_id
                   AND vl.view_id = bt.view_id
                   AND vl.viewmode_id = %d
                   AND m.module_guid = v.module_guid
                   AND m.module_active = 1
                   AND m.module_type = 'box'
                       $publishedFilter
                 ORDER BY bl.box_sort";
        $params = array(
          $this->tableLink,
          $this->tableBox,
          $this->tableBoxTrans,
          $this->tableViews,
          $this->tableViewLinks,
          $this->tableModules,
          $this->topicId,
          $lngId,
          $viewModeId
        );
      } else {
        $sql = "SELECT bl.topic_id, bl.box_id, bl.box_sort,
                       b.box_name, b.boxgroup_id,
                       b.box_deliverymode,
                       b.box_cachemode, b.box_cachetime,
                       b.box_expiresmode, b.box_expirestime,
                       bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                       m.module_guid, m.module_useoutputfilter,
                       m.module_path, m.module_file, m.module_class
                  FROM %s bl,
                       %s b,
                       %s bt,
                       %s v,
                       %s m
                 WHERE bl.topic_id = '%d'
                   AND bl.boxgroup_id = 0
                   AND b.box_id = bl.box_id
                   AND bt.box_id = bl.box_id
                   AND bt.lng_id = '%d'
                   AND v.view_id = bt.view_id
                   AND m.module_guid = v.module_guid
                   AND m.module_active = 1
                   AND m.module_type = 'box'
                       $publishedFilter
                 ORDER BY bl.box_sort";
        $params = array(
          $this->tableLink,
          $this->tableBox,
          $this->tableBoxTrans,
          $this->tableViews,
          $this->tableModules,
          $this->topicId,
          $lngId
        );
      }
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $row['module_file'] = $row['module_path'].$row['module_file'];
          $this->data[$row['box_id']] = $row;
        }
      } else {
        return FALSE;
      }
    }
    if ($this->groupPageId > 0) {
      if ($viewModeId > 0) {
        $sql = "SELECT bl.topic_id, b.box_id, bl.box_sort,
                       b.box_name, b.boxgroup_id,
                       b.box_deliverymode,
                       b.box_cachemode, b.box_cachetime,
                       b.box_expiresmode, b.box_expirestime,
                       bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                       m.module_guid, m.module_useoutputfilter,
                       m.module_path, m.module_file, m.module_class
                  FROM %s bl,
                       %s b,
                       %s bt,
                       %s v,
                       %s vl,
                       %s m
                 WHERE bl.topic_id = '%d'
                   AND bl.boxgroup_id > 0
                   AND b.boxgroup_id = bl.boxgroup_id
                   AND bt.box_id = b.box_id
                   AND bt.lng_id = '%d'
                   AND v.view_id = bt.view_id
                   AND vl.view_id = bt.view_id
                   AND vl.viewmode_id = %d
                   AND m.module_guid = v.module_guid
                   AND m.module_active = 1
                   AND m.module_type = 'box'
                       $publishedFilter
                 ORDER BY b.box_position";
        $params = array(
          $this->tableLink,
          $this->tableBox,
          $this->tableBoxTrans,
          $this->tableViews,
          $this->tableViewLinks,
          $this->tableModules,
          $this->groupPageId,
          $lngId,
          $viewModeId
        );
      } else {
        $sql = "SELECT bl.topic_id, b.box_id, bl.box_sort,
                       b.box_name, b.boxgroup_id,
                       b.box_deliverymode,
                       b.box_cachemode, b.box_cachetime,
                       b.box_expiresmode, b.box_expirestime,
                       bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                       m.module_guid, m.module_useoutputfilter,
                       m.module_path, m.module_file, m.module_class
                  FROM %s bl,
                       %s b,
                       %s bt,
                       %s v,
                       %s m
                 WHERE bl.topic_id = '%d'
                   AND bl.boxgroup_id > 0
                   AND b.boxgroup_id = bl.boxgroup_id
                   AND bt.lng_id = '%d'
                   AND v.view_id = bt.view_id
                   AND m.module_guid = v.module_guid
                   AND m.module_active = 1
                   AND m.module_type = 'box'
                       $publishedFilter
                 ORDER BY b.box_position";
        $params = array(
          $this->tableLink,
          $this->tableBox,
          $this->tableBoxTrans,
          $this->tableViews,
          $this->tableModules,
          $this->groupPageId,
          $lngId
        );
      }
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $row['module_file'] = $row['module_path'].$row['module_file'];
          $this->data[$row['box_id']] = $row;
        }
      } else {
        return FALSE;
      }
    }
    return TRUE;
  }


  /**
   * Load data list
   *
   * @param integer $lngId language id
   * @param integer $viewModeId view mode id
   * @param array $boxIds
   * @internal param int $boxId view mode id
   * @access public
   * @return bool
   */
  function loadDataElements($lngId, $viewModeId, $boxIds) {
    $this->data = array();
    if (is_array($boxIds) && count($boxIds) > 0) {
      $filter = $this->databaseGetSQLCondition('b.box_id', $boxIds);
    } else {
      $filter = $this->databaseGetSQLCondition('b.box_id', (int)$boxIds);
    }
    if ($viewModeId > 0) {
      $sql = "SELECT b.box_id, b.box_name, b.boxgroup_id,
                     b.box_deliverymode,
                     b.box_cachemode, b.box_cachetime,
                     b.box_expiresmode, b.box_expirestime,
                     bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s b,
                     %s bt,
                     %s v,
                     %s vl,
                     %s m
               WHERE $filter
                 AND bt.box_id = b.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND vl.view_id = bt.view_id
                 AND vl.viewmode_id = %d
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'";
      $params = array(
        $this->tableBox,
        $this->tableBoxTrans,
        $this->tableViews,
        $this->tableViewLinks,
        $this->tableModules,
        $lngId,
        $viewModeId
      );
    } else {
      $sql = "SELECT b.box_id, b.box_name, b.boxgroup_id,
                     b.box_deliverymode,
                     b.box_cachemode, b.box_cachetime,
                     b.box_expiresmode, b.box_expirestime,
                     bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s b,
                     %s bt,
                     %s v,
                     %s m
               WHERE $filter
                 AND bt.box_id = b.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'";
      $params = array(
        $this->tableBox,
        $this->tableBoxTrans,
        $this->tableViews,
        $this->tableModules,
        $lngId
      );
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['module_file'] = $row['module_path'].$row['module_file'];
        $this->data[$row['box_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  private function getBoxReference($box) {
    /**
     * @var \Papaya\UI\Reference $reference
     */
    $reference = $this->papaya()->references->byString('');
    $parameters = $reference->getParameters();
    $parameters['__cms_box_id'] = $box['box_id'];
    $reference->setParameters($parameters);
    return $reference;
  }

  private function getEsiCode($box) {
    $result = $this->getBoxMetaElement($box);
    $result .= sprintf(
      '<data><![CDATA[<esi:include src="%s" />]]></data>',
      \Papaya\Utility\Text\XML::escapeAttribute($this->getBoxReference($box)->getRelative())
    );
    $result .= '</box>';
    return $result;
  }

  private function getJavascriptCode($box) {
    $result = $this->getBoxMetaElement($box);
    $result .= sprintf(
      '<data><![CDATA[<div data-fragment-replace="%s"> </div>]]></data>',
      \Papaya\Utility\Text\XML::escapeAttribute($this->getBoxReference($box)->get())
    );
    $result .= '</box>';
    return $result;
  }

  private function getBoxMetaElement($data) {
    return sprintf(
      '<box title="%s" group="%s" guid="%s" module="%s" view="%s">',
      papaya_strings::escapeHTMLChars($data['box_title']),
      empty($this->boxGroupsList[$data['boxgroup_id']]['boxgroup_name'])
        ? ''
        : papaya_strings::escapeHTMLChars(
          $this->boxGroupsList[$data['boxgroup_id']]['boxgroup_name']
        ),
      papaya_strings::escapeHTMLChars($data['module_guid']),
      papaya_strings::escapeHTMLChars($data['module_class']),
      papaya_strings::escapeHTMLChars($data['view_name'])
    );
  }

  /**
  * Parse box ( for output )
  *
  * @param array $data Data of Box loaded by loadDataList()
  * @param integer $lngId Language ID
  * @param integer $boxId ID of actual Box
  * @param integer $viewModeId ID of ViewMode
  * @param bool $cache default TRUE
  * @param bool $wrapperTags add wrapper tags with meta data
  * @access public
  * @return string '' or XML
  */
  function parsedBox($data, $lngId, $boxId, $viewModeId, $cache = TRUE, $wrapperTags = TRUE) {
    $result = '';
    // Load Boxmodul defined in $data
    $obj = $this->papaya()->plugins->get(
      $data['module_guid'],
      $this->parentObj,
      $data['box_data']
    );
    // success?
    if (isset($obj) && is_object($obj)) {
      $obj->boxId = $boxId;
      $obj->languageId = $lngId;
      //get cache time for box
      $cacheForTime = $this->getBoxCacheTime($data);
      // use cache and loading of cache OK???
      if ($cache) {
        $str = $this->loadBoxCache(
          $cacheForTime, $data['box_trans_modified'], $data, $obj, $lngId, $viewModeId
        );
      } else {
        $str = '';
      }
      if (!empty($str)) {
        // Create Box-XML from Cachefile
        if ($wrapperTags) {
          $result .= $this->getBoxMetaElement($data);
          $xmlTree = \Papaya\XML\Document::createFromXML('<box>'.$str.'</box>', TRUE);
          if ($xmlTree && isset($xmlTree->documentElement)) {
            $result .= $str;
          } else {
            $result .= '<data>'.$this->cdataSection($str).'</data>';
          }
          unset($xmlTree);
          $result .= '</box>'.LF;
        } else {
          $result .= $str;
        }
      } else {
        $this->parser->setLinkOutputMode(NULL);
        // If no cache data available, load box data from XML stored in $data[box_data]
        if ($obj instanceof \Papaya\Plugin\Appendable) {
          $dom = new \Papaya\XML\Document();
          $boxNode = $dom->appendElement('box');
          $sandbox = $this->papaya()->messages->encapsulate(array($obj, 'appendTo'));
          call_user_func($sandbox, $boxNode);
          $str = $boxNode->saveFragment();
        } elseif ($obj instanceof base_actionbox) {
          $sandbox = $this->papaya()->messages->encapsulate(array($obj, 'getParsedData'));
          $str = call_user_func($sandbox);
        }
        if (!empty($str)) {
          $output = '';
          if ($wrapperTags) {
            if ($obj instanceof \Papaya\Plugin\Assignable) {
              $output = $this->serializeParsedAttributes($obj->getAttributes());
            } elseif (method_exists($obj, 'getParsedAttributes')) {
              $output = $this->serializeParsedAttributes($obj->getParsedAttributes());
            }
          }
          $outputString = '';
          if ($viewModeId > 0) {
            if ($data['module_useoutputfilter']) {
              $outputObj = new papaya_output;
              if ($outputObj->loadViewModeData('', $viewModeId)) {
                if ($filter = $outputObj->getFilter($data['view_id'])) {
                  if ($filter->checkConfiguration('box')) {
                    if (isset($filter->data['link_outputmode'])) {
                      $this->parser->setLinkOutputMode($filter->data['link_outputmode']);
                    }
                    $str = $this->parser->parse($str, $lngId);
                    if ($boxOutput = $filter->parseBox($this->parentObj, $data, $str)) {
                      $output .= '<data>'.$this->cdataSection($boxOutput).'</data>';
                      if ($cacheForTime > 0) {
                        $this->writeBoxCache(
                          $data, $obj, $lngId, $viewModeId, $output, $cacheForTime
                        );
                      }
                      if ($wrapperTags) {
                        $outputString = $output;
                      } else {
                        $outputString = $boxOutput;
                      }
                    } else {
                      $outputString = $output;
                    }
                  } else {
                    $outputString = $this->cdataSection('<!--'.$filter->errorMessage.'-->');
                  }
                }
              }
              unset($outputObj);
            } else {
              $str = $this->parser->parse($str, $lngId);
              if ($wrapperTags) {
                $outputString = $output.'<data>'.$this->cdataSection($str).'</data>';
              } else {
                $outputString = $output.$str;
              }
              if ($cacheForTime > 0) {
                $this->writeBoxCache(
                  $data, $obj, $lngId, $viewModeId, $outputString, $cacheForTime
                );
              }
            }
          } else {
            $str = $this->parser->parse($str, $lngId);
            if (!$wrapperTags) {
              $outputString = $str;
            } else {
              $xmlTree = \Papaya\XML\Document::createFromXML('<box>'.$str.'</box>', TRUE);
              if ($xmlTree) {
                $outputString = $output.'<data type="xml">'.$str.'</data>';
              } else {
                $outputString = $output.'<data>'.$this->cdataSection($str).'</data>';
              }
            }
            unset($xmlTree);
          }
          if ($outputString) {
            if ($wrapperTags) {
              $result .= sprintf(
                '<box title="%s" group="%s" guid="%s" module="%s" view="%s">',
                papaya_strings::escapeHTMLChars($data['box_title']),
                papaya_strings::escapeHTMLChars(
                  empty($this->boxGroupsList[$data['boxgroup_id']]['boxgroup_name'])
                    ? ''
                    : $this->boxGroupsList[$data['boxgroup_id']]['boxgroup_name']
                ),
                papaya_strings::escapeHTMLChars($data['module_guid']),
                papaya_strings::escapeHTMLChars($data['module_class']),
                papaya_strings::escapeHTMLChars(isset($data['view_name']) ? $data['view_name'] : '')
              );
              $result .= $outputString;
              $result .= '</box>'.LF;
            } else {
              $result .= $outputString;
            }
          }
          unset($outputString);
        }
      }
      unset($obj);
    }
    return $result;
  }

  /**
   * put a string into a cdata section with minimal escaping
   * @param $str
   * @return string
   */
  function cdataSection($str) {
    $result = str_replace(array('<![CDATA[', ']]>'), array('&lt;![CDATA[', ']]&gt;'), $str);
    return '<![CDATA['.$result.']]>';
  }

  /**
  * Serialize attribute array to xml string
  * @param array|Traversable $attributes
  * @return string
  */
  function serializeParsedAttributes($attributes) {
    $result = '';
    if ($attributes instanceof Traversable) {
      $attributes = iterator_to_array($attributes);
    }
    if (isset($attributes) && is_array($attributes) && count($attributes) > 0) {
      $result = '<attributes>';
      foreach ($attributes as $name => $value) {
        $result .= sprintf(
          '<attribute name="%s" value="%s" />',
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($value)
        );
      }
      $result .= '</attributes>';
    }
    return $result;
  }

  /**
   * get cache id of box
   *
   * @see papaya_publictopic::getContentCacheId() for a similar function for pages
   *
   * @param array $boxData
   * @param object $box
   * @param integer $lngId language id
   * @param integer $viewModeId view mode id
   * @access public
   * @return mixed string box cache id or boolean FALSE
   */
  function getBoxCacheId(array $boxData, $box, $lngId, $viewModeId) {
    if (isset($box) && is_object($box) && $this->papaya()->front->public) {
      if ($box instanceof \Papaya\Plugin\Cacheable) {
        $definition = $box->cacheable();
      } elseif (method_exists($box, 'getCacheId')) {
        $definition = new Cache\Identifier\Definition\Callback(array($box, 'getCacheId'));
      } else {
        return FALSE;
      }
      $definition = new Cache\Identifier\Definition\Group(
        $definition,
        new Cache\Identifier\Definition\Values(
          \Papaya\Utility\Server\Protocol::get(),
          \Papaya\Utility\Server\Name::get(),
          \Papaya\Utility\Server\Port::get(),
          $lngId,
          $viewModeId
        )
      );
      if ($status = $definition->getStatus()) {
        return 'box_'.((int)$boxData['box_id']).'_'.md5(serialize($status)).'.output';
      }
    }
    return FALSE;
  }

  /**
  * Returns the cache time of the box in seconds.
  *
  * @param array $box
  * @return integer
  */
  function getBoxCacheTime($box) {
    if (defined('PAPAYA_CACHE_BOXES') && PAPAYA_CACHE_BOXES) {
      switch ($box['box_cachemode']) {
      case 1 :
        //system cache time
        if (defined('PAPAYA_CACHE_TIME_BOXES') && PAPAYA_CACHE_TIME_BOXES > 0) {
          return (int)PAPAYA_CACHE_TIME_BOXES;
        }
        break;
      case 2 :
        if ($box['box_cachetime'] > 0) {
          return (int)$box['box_cachetime'];
        }
      }
    }
    return 0;
  }

  /**
   *
   *
   * @param integer $cacheForTime
   * @param integer $ifModfiedSince
   * @param array $boxData
   * @param object $box
   * @param integer $lngId language id
   * @param integer $viewModeId view mode id
   * @access public
   * @return string $str
   */
  function loadBoxCache($cacheForTime, $ifModfiedSince, $boxData, $box, $lngId, $viewModeId) {
    if ($cacheForTime > 0) {
      if ($cacheId = $this->getBoxCacheId($boxData, $box, $lngId, $viewModeId)) {
        $cache = Cache::getService($this->papaya()->options);
        return $cache->read('boxes', $boxData['box_id'], $cacheId, $cacheForTime, $ifModfiedSince);
      }
    }
    return FALSE;
  }

  /**
  * Write box cache
  *
  * @param array $boxData
  * @param object $box
  * @param integer $lngId language id
  * @param integer $viewModeId
  * @param $str
  * @param integer $expires
  * @access public
  * @return bool FALSE
  */
  function writeBoxCache($boxData, $box, $lngId, $viewModeId, $str, $expires) {
    if ($str != '' && $cacheId = $this->getBoxCacheId($boxData, $box, $lngId, $viewModeId)) {
      $cache = Cache::getService($this->papaya()->options);
      return $cache->write('boxes', $boxData['box_id'], $cacheId, $str, $expires);
    }
    return FALSE;
  }

  /**
   * Parse function
   *
   * @param integer $id Topic id (id of page)
   * @param integer $lngId Language id
   * @param integer $viewModeId ID of viewmode
   * @param null|array $boxIds
   * @param bool $contentOnly (switches return to an array())
   * @access public
   * @return string | array '' or XML or array(box_id => content)
   */
  function parsed($id, $lngId, $viewModeId, $boxIds = NULL, $contentOnly = FALSE) {
    $result = '';
    $this->parser = new papaya_parser;
    $this->parser->tableTopics = $this->tableTopics;
    $this->parser->tableTopicsTrans = $this->tableTopicsTrans;
    $this->setPageId($id, NULL);
    $this->loadBoxGroupList();
    $loaded = $this->load($lngId, $viewModeId, $boxIds);
    if ($loaded) {
      //preload view data
      $viewIds = array();
      foreach ($this->data as $box) {
        $viewIds[] = $box['view_id'];
      }
      $outputObj = new papaya_output;
      $outputObj->preloadViewLinkData($viewModeId, $viewIds);
      //get box content
      if ($contentOnly) {
        foreach ($this->data as $boxId => $box) {
          $result[$boxId] = $this->parsedBox($box, $lngId, $boxId, $viewModeId, TRUE, FALSE);
        }
      } else {
        foreach ($this->data as $boxId => $box) {
          switch ($box['box_deliverymode']) {
          case \Papaya\CMS\Content\Box::DELIVERY_MODE_JAVASCRIPT :
            $result .= $this->getJavascriptCode($box, $lngId, $boxId);
            break;
          case \Papaya\CMS\Content\Box::DELIVERY_MODE_ESI :
            if ($this->papaya()->request->allowEsi()) {
              $result .= $this->getEsiCode($box, $lngId, $boxId);
            } else {
              $result .= $this->parsedBox($box, $lngId, $boxId, $viewModeId, TRUE, TRUE);
            }
            break;
          default :
            $result .= $this->parsedBox($box, $lngId, $boxId, $viewModeId, TRUE, TRUE);
          }
        }
      }
    }
    unset($this->parser);
    return $result;
  }

  /**
   * Loade the box data from database
   *
   * @param integer $lngId
   * @param integer $viewModeId
   * @param array $boxIds
   * @return bool
   */
  public function load($lngId, $viewModeId = 0, array $boxIds = NULL) {
    $checksum = serialize(array($this->topicId, $this->groupPageId, $lngId, $viewModeId, $boxIds));
    if ($checksum != $this->_checksum) {
      if (!empty($boxIds)) {
        $loaded = $this->loadDataElements($lngId, $viewModeId, $boxIds);
      } else {
        $loaded = $this->loadDataList($lngId, $viewModeId);
      }
      if ($loaded) {
        $this->_checksum = $checksum;
      }
    } else {
      $loaded = TRUE;
    }
    return $loaded;
  }

  /**
   * Just implement the interface, this will be redefinined in the plublic boxes list
   *
   * @param Cache\Identifier\Definition $definition
   * @return Cache\Identifier\Definition
   */
  public function cacheable(Cache\Identifier\Definition $definition = NULL) {
    return new Cache\Identifier\Definition\BooleanValue(FALSE);
  }
}

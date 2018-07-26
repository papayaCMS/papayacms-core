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

namespace Papaya;
/**
 * Access and handle domain information for the current request
 *
 * @package Papaya-Library
 * @subpackage Domains
 */
class Domains extends \PapayaObject {

  /**
   * @var array
   */
  private $_domains = NULL;
  /**
   * @var array
   */
  private $_domainsByRootId = NULL;
  /**
   * @var array
   */
  private $_domainsByName = NULL;

  /**
   * @var boolean
   */
  private $_loaded = FALSE;
  /**
   * @var array
   */
  private $_current = NULL;

  /**
   * Return domains with virtual roots for the given path of page ids.
   *
   * @param array $pageRootIds
   * @return array
   */
  public function getDomainsByPath(array $pageRootIds) {
    $this->loadLazy();
    $result = array();
    foreach ($pageRootIds as $pageRootId) {
      if (isset($this->_domainsByRootId[$pageRootId])) {
        $result = \PapayaUtilArray::merge($result, $this->_domainsByRootId[$pageRootId]);
      }
    }
    return $result;
  }

  /**
   * Return the domain that matches the given host
   *
   * @param string $host
   * @param integer $scheme
   * @return array|FALSE
   */
  public function getDomainByHost($host, $scheme) {
    $this->loadLazy();
    $result = FALSE;
    $variants = $this->getHostVariants($host);
    foreach ($variants as $name) {
      if (isset($this->_domainsByName[$name])) {
        foreach ($this->_domainsByName[$name] as $domain) {
          if ($domain['scheme'] == $scheme) {
            return $domain;
          } elseif ($domain['scheme'] == \PapayaUtilServerProtocol::BOTH && !$result) {
            $result = $domain;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Get the domain for the current request if here is one. The result is cached in
   * a member variable.
   *
   * return array|FALSE
   */
  public function getCurrent() {
    if (isset($this->_current)) {
      return $this->_current;
    }
    $this->_current = $this->getDomainByHost(
      \PapayaUtilServerName::get(),
      \PapayaUtilServerProtocol::isSecure()
        ? \PapayaUtilServerProtocol::HTTPS : \PapayaUtilServerProtocol::HTTP
    );
    return $this->_current;
  }

  /**
   * Get host variants by replacing the parts of a host name with wildcards. The result
   * is a list with any variant of a subdomain and tld replaced by *.
   *
   * @param string $host
   * @return array
   */
  public function getHostVariants($host) {
    $host = strtolower($host);
    $hostParts = explode('.', $host);
    $result[] = '*';
    //does it have more then two parts?
    if (is_array($hostParts) && count($hostParts) > 1) {
      $hostParts = array_reverse($hostParts);
      //last to parts of the hostname to the buffer
      $buffer = $hostParts[0];
      $tldLength = strlen($hostParts[0]);
      for ($i = 1; $i < count($hostParts); $i++) {
        //prefix hostname parts in buffer with a "*." and replace tld with *
        if ($i > 1) {
          $result[] = '*.'.substr($buffer, 0, -1 * $tldLength).'*';
          $result[] = $hostParts[$i].'.'.substr($buffer, 0, -1 * $tldLength).'*';
        }
        //prefix hostname parts in buffer with a "*."
        $result[] = '*.'.$buffer;
        if ($i == 1) {
          $result[] = $hostParts[$i].'.'.substr($buffer, 0, -1 * $tldLength).'*';
        }
        //add hostname part to the buffer
        $buffer = $hostParts[$i].'.'.$buffer;
      }
    }
    $result[] = $host;
    return array_reverse($result);
  }

  /**
   * Lazy load the domain informations and create index array for fast access.
   *
   * @param boolean $reset
   */
  public function loadLazy($reset = FALSE) {
    if ($reset || !$this->_loaded) {
      $this->domains()->load();
      foreach ($this->domains() as $domainId => $domain) {
        if ($domain['mode'] == \Papaya\Content\Domain::MODE_VIRTUAL_DOMAIN) {
          $this->_domainsByRootId[(int)$domain['data']][$domainId] = $domain;
        } elseif ($domain['mode'] == \Papaya\Content\Domain::MODE_DEFAULT ||
          $domain['mode'] == \Papaya\Content\Domain::MODE_REDIRECT_LANGUAGE) {
          $this->_domainsByRootId[0][$domainId] = $domain;
        }
        $this->_domainsByName[$domain['host']][$domainId] = $domain;
      }
      $this->_loaded = TRUE;
    }
  }

  /**
   * Getter/Setter for the domain database object.
   *
   * @param \Papaya\Content\Domains $domains
   * @return \Papaya\Content\Domains
   */
  public function domains(\Papaya\Content\Domains $domains = NULL) {
    if (isset($domains)) {
      $this->_domains = $domains;
    } elseif (is_null($this->_domains)) {
      $this->_domains = new \Papaya\Content\Domains();
      $this->_domains->papaya($this->papaya());
    }
    return $this->_domains;
  }

  public function isStartPage(\PapayaUiReferencePage $page) {
    $targetDomain = $this->getDomainByHost(
      $page->url()->getHost(),
      $page->url()->getScheme() == 'https'
        ? \PapayaUtilServerProtocol::HTTPS : \PapayaUtilServerProtocol::HTTP
    );
    $pageId = isset($targetDomain['options']['PAPAYA_PAGEID_DEFAULT']) ?
      $targetDomain['options']['PAPAYA_PAGEID_DEFAULT'] : $this->papaya()->options['PAPAYA_PAGEID_DEFAULT'];
    $outputMode = $this->papaya()->options['PAPAYA_URL_EXTENSION'];
    if ($targetDomain['language_id'] > 0) {
      $languageId = $targetDomain['language_id'];
    } elseif (!empty($targetDomain['options']['PAPAYA_CONTENT_LANGUAGE'])) {
      $languageId = $targetDomain['options']['PAPAYA_CONTENT_LANGUAGE'];
    } else {
      $languageId = $this->papaya()->options['PAPAYA_CONTENT_LANGUAGE'];
    }
    if (!($language = $this->papaya()->languages->getLanguage($languageId))) {
      return FALSE;
    }
    return (
      ($pageId == $page->getPageId()) &&
      ($language['identifier'] == $page->getPageLanguage()) &&
      ($outputMode == $page->getOutputMode())
    );
  }
}

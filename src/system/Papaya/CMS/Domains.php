<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS {

  use Papaya\Application;
  use Papaya\CMS\Content\Domains as DomainsContent;
  use Papaya\CMS\Reference\Page as PageReference;
  use Papaya\Utility;

  /**
   * Access and handle domain information for the current request
   *
   * @package Papaya-Library
   * @subpackage Domains
   */
  class Domains implements Application\Access {
    use Application\Access\Aggregation;

    /**
     * @var array
     */
    private $_domains;

    /**
     * @var array
     */
    private $_domainsByRootId;

    /**
     * @var array
     */
    private $_domainsByName;

    /**
     * @var bool
     */
    private $_loaded = FALSE;

    /**
     * @var array
     */
    private $_current;

    /**
     * Return domains with virtual roots for the given path of page ids.
     *
     * @param array $pageRootIds
     *
     * @return array
     */
    public function getDomainsByPath(array $pageRootIds) {
      $this->loadLazy();
      $result = [];
      foreach ($pageRootIds as $pageRootId) {
        if (isset($this->_domainsByRootId[$pageRootId])) {
          $result = Utility\Arrays::merge($result, $this->_domainsByRootId[$pageRootId]);
        }
      }
      return $result;
    }

    /**
     * Return the domain that matches the given host
     *
     * @param string $host
     * @param int $scheme
     *
     * @return array|false
     */
    public function getDomainByHost($host, $scheme) {
      $this->loadLazy();
      $result = FALSE;
      $variants = $this->getHostVariants($host);
      foreach ($variants as $name) {
        if (isset($this->_domainsByName[$name])) {
          foreach ($this->_domainsByName[$name] as $domain) {
            if ((int)$domain['scheme'] === $scheme) {
              return $domain;
            }
            if (!$result && Utility\Server\Protocol::BOTH === (int)$domain['scheme']) {
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
      if (NULL !== $this->_current) {
        return $this->_current;
      }
      $this->_current = $this->getDomainByHost(
        Utility\Server\Name::get(),
        Utility\Server\Protocol::isSecure()
          ? Utility\Server\Protocol::HTTPS : Utility\Server\Protocol::HTTP
      );
      return $this->_current;
    }

    /**
     * Get host variants by replacing the parts of a host name with wildcards. The result
     * is a list with any variant of a subdomain and tld replaced by *.
     *
     * @param string $host
     *
     * @return array
     */
    public function getHostVariants($host) {
      $host = \strtolower($host);
      $hostParts = \explode('.', $host);
      $result[] = '*';
      //does it have more then two parts?
      if (\is_array($hostParts) && \count($hostParts) > 1) {
        $hostParts = \array_reverse($hostParts);
        //last to parts of the hostname to the buffer
        $buffer = $hostParts[0];
        $tldLength = \strlen($hostParts[0]);
        for ($i = 1, $c = \count($hostParts); $i < $c; $i++) {
          //prefix hostname parts in buffer with a "*." and replace tld with *
          if ($i > 1) {
            $result[] = '*.'.\substr($buffer, 0, -1 * $tldLength).'*';
            $result[] = $hostParts[$i].'.'.\substr($buffer, 0, -1 * $tldLength).'*';
          }
          //prefix hostname parts in buffer with a "*."
          $result[] = '*.'.$buffer;
          if (1 === $i) {
            $result[] = $hostParts[$i].'.'.\substr($buffer, 0, -1 * $tldLength).'*';
          }
          //add hostname part to the buffer
          $buffer = $hostParts[$i].'.'.$buffer;
        }
      }
      $result[] = $host;
      return \array_reverse($result);
    }

    /**
     * Lazy load the domain information and create index array for fast access.
     *
     * @param bool $reset
     */
    public function loadLazy($reset = FALSE) {
      if ($reset || !$this->_loaded) {
        $this->domains()->load();
        foreach ($this->domains() as $domainId => $domain) {
          if (Content\Domain::MODE_VIRTUAL_DOMAIN === (int)$domain['mode']) {
            $this->_domainsByRootId[(int)$domain['data']][$domainId] = $domain;
          } elseif (
            Content\Domain::MODE_DEFAULT === (int)$domain['mode'] ||
            Content\Domain::MODE_REDIRECT_LANGUAGE === (int)$domain['mode']) {
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
     * @param DomainsContent|NULL $domains
     * @return DomainsContent
     */
    public function domains(DomainsContent $domains = NULL): DomainsContent {
      if (NULL !== $domains) {
        $this->_domains = $domains;
      } elseif (NULL === $this->_domains) {
        $this->_domains = new DomainsContent();
        $this->_domains->papaya($this->papaya());
      }
      return $this->_domains;
    }

    /**
     * @param PageReference $page
     * @return bool
     */
    public function isStartPage(PageReference $page) {
      $targetDomain = $this->getDomainByHost(
        $page->url()->getHost(),
        'https' === $page->url()->getScheme()
          ? Utility\Server\Protocol::HTTPS : Utility\Server\Protocol::HTTP
      );
      $pageId = isset($targetDomain['options']['PAPAYA_PAGEID_DEFAULT'])
        ? $targetDomain['options']['PAPAYA_PAGEID_DEFAULT']
        : $this->papaya()->options['PAPAYA_PAGEID_DEFAULT'];
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
        ((int)$pageId === (int)$page->getPageId()) &&
        ($language['identifier'] === $page->getPageLanguage()) &&
        ($outputMode === $page->getOutputMode())
      );
    }
  }
}

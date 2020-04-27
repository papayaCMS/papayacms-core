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

namespace Papaya\Administration\Protocol\Commands {

  use Papaya\Request\Parameters as RequestParameters;
  use Papaya\UI\ListView;
  use Papaya\Content\Protocol\ProtocolEntry;
  use Papaya\UI\Control\Command as UICommandControl;
  use Papaya\UI\Sheet;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\URL;
  use Papaya\Utility\File\Path as PathUtilities;
  use Papaya\Utility\Text\UTF8 as UTF8String;
  use Papaya\Utility\Text\XML as XMLUtilities;
  use Papaya\XML\Element;

  class ShowProtocolEntry extends UICommandControl {

    /**
     * @var ProtocolEntry
     */
    private $_protocolEntry;

    public function __construct(ProtocolEntry $protocolEntry) {
      $this->_protocolEntry = $protocolEntry;
    }

    public function appendTo(Element $parent) {
      if ($this->_protocolEntry->id > 0) {
        $listView = new ListView();
        $listView->caption = new TranslatedText('Details');
        $listView->items[] = $item = new ListView\Item(
          '', new TranslatedText('Time')
        );
        $item->subitems[] = new ListView\SubItem\Date((int)$this->_protocolEntry->createdAt);
        $this->appendURLDetails($listView, 'URL', $this->_protocolEntry->requestURL);
        $this->appendURLDetails($listView, 'Referer', $this->_protocolEntry->refererURL);
        $this->appendURLDetails($listView, 'Cookies', $this->_protocolEntry->cookies);
        $this->appendStringDetails($listView, 'Script', $this->_protocolEntry->script);
        $this->appendHostDetails($listView, 'Host', $this->_protocolEntry->clientIP);
        $this->appendStringDetails($listView, 'IP', $this->_protocolEntry->clientIP);
        $this->appendStringDetails($listView, 'papaya CMS Version', $this->_protocolEntry->papayaVersion);
        $this->appendStringDetails($listView, 'Project Version', $this->_protocolEntry->projectVersion);
        $this->appendStringDetails($listView, 'User ID', $this->_protocolEntry->userID);
        $this->appendStringDetails($listView, 'User Name', $this->_protocolEntry->userName);
        $parent->append($listView);
        $sheet = new Sheet();
        $sheet->padding = Sheet::PADDING_MEDIUM;
        $sheet
          ->content()
          ->appendXML(
            @XMLUtilities::repairEntities(
              $this->rewrapContent(
                $this->_protocolEntry->content
              )
            )
          );
        $parent->append($sheet);
      }
    }

    private function appendURLDetails(ListView $listView, $caption, $urlString) {
      $url = new URL($urlString);
      $listView->items[] = $item = new ListView\Item(
        '', new TranslatedText($caption)
      );
      $item->hint = $urlString;
      $item->subitems[] = new ListView\SubItem\Text($url->getPathURL());
      $parameters = new RequestParameters();
      $parameters->setQueryString($url->getQuery());
      foreach ($parameters->getList() as $name => $value) {
        $listView->items[] = $item = new ListView\Item(
          '', $name
        );
        $item->subitems[] = new ListView\SubItem\Text($value);
        $item->indentation = 1;
      }
    }

    private function appendStringDetails(ListView $listView, $caption, $text) {
      if (trim($text) !== '') {
        $listView->items[] = $item = new ListView\Item(
          '', new TranslatedText($caption)
        );
        $item->subitems[] = new ListView\SubItem\Text($text);
      }
    }


    private function appendHostDetails(ListView $listView, $caption, $ipString) {
      $hostNames = $this->getHostNames($ipString);
      if ($hostNames !== $ipString) {
        $this->appendStringDetails($listView, $caption, $hostNames);
      }
    }

    /**
    * get host names for one or more ips
    *
    * @param string $ipString
    * @return string
    */
    private function getHostNames($ipString) {
      if (FALSE !== strpos($ipString, ',')) {
        $ips = explode(',', $ipString);
        if (is_array($ips) && count($ips)) {
          $result = '';
          foreach ($ips as $ip) {
            $result .= ','.@gethostbyaddr(trim($ip));
          }
          return substr($result, 1);
        }
        return '';
      }
      return @gethostbyaddr($ipString);
    }


    /**
     * Rewrap HTML (do not break inside html tags)
     *
     * @param string $html
     * @return string
     */
    private function rewrapContent($html) {
      $pattern = '~(^|>)([^<]+)~';
      $paths = [
        'PAPAYA_INCLUDE_PATH' => dirname(__DIR__, 5),
        'DOCUMENT_ROOT' => PathUtilities::getDocumentRoot(),
      ];
      return preg_replace_callback(
        $pattern,
        static function ($match) use ($paths) {
          $width = 80;
          $break = '<span class="allowWrap"> </span>';
          $words = preg_split('~(\r\n|\n\r|[\r\n\s])~', $match[2]);
          if (is_array($words)) {
            $result = '';
            foreach ($words as $word) {
              foreach ($paths as $pathName => $path) {
                if (0 === strpos($word, $path)) {
                  $partWord = substr($word, strlen($path));
                  if (UTF8String::length($partWord) > $width) {
                    $result .= ' <em>{'.$pathName.'}</em>'.wordwrap($partWord, $width, $break, TRUE);
                  } else {
                    $result .= ' <em>{'.$pathName.'}</em>'.$partWord;
                  }
                  break 2;
                }
              }
              if (UTF8String::length($word) > $width) {
                $result .= ' '.wordwrap($word, $width, $break, TRUE);
              } else {
                $result .= ' '.$word;
              }
            }
            return $match[1].substr($result, 1);
          }
          return $match[0];
        },
        $html
      );
    }
  }
}



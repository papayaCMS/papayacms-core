<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Sequences {

  use Papaya\Template\Engine\XSLT\Namespaces;
  use Papaya\Template\Engine\XSLT\XpathError;

  abstract class External {

    private const ENCODINGS_MAP = [
      'utf-8' => \UConverter::UTF8,
      'utf-16' => \UConverter::UTF16,
      'utf-32' => \UConverter::UTF32
    ];

    public static function doc(string $href): \DOMDocument {
      $document = new \DOMDocument();
      $document->load($href);
      return $document;
    }

    public static function unparsedText(string $href, string $encoding = ''): string {
      try {
        $data = file_get_contents($href);
      } catch (\Throwable $e) {
        throw new XpathError(Namespaces::XMLNS_ERR.'#FOUT1170', 'Invalid $href argument to fn:unparsed-text(): '.$href);
      }
      if ($sourceEncoding = self::mapEncodingStringToConstant($encoding)) {
        $converter = new \UConverter(\UConverter::UTF8, $sourceEncoding);
        return $converter->convert($data, FALSE);
      }
      return $data;
    }

    public static function unparsedTextLines(string $href, string $encoding = ''): \DOMNode {
      $lines = preg_split(
        '(\r\n|\r|\n)',
        rtrim(self::unparsedText($href, $encoding))
      );
      $document = new \DOMDocument();
      $document->appendChild(
        $array = $document->createElementNS(Namespaces::XMLNS_FN, 'array')
      );
      foreach ($lines as $line) {
        $array->appendChild(
          $document->createElementNS(Namespaces::XMLNS_FN, 'string')
        )->textContent = $line;
      }
      return $document->documentElement;
    }

    private static function mapEncodingStringToConstant(string $encoding): ?int {
      return self::ENCODINGS_MAP[strtolower($encoding)] ?? NULL;
    }
  }
}

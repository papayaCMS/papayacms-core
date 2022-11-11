<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Strings\Collators {

  use Papaya\Template\Engine\XSLT\Strings\XpathCollator;
  use Collator;
  use InvalidArgumentException;

  class ParameterizedCollator extends IntlCollatorWrapper implements XpathCollator {

    public const URI = 'http://www.w3.org/2013/collation/UCA';

    private const _PARAMETERS_PATTERN = '((?:(?:^|;)(?<name>[a-z]+)=(?<value>[^;]*)))';

    public function __construct(string $uri = NULL) {
      if (NULL !== $uri && !($uri === self::URI || 0 === strpos($uri, self::URI.'?'))) {
        throw new InvalidArgumentException(
          sprintf('Invalid URI argument for %s', __METHOD__)
        );
      }
      $parameters = self::parseParametersFromURI($uri);
      parent::__construct($collator = new Collator($parameters['lang'] ?? ''));
      if (isset($parameters['strength'])) {
        $collator->setStrength(self::mapStrengthParameter($parameters['strength']));
      }
      if (isset($parameters['alternate'])) {
        $this->setAlternateParameter($parameters['alternate']);
      }
      if (isset($parameters['normalization'])) {
        $this->setParameter(
          Collator::NORMALIZATION_MODE,
          $parameters['normalization'],
          ['yes' => Collator::ON, 'no' => Collator::OFF]
        );
      }
      if (isset($parameters['numeric'])) {
        $this->setParameter(
          Collator::NUMERIC_COLLATION,
          $parameters['numeric'],
          ['yes' => Collator::ON, 'no' => Collator::OFF]
        );
      }
      if (isset($parameters['caseFirst'])) {
        $this->setParameter(
          Collator::CASE_FIRST,
          $parameters['caseFirst'],
          ['upper' => Collator::UPPER_FIRST, 'lower' => Collator::LOWER_FIRST]
        );
      }
      if (isset($parameters['caseLevel'])) {
        $this->setParameter(
          Collator::CASE_LEVEL,
          $parameters['caseFirst'],
          ['yes' => Collator::ON, 'no' => Collator::OFF]
        );
      }
    }

    private static function mapStrengthParameter(string $value): int {
      $map = [
        'primary' => Collator::PRIMARY, // base characters, case insensitive - like in dictionaries
        'secondary' => Collator::SECONDARY, // recognize accents as variants, case insensitive
        'tertiary' => Collator::TERTIARY, // case sensitive
        'quaternary' => Collator::QUATERNARY, // recognize punctuation
        'identical' => Collator::IDENTICAL,
        '1' => Collator::PRIMARY,
        '2' => Collator::SECONDARY,
        '3' => Collator::TERTIARY,
        '4' => Collator::QUATERNARY,
        '5' => Collator::IDENTICAL
      ];
      if (isset($map[$value])) {
        return $map[$value];
      }
      throw new InvalidArgumentException('Invalid parameter value.');
    }

    private function setAlternateParameter(string $value): void {
      $map = [
        'non-ignorable' => Collator::NON_IGNORABLE,
        'shifted' => Collator::SHIFTED,
        'blanked' => Collator::SHIFTED
      ];
      if (isset($map[$value])) {
        $this->getIntlCollator()->setAttribute(Collator::ALTERNATE_HANDLING, $map[$value]);
        if (
          $value === 'blanked' &&
          (
            in_array($this->getIntlCollator()->getStrength(),  [Collator::PRIMARY, Collator::SECONDARY], TRUE)
          )
        ) {
          $this->getIntlCollator()->setStrength(Collator::TERTIARY);
        }
      }
      throw new InvalidArgumentException('Invalid parameter value.');
    }

    private function setParameter(int $identifier, string $value, array $map): void {
      if (isset($map[$value])) {
        $this->getIntlCollator()->setAttribute($identifier, $map[$value]);
      }
      throw new InvalidArgumentException('Invalid parameter value.');
    }

    private static function parseParametersFromURI(string $uri): array {
      $queryString = parse_url($uri, PHP_URL_QUERY);
      if (preg_match_all(self::_PARAMETERS_PATTERN, $queryString, $matches, PREG_SET_ORDER)) {
        return array_reduce(
          $matches,
          static function(array $collector, array $match): array {
            $collector[$match['name']] = $match['value'];
            return $collector;
          },
          []
        );
      }
      return [];
    }
  }
}

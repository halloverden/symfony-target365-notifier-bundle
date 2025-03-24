<?php

namespace HalloVerden\Target365NotifierBundle\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Target365\ApiSdk\Exception\ApiClientException;

final class Target365TransportFactory extends AbstractTransportFactory {
  public const SCHEME = 'target365';

  public function __construct(
    ?EventDispatcherInterface $dispatcher = null,
    ?HttpClientInterface $client = null,
    private readonly ?LoggerInterface $logger = null
  ) {
    parent::__construct($dispatcher, $client);
  }

  /**
   * @inheritDoc
   */
  protected function getSupportedSchemes(): array {
    return [self::SCHEME];
  }

  /**
   * @inheritDoc
   * @throws ApiClientException
   */
  public function create(Dsn $dsn): TransportInterface {
    $scheme = $dsn->getScheme();

    if (self::SCHEME !== $scheme) {
      throw new UnsupportedSchemeException($dsn, self::SCHEME, $this->getSupportedSchemes());
    }

    $keyName = $this->getUser($dsn);
    $privateKey = $this->getPassword($dsn);
    $from = $dsn->getOption('from', '');
    $allowUnicode = $dsn->getOption('allowUnicode') === 'true';
    $allowFakePhoneNumbers = $dsn->getOption('allowFakePhoneNumbers') === 'true';
    $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
    $port = $dsn->getPort();

    return new Target365Transport($keyName, $privateKey, $from, $allowUnicode, $allowFakePhoneNumbers, $host, $port, $this->logger, $this->client, $this->dispatcher);
  }

}

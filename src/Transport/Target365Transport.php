<?php

namespace HalloVerden\Target365NotifierBundle\Transport;

use GuzzleHttp\Exception\GuzzleException;
use HalloVerden\Target365NotifierBundle\Options\Target365Options;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Target365\ApiSdk\ApiClient;
use Target365\ApiSdk\Exception\ApiClientException;
use Target365\ApiSdk\Model\OutMessage;
use Target365\ApiSdk\Model\Properties;

final class Target365Transport extends AbstractTransport {
  protected const HOST = 'shared.target365.io';

  private const FAKE_PHONE_NUMBERS = [
    '+4700000001', // Ok - Delivered
    '+4700000010', // Failed - Undelivered
    '+4700000020', // Failed - SubscriberBarred
  ];

  private ApiClient $apiClient;

  /**
   * Target365Transport constructor.
   * @throws ApiClientException
   */
  public function __construct(
    string $keyName,
    string $privateKey,
    private readonly string $from = '',
    private readonly bool $allowUnicode = false,
    private readonly bool $allowFakePhoneNumbers = false,
    ?string $host = null,
    ?string $port = null,
    ?LoggerInterface $logger = null,
    ?HttpClientInterface $client = null,
    EventDispatcherInterface $dispatcher = null
  ) {
    parent::__construct($client, $dispatcher);
    $this->setHost($host)->setPort($port);
    $this->apiClient = new ApiClient('https://' . $this->getEndpoint(), $keyName, $privateKey, $logger);
  }

  /**
   * @throws ApiClientException
   * @throws GuzzleException
   */
  protected function doSend(MessageInterface $message): SentMessage {
    if (!$message instanceof SmsMessage) {
      throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
    }

    $options = $message->getOptions() ?? new Target365Options();
    if (!$options instanceof Target365Options) {
      throw new LogicException(\sprintf('options passed to "%s", must be instance of "%s"', __CLASS__, Target365Options::class));
    }

    $from = $message->getFrom() ?: $this->from;

    $outMessage = (new OutMessage())
      ->setTransactionId($options->getTransactionIdPrefix() ? $options->getTransactionIdPrefix() . microtime(true) : uniqid((string) time(), true))
      ->setAllowUnicode($this->allowUnicode)
      ->setSender($from)
      ->setRecipient($this->getFormattedPhoneNumber($message))
      ->setContent($message->getSubject())
      ->setTags($options->getTags());

    if (!empty($properties = $options->getProperties())) {
      $outMessage->setProperties(new Properties($properties));
    }

    $messageId = $this->apiClient->outMessageResource()->post($outMessage);

    $sentMessage = new SentMessage($message, (string) $this);
    $sentMessage->setMessageId($messageId);
    return $sentMessage;
  }

  public function supports(MessageInterface $message): bool {
    return $message instanceof SmsMessage && (null === $message->getOptions() || $message->getOptions() instanceof Target365Options);
  }

  public function __toString(): string {
    return \sprintf(
      '%s://%s?from=%s&allowUnicode=%s&allowFakePhoneNumbers=%s',
      Target365TransportFactory::SCHEME,
      $this->getEndpoint(),
      \urlencode($this->from),
      $this->allowUnicode ? 'true' : 'false',
      $this->allowFakePhoneNumbers ? 'true' : 'false',
    );
  }

  /**
   * @param SmsMessage $message
   *
   * @return string
   */
  private function getFormattedPhoneNumber(SmsMessage $message): string {
    if ($this->allowFakePhoneNumbers && \in_array($message->getPhone(), self::FAKE_PHONE_NUMBERS, true)) {
      return $message->getPhone();
    }

    $phoneNumberUtil = PhoneNumberUtil::getInstance();

    try {
      $phoneNumber = $phoneNumberUtil->parse($message->getPhone());
    } catch (NumberParseException $e) {
      throw new InvalidArgumentException(\sprintf('Unable to parse phone number (%s)', $message->getPhone()), previous: $e);
    }

    if (!$phoneNumberUtil->isValidNumber($phoneNumber)) {
      throw new InvalidArgumentException(\sprintf('The phone number (%s) is not valid', $message->getPhone()));
    }

    return $phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::E164);
  }

}

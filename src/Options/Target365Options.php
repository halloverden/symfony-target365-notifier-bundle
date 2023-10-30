<?php

namespace HalloVerden\Target365NotifierBundle\Options;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

final class Target365Options implements MessageOptionsInterface {
  private const OPTION_TAGS = 'tags';
  private const OPTION_PROPERTIES = 'properties';
  private const OPTION_TRANSACTION_ID_PREFIX = 'transaction_id_prefix';

  /**
   * Target365Options constructor.
   */
  public function __construct(private array $options = []) {
  }

  /**
   * @return string[]|null
   */
  public function getTags(): ?array {
    return $this->options[self::OPTION_TAGS] ?? null;
  }

  /**
   * @param string[]|null $tags
   *
   * @return $this
   */
  public function setTags(?array $tags): self {
    $this->options[self::OPTION_TAGS] = $tags;
    return $this;
  }

  /**
   * @return array|null
   */
  public function getProperties(): ?array {
    return $this->options[self::OPTION_PROPERTIES] ?? null;
  }

  /**
   * @param array|null $properties
   *
   * @return $this
   */
  public function setProperties(?array $properties): self {
    $this->options[self::OPTION_PROPERTIES] = $properties;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getTransactionIdPrefix(): ?string {
    return $this->options[self::OPTION_TRANSACTION_ID_PREFIX] ?? null;
  }

  /**
   * @param string|null $transactionIdPrefix
   *
   * @return $this
   */
  public function setTransactionIdPrefix(?string $transactionIdPrefix): self {
    $this->options[self::OPTION_TRANSACTION_ID_PREFIX] = $transactionIdPrefix;
    return $this;
  }

  public function toArray(): array {
    return $this->options;
  }

  public function getRecipientId(): ?string {
    return null;
  }

}

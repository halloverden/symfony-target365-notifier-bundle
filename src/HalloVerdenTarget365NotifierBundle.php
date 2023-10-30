<?php

namespace HalloVerden\Target365NotifierBundle;

use HalloVerden\Target365NotifierBundle\Transport\Target365TransportFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class HalloVerdenTarget365NotifierBundle extends AbstractBundle {

  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void {
    $alias = $this->getContainerExtension()->getAlias();

    $factoryServiceId = $alias . '.transport_factory';
    $container->services()
      ->set($factoryServiceId, Target365TransportFactory::class)
      ->parent('notifier.transport_factory.abstract')
      ->tag('texter.transport_factory')
    ;

    $builder->getDefinition($factoryServiceId)
      ->replaceArgument('$logger', new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
  }

}

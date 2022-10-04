<?php

namespace Drupal\entities_info\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntitiesExportController.
 */
class EntitiesInfoExportController extends ControllerBase {

  /**
   * Drupal\entities_info\EntitiesInfoManagerInterface definition.
   *
   * @var \Drupal\entities_info\EntitiesInfoManagerInterface
   */
  protected $entityInfoManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): EntitiesInfoExportController {
    $instance = parent::create($container);
    $instance->entityInfoManager = $container->get('entities_info.manager');
    return $instance;
  }

  /**
   * Export entities info to a table.
   *
   * @return array
   *   Return render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function export(): array {

    $entitiesInfoTempstore = $this->entityInfoManager->getEntitiesInfoTempstore();
    $entitiesInfoValues = $this->entityInfoManager->getValues($entitiesInfoTempstore);
    $entitiesFields = $this->entityInfoManager->getEntitiesFields($entitiesInfoValues);
    $tables = $this->entityInfoManager->createTables($entitiesFields);

    $export = [
      '#theme' => 'entities_info',
      '#tables' => $tables,
    ];

    $entitiesInfoTempstore->set('export', $export);

    return $export;
  }

}

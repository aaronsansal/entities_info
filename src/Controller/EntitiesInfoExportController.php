<?php

namespace Drupal\entities_info\Controller;

use Drupal\Core\Controller\ControllerBase;
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
   * Drupal\entities_info\EntitiesInfoGenerateTablesInterface definition.
   *
   * @var \Drupal\entities_info\EntitiesInfoGenerateTablesInterface
   */
  protected $entityInfoGenerateTables;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): EntitiesInfoExportController {
    $instance = parent::create($container);
    $instance->entityInfoManager = $container->get('entities_info.manager');
    $instance->entityInfoGenerateTables = $container->get('entities_info.generate_tables');
    $instance->tempStoreFactory = $container->get('tempstore.private');
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

    $tempstore = $this->tempStoreFactory->get('entities_info_export');
    $entitiesInfoValues = $tempstore->get('values');
    $tableType = $tempstore->get('table_type');
    $tables = $this->getExportData($tableType, $entitiesInfoValues);

    $export = [
      '#theme' => 'entities_info',
      '#tables' => $tables,
    ];

    $tempstore->set('export', $export);

    return $export;
  }

  /**
   * @param mixed $tableType
   * @param mixed $entitiesInfoValues
   * @return array|array[]|\array[][]|false[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getExportData(mixed $tableType, mixed $entitiesInfoValues): array {
    $tables = [];

    if ($tableType == 'entity_fields') {
      $entitiesFields = array_map(function ($item) {
        [$bundle, $entity_id] = explode('-ei-', $item);
        return $this->entityInfoManager->getEntityFields($entity_id, $bundle);
      }, $entitiesInfoValues);

      $tables = array_map(fn($fields) => $this->entityInfoGenerateTables->createTableEntityFields($fields), $entitiesFields);
    }

    if ($tableType == 'entities') {

    }

    return $tables;
  }

}

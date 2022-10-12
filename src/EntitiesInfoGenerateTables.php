<?php

namespace Drupal\entities_info;

/**
 * Service description.
 */
class EntitiesInfoGenerateTables implements EntitiesInfoGenerateTablesInterface {

  /**
   * {@inheritdoc}
   */
  public function createTableEntityFields(array $fields): array {
    $count = t('Count items:') . $fields['count'];
    $label = $fields['label'];
    if (count($fields) === 2 && array_key_exists('count', $fields)) {
      return [
        '#name' => $label,
        '#count' => $count,
        '#markup' => '<p>' . t('There is not fields created.') . '</p>',
      ];
    }

    unset($fields['count'], $fields['label']);
    $rows = $this->getTableRows($fields);

    return [
      '#type' => 'table',
      '#header' => $this->getTableHeaders(),
      '#rows' => $rows,
      '#name' => $label,
      '#count' => $count,
    ];
  }

  /**
   * Return table rows with field info.
   *
   * @param array $entity
   *   Entity with field configs.
   *
   * @return array
   *   Array with field info.
   */
  protected function getTableRows(array $entity): array {
    return array_map(function ($field) {
      return [
        $field['field_name'],
        $field['label'],
        $field['field_type'],
        $field['required'],
        $field['description'],
        $field['count_used'],
      ];
    }, $entity);
  }

  /**
   * Table headers.
   *
   * @return array
   *   Field information labels.
   */
  protected function getTableHeaders(): array {
    return [
      'field_name' => t('Field name'),
      'label' => t('Label'),
      'field_type' => t('Field type'),
      'required' => t('Required'),
      'description' => t('Description'),
      'count_used' => t('Count field use'),
    ];
  }

}

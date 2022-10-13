<?php

namespace Drupal\entities_info;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\field\Entity\FieldConfig;

/**
 * Manages entities info to export.
 */
class EntitiesInfoManager implements EntitiesInfoManagerInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new Entity info manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfigEntities(): array {
    $entities = $this->entityTypeManager->getDefinitions();
    $fields_map = array_keys($this->entityFieldManager->getFieldMap());
    $entities = array_filter($entities, fn($entity) => $entity->getGroup() != 'content');

    return array_filter($entities, fn($entity) => (
      in_array($entity->id(), $fields_map) || in_array($entity->getBundleOf(), $fields_map)
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundles(string $entity_id): array {
    return $this->entityTypeManager->getStorage($entity_id)->loadMultiple();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFields(string $entity_id, string $bundle): array|bool {
    $label = $this->entityTypeManager->getStorage($entity_id)->load($bundle)->label();
    $entity_id = $this->entityTypeManager->getDefinition($entity_id)->getBundleOf() ?: $entity_id;
    $fields = $this->entityFieldManager->getFieldDefinitions($entity_id, $bundle);
    $fields = array_filter($fields, fn($field) => $field instanceof FieldConfig);
    $fields['count'] = $this->getCountBundle($entity_id, $bundle);
    $fields['label'] = $label;

    return $this->getFieldInfo($fields);
  }

  /**
   * Get field name, label, type, required and description.
   *
   * @param array $fields
   *   Fields.
   *
   * @return array
   *   Array with field information.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getFieldInfo(array $fields): array {
    return array_map(function ($field) {
      if (!($field instanceof FieldConfig)) {
        return $field;
      }

      $fieldType = $this->getFieldType($field);
      $count = $this->getCountField($field);

      return [
        'field_name' => $field->getName(),
        'label' => $field->getLabel(),
        'field_type' => $fieldType,
        'required' => $field->isRequired() == 1 ? t("Yes") : t("No"),
        'description' => $field->getDescription(),
        'count_used' => $count,
      ];
    }, $fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getCountBundle(string $entity, string $bundle): array|int {
    $entity_keys = $this->entityTypeManager->getStorage($entity)->getEntityType()->get('entity_keys');
    return $this->entityTypeManager->getStorage($entity)->getQuery()
      ->condition($entity_keys['bundle'], $bundle)
      ->count()
      ->execute();
  }

  /**
   * Get the field type.
   *
   * If the type is entity reference, add target type and target bundle.
   *
   * @param \Drupal\field\Entity\FieldConfig $field
   *   Field config.
   *
   * @return string
   *   Field type.
   */
  protected function getFieldType(FieldConfig $field): string {
    $fieldType = $field->getType();
    if ($fieldType != 'entity_reference') {
      return $fieldType;
    }

    $settings = $field->getSettings();
    $target_bundle = $settings['handler_settings']['target_bundles'];

    if ($target_bundle == NULL) {
      return $fieldType;
    }

    $target_bundle = array_values($target_bundle);
    return $fieldType . ':' . $settings['target_type'] . ':' . $target_bundle[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getCountField(FieldConfig $field): string {
    if ($field->getType() == 'field_menu') {
      return '';
    }
    $entity = $field->getTargetEntityTypeId();
    $bundle = $field->getTargetBundle();
    $name = $field->getName();
    $entity_keys = $this->entityTypeManager->getStorage($entity)->getEntityType()->get('entity_keys');

    return $this->entityTypeManager->getStorage($entity)->getQuery()
      ->condition($entity_keys['bundle'], $bundle)
      ->condition($name, NULL, 'IS NOT NULL')
      ->count()
      ->execute();
  }

}

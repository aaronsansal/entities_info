<?php

namespace Drupal\entities_info\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntitiesExport.
 */
class EntitiesInfoExportForm extends FormBase {

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
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): EntitiesInfoExportForm {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityFieldManager = $container->get('entity_field.manager');
    $instance->tempStoreFactory = $container->get('tempstore.private');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'entities_info_export_form';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $entities = $this->entityTypeManager->getDefinitions();
    $fields_map = array_keys($this->entityFieldManager->getFieldMap());

    foreach ($entities as $entity) {

      if ($entity->getGroup() == 'content') {
        continue;
      }

      $entity_id = $entity->id();
      $bundle_of = $entity->getBundleOf();

      if (!in_array($entity_id, $fields_map) && !in_array($bundle_of, $fields_map)) {
        continue;
      }

      $bundle = $entity->getBundleLabel();
      $storage = $this->entityTypeManager->getStorage($entity_id)->loadMultiple();

      $form['configuration_' . $entity_id] = [
        '#type' => 'details',
        '#title' => $bundle,
        '#open' => FALSE,
      ];

      foreach ($storage as $item) {

        $id = $item->id();
        $label = $item->label();

        $form['configuration_' . $entity_id][$id . '---' . $entity_id] = [
          '#type' => 'checkbox',
          '#title' => $label . ' (' . $id . ')',
          '#weight' => '0',
        ];
      }
    }

    $form['error'] = [
      '#type' => 'hidden',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export info'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $results = $form_state->getValues();
    $values_selected = array_filter($results, fn($value) => $value === 1);

    if (!$values_selected) {
      $form_state->setError($form['error'], $this->t('Select at least one option.'));
    }

  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $results = $form_state->getValues();

    $values_selected = array_filter($results, fn($value) => $value === 1);
    $values_format = array_map(fn($index, $value) => $index, array_keys($values_selected), $values_selected);
    $values = array_combine(array_keys($values_selected), $values_format);

    $tempstore = $this->tempStoreFactory->get('entities_info_export');
    $tempstore->set('values', $values);

    $form_state->setRedirect('entities_info.entities_info_export_controller_export');
  }

}

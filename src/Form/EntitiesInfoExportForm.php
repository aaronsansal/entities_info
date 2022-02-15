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
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->tempStoreFactory = $container->get('tempstore.private');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entities_info_export_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $entities = $this->entityTypeManager->getDefinitions();

    foreach ($entities as $entity) {

      if ($entity->getGroup() == 'content') {
        continue;
      }

      $bundle = $entity->getBundleLabel();
      $entity_id = $entity->id();
      $storage = $this->entityTypeManager->getStorage($entity_id)->loadMultiple();

      $form['configuration_' . $entity_id] = [
        '#type' => 'details',
        '#title' => $this->t($bundle),
        '#open' => FALSE,
      ];

      foreach ($storage as $item) {

        $id = $item->id();
        $label = $item->label();

        $form['configuration_' . $entity_id][$id . '-' . $entity_id] = [
          '#type' => 'checkbox',
          '#title' => $label . ' (' . $id . ')',
          '#weight' => '0',
        ];
      }
    }

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
    foreach ($form_state->getValues() as $key => $value) {

    }
    parent::validateForm($form, $form_state);
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

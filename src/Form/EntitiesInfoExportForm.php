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
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * Drupal\entities_info\EntitiesInfoManagerInterface definition.
   *
   * @var \Drupal\entities_info\EntitiesInfoManagerInterface
   */
  protected $entityInfoManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): EntitiesInfoExportForm {
    $instance = parent::create($container);
    $instance->tempStoreFactory = $container->get('tempstore.private');
    $instance->entityInfoManager = $container->get('entities_info.manager');
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
    $entities = $this->entityInfoManager->getFieldConfigEntities();

    foreach ($entities as $entity) {
      $entity_id = $entity->id();
      $bundle = $entity->getBundleLabel();
      $storage = $this->entityInfoManager->getEntityBundles($entity_id);

      $form['configuration_' . $entity_id] = [
        '#type' => 'details',
        '#title' => $bundle,
        '#open' => FALSE,
      ];

      foreach ($storage as $item) {
        $id = $item->id();
        $label = $item->label();

        $form['configuration_' . $entity_id]['select_all'] = [
          '#title' => $this->t('Select all'),
          '#type' => 'checkbox',
          '#attributes' => [
            'name' => 'all_' . $entity_id
          ]
        ];

        $form['configuration_' . $entity_id][$id . '-ei-' . $entity_id] = [
          '#type' => 'checkbox',
          '#title' => $label . ' (' . $id . ')',
          '#weight' => '0',
          '#states' => [
            'checked' => [
              ':input[name="all_' . $entity_id . '"]' => [
                'checked' => TRUE
              ]
            ]
          ]
        ];
      }
    }

    $form['error'] = [
      '#type' => 'hidden',
    ];

    $form['table_type'] = [
      '#type' => 'select',
      '#options' => [
        'entity_fields' => $this->t('Entity with fields'),
        'entities' => $this->t('Entities'),
      ],
      '#title' => $this->t('Table type'),
      '#default' => 'entity_fields',
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
    $tempstore = $this->tempStoreFactory->get('entities_info_export');
    $tempstore->set('table_type', $results['table_type']);

    $values_selected = array_filter($results, fn($value) => $value === 1);
    $values_format = array_map(fn($index, $value) => $index, array_keys($values_selected), $values_selected);
    $values = array_combine(array_keys($values_selected), $values_format);

    $tempstore->set('values', $values);
    $form_state->setRedirect('entities_info.export_controller');
  }

}

<?php

namespace Drupal\entities_info\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Mpdf\Mpdf;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Export entities info to PDF file.
 */
class ExportPdfController extends ControllerBase {

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * Drupal\Core\Render\RendererInterface definition.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ExportPdfController {
    $instance = parent::create($container);
    $instance->tempStoreFactory = $container->get('tempstore.private');
    $instance->renderer = $container->get('renderer');
    $instance->moduleHandler = $container->get('module_handler');
    return $instance;
  }

  /**
   * Export info to PDF.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   Return File download.
   *
   * @throws \Mpdf\MpdfException
   * @throws \Exception
   */
  public function exportPdf(): BinaryFileResponse {

    $tempstore = $this->tempStoreFactory->get('entities_info_export');
    $tables = $tempstore->get('export');

    $headers = [
      'Content-Type'     => 'application/pdf',
      'Content-Disposition' => 'attachment;filename="download"',
    ];

    $modulePath = $this->moduleHandler->getModule('entities_info')->getPath();
    $stylesheetFile = $modulePath . "/css/tables.css";
    $stylesheet = file_get_contents($stylesheetFile);

    $mpdf = new Mpdf(['tempDir' => 'sites/default/files']);
    $mpdf->WriteHTML($stylesheet, 1);
    $mpdf->WriteHTML(Markup::create($this->renderer->render($tables)));
    $file = $mpdf->Output("entities_info.pdf", 'D');

    return new BinaryFileResponse($file, 200, $headers, TRUE);
  }

}

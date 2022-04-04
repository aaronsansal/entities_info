<?php

namespace Drupal\entities_info\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ExportPdfController {
    $instance = parent::create($container);
    $instance->tempStoreFactory = $container->get('tempstore.private');
    return $instance;
  }

  /**
   * Export info to PDF.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   Return File download.
   *
   * @throws \Mpdf\MpdfException
   */
  public function exportPdf(): BinaryFileResponse {

    $tempstore = $this->tempStoreFactory->get('entities_info_export');
    $tables = $tempstore->get('export');

    $headers = [
      'Content-Type'     => 'application/pdf',
      'Content-Disposition' => 'attachment;filename="download"',
    ];

    $mpdf = new Mpdf(['tempDir' => 'sites/default/files']);
    $mpdf->WriteHTML(Markup::create(render($tables)));
    $file = $mpdf->Output("entities_info.pdf", 'D');

    return new BinaryFileResponse($file, 200, $headers, TRUE);
  }

}

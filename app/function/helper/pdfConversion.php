<?php

use Dompdf\Dompdf as Dompdf;

function htmlToPdf($x)
{
  try {
    ob_start();
    include_once($x);
    $page = ob_get_contents();
    ob_end_clean();
    $document = new Dompdf();
    $document->loadHtml($page);
    $document->setPaper('A4', 'portrait');
    $document->render();
    $document->stream("Application.pdf", array("Attachment" => 0));
    return $document->output();
  } catch (Throwable $t) {
    echo $t->getMessage();
  }
}


$fileLoad = function ($x) {
  ob_start();
  require_once($x);
  $page = ob_get_contents();
  ob_end_clean();
  $document = new Dompdf;
  $document->loadHtml($page);
  $document->setPaper('A4', 'portrait');
  $document->render();
  return $document->output();
};

function convertHtml($x)
{
  ob_start();
  $x;
  $page = ob_get_contents();
  ob_end_clean();
  $document = new Dompdf;
  $document->loadHtml($page);
  $document->setPaper('A4', 'portrait');
  $document->render();
  $document->stream("Application.pdf", array("Attachment" => 0));
  return $document->output();
}

function pdfConvertDom($x)
{
  // $document = new Dompdf();
  ob_start();
  require_once($x);
  $page = ob_get_contents();
  ob_end_clean();
  $document = new Dompdf();
  $document->loadHtml($page);
  $document->setPaper('A4', 'portrait');
  $document->render();
  $document->stream("contract.pdf", array("Attachment" => 0));
  return $document->output();
}

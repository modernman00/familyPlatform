<?php

namespace App\classes;

use Exception;
use Swagger\Client\Configuration as SwaggerConfiguration;
use GuzzleHttp\Client;
use Swagger\Client\Api\ScanApi;

// https://api.cloudmersive.com/php-client.asp
class VirusScan
{

  // Constructor to initialize the API
  public function __construct($tempFileLocation)
  {

    try {

      $setApiKey = SwaggerConfiguration::getDefaultConfiguration()->setApiKey('Apikey', getenv('FILE_UPLOAD_CLOUDMERSIVE'));

      $apiInstance = new ScanApi(
        client: new Client(),
        config: $setApiKey
      );

      $result = $apiInstance->scanFile($tempFileLocation);

      if (!$result->getCleanResult()) {
        msgException(401, 'Virus detected');
        
      }
    } catch (Exception $e) {
      showError($e);
    }
  }
}

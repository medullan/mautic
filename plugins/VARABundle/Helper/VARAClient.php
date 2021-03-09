<?php
namespace MauticPlugin\VARABundle\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class VARAClient
{

  private $client;

  public function __construct()
  {
    $VARA_CEP_API_URL = getenv('VARA_CEP_API_URL');
    $VARA_CEP_API_KEY = getenv('VARA_CEP_API_KEY');
    $VARA_CEP_API_VERSION = (null !== getenv('VARA_CEP_API_VERSION')) ? getenv('VARA_CEP_API_VERSION') : '2018-11';

    if((!isset($VARA_CEP_API_URL)) || (!isset($VARA_CEP_API_URL))){
        throw new \Exception('Environment variables VARA_CEP_API_URL and VARA_CEP_API_KEY are required');
    }

    $this->client = new Client([
        'base_uri' => $VARA_CEP_API_URL,
        'headers' => ['Authorization' => "Bearer $VARA_CEP_API_KEY", 'Version' => "$VARA_CEP_API_VERSION"]
    ]);
  }

  function addUniqueIdentifierToResource($id, $resource, $start, $end, $use, $system, $multiple) {

    $identifier = $this->generateIdentifier();
    $response = $this->client->get("/fhir/3_0_1/$resource/$id");
    $responseBody = $response->getBody();
    $resourceObj = json_decode($responseBody, TRUE);

    if (!isset($use)) {
      $use = "temp";
    }

    if (!isset($system)) {
      $system = "http://vara.io/fhir/pro/recurring";
    }

    $identifierObj = [
      "use" => "$use",
      "system" => "$system",
      "value" => "$identifier",
    ];

    if (isset($start)) {
      $identifierObj['period']['start'] = date('Y-m-d\TH:i:s.Z\Z', strtotime($start));
    }

    if (isset($end)) {
      $identifierObj['period']['end'] = date('Y-m-d\TH:i:s.Z\Z', strtotime($end));
    }

    if (isset($multiple) && $multiple === TRUE) {
      if (!is_array($resourceObj['identifier'])) {
        $resourceObj['identifier'] = [];
      }
      array_push($resourceObj['identifier'], $identifierObj);
    } else {
      $resourceObj['identifier'] = $identifierObj;
    }


    try {
      $this->client->put("/fhir/3_0_1/$resource/$id", [
        'json' => $resourceObj
      ]);
    } catch(RequestException $exception) {
      if ($exception->hasResponse()) {
        $response = Psr7\Message::toString($exception->getResponse());
        // TODO: Log Error.
      }
    }

    return $identifier;
  }

  function generateIdentifier()
  {
    // TODO: Consider https://symfony.com/blog/introducing-the-new-symfony-uuid-polyfill
    $id = uniqid();
    return $id;
  }
}

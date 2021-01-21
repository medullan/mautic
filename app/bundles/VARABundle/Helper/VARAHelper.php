<?php
namespace Mautic\VARABundle\Helper;

use GuzzleHttp\Client;

class VARAHelper
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

    function savePatientUniqueIdentifier($patientId)
    {
        $identifier = $this->generatePatientIdentifier();
        $patientLookup = $this->client->get("/fhir/3_0_1/Patient/$patientId");

        $patientBody = $patientLookup->getBody();
        $patientObj = json_decode($patientBody, true);

        array_push($patientObj['identifier'], [
            "use" => "temp",
            "system" => "http://vara.io/fhir/pro/recurring",
            "value" => "$identifier",
        ]);

        $patientUpdate = $this->client->put("/fhir/3_0_1/Patient/$patientId", [
            'json' => $patientObj
        ]);

        return $identifier;
    }

    function generatePatientIdentifier()
    {
        $id = uniqid();
        return $id;
    }
}

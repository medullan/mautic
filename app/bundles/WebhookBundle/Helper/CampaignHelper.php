<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Helper;

use Doctrine\Common\Collections\Collection;
use Joomla\Http\Http;
use Mautic\CoreBundle\Helper\AbstractFormFieldHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Helper\TokenHelper;

class CampaignHelper
{
    /**
     * @var Http
     */
    protected $connector;

    /**
     * Cached contact values in format [contact_id => [key1 => val1, key2 => val1]].
     *
     * @var array
     */
    private $contactsValues = [];

    /**
     * @param Http $connector
     */
    public function __construct(Http $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Prepares the neccessary data transformations and then makes the HTTP request.
     *
     * @param array $config
     * @param Lead  $contact
     */
    public function fireWebhook(array $config, Lead $contact)
    {
        // dump($config);die;
        $payload = $this->getPayload($config, $contact);
        $headers = $this->getHeaders($config, $contact);

        $parsedUrl = $this->replaceTokensInUrl($config['url'], $contact);

        // `config` contains a `dataType` property where `0` represents key value pair format and `1` represents JSON format
        $this->makeRequest($parsedUrl, $config['method'], $config['timeout'], $headers, $payload, $config['dataType']);
    }

    /**
     * Translates tokens to values if present in a given url.
     *
     * @param string $url
     * @param Lead   $contact
     *
     * @return string
     */
    private function replaceTokensInUrl($url, $contact) {

        $contactValues = $this->getContactValues($contact);
        return rawurldecode(TokenHelper::findLeadTokens($url, $contactValues, true));
    }

    /**
     * Gets the payload fields from the config and if there are tokens it translates them to contact values.
     *
     * @param array $config
     * @param Lead  $contact
     *
     * @return array
     */
    private function getPayload(array $config, Lead $contact)
    {
        //process lists
        if($config['dataType'] == 0){
            $payload = !empty($config['additional_data']['list']) ? $config['additional_data']['list'] : '';
            $payload = array_flip(AbstractFormFieldHelper::parseList($payload));
            return $this->getTokenValues($payload, $contact);
        } else {
            //process raw json objects
            $payload = !empty($config['additional_data_raw']) ? $config['additional_data_raw']  : '';
            $payload = TokenHelper::findLeadTokens($payload, $contact->getProfileFields(), true);
            $payload = json_decode($payload, true);
            //this function returns arrays
            return $payload;
        }
    }

    /**
     * Gets the payload fields from the config and if there are tokens it translates them to contact values.
     *
     * @param array $config
     * @param Lead  $contact
     *
     * @return array
     */
    private function getHeaders(array $config, Lead $contact)
    {
        $headers = !empty($config['headers']['list']) ? $config['headers']['list'] : '';
        $headers = array_flip(AbstractFormFieldHelper::parseList($headers));

        return $this->getTokenValues($headers, $contact);
    }

    /**
     * @param string $url
     * @param string $method
     * @param int    $timeout
     * @param array  $headers
     * @param array  $payload
     *
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     */
    private function makeRequest($url, $method, $timeout, array $headers, array $payload, $isJson)
    {
        switch ($method) {
            case 'get':
                $payload  = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').http_build_query($payload);
                $response = $this->connector->get($payload, $headers, $timeout);
                break;
            case 'post':
            case 'put':
            case 'patch':
                $headers = array_change_key_case($headers);
                if($isJson) {
                    if (!array_key_exists('content-type', $headers)) {
                        $headers['content-type'] = 'application/json';
                    }
                    $payload = json_encode($payload, JSON_NUMERIC_CHECK);
                }
                $response = $this->connector->$method($url, $payload, $headers, $timeout);
                break;
            case 'delete':
                $response = $this->connector->delete($url, $headers, $timeout, $payload);
                break;
            default:
                throw new \InvalidArgumentException('HTTP method "'.$method.' is not supported."');
        }
        if ($response->code > 299) {
            throw new \OutOfRangeException("Campaign webhook response returned error code: {$response->code} \n Error Message: {$response->body}");
        }
    }

    /**
     * Translates tokens to values.
     * This is done for each parameter of a payload, for a webhook request.
     *
     * @param array $rawTokens
     * @param Lead  $contact
     *
     * @return array
     */
    private function getTokenValues(array $rawTokens, Lead $contact)
    {
        $values        = [];
        $contactValues = $this->getContactValues($contact);

        foreach ($rawTokens as $key => $value) {
            // rawurldecode() does not decode plus symbols (`+`) into spaces. urldecode() does.
            // See here: https://www.php.net/manual/en/function.rawurldecode.php
            $values[$key] = rawurldecode(TokenHelper::findLeadTokens($value, $contactValues, true));
        }

        return $values;
    }

    /**
     * Gets array of contact values.
     *
     * @param Lead $contact
     *
     * @return array
     */
    private function getContactValues(Lead $contact)
    {
        if (empty($this->contactsValues[$contact->getId()])) {
            $this->contactsValues[$contact->getId()]              = $contact->getProfileFields();
            $this->contactsValues[$contact->getId()]['ipAddress'] = $this->ipAddressesToCsv($contact->getIpAddresses());
        }

        return $this->contactsValues[$contact->getId()];
    }

    /**
     * @param Collection $ipAddresses
     *
     * @return string
     */
    private function ipAddressesToCsv(Collection $ipAddresses)
    {
        $addresses = [];
        foreach ($ipAddresses as $ipAddress) {
            $addresses[] = $ipAddress->getIpAddress();
        }

        return implode(',', $addresses);
    }
}
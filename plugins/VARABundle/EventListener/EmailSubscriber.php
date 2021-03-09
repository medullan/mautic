<?php

namespace MauticPlugin\VARABundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailSendEvent;
use MauticPlugin\VARABundle\Helper\VARAClient;

/**
 * Class EmailSubscriber
 */
class EmailSubscriber extends CommonSubscriber
{

  /**
   * @return array
   */
  static public function getSubscribedEvents()
  {
    return array(
      EmailEvents::EMAIL_ON_SEND => array('onEmailGenerate', 0),
    );
  }

  private function processVARAToken($contact, $token, $tokenParams) {

    if (stripos($token, 'vara-qr-identifier') !== false) {
      $resourceIdCustomField = $tokenParams['resource_id_field'];
      $resourceId = $contact[$resourceIdCustomField];

      if (isset($resourceId)) {
        $client = new VARAClient();
        $client->addUniqueIdentifierToResource(
          $resourceId,
          "questionnaireresponse",
          $tokenParams['start'],
          $tokenParams['end'],
          $tokenParams['use'],
          $tokenParams['system'],
          $tokenParams['multiple']
        );

        $token = $resourceId;
      }
    }
    if (stripos($token, 'vara-date') !== false) {
      $format = isset($tokenParams['format']) ? $tokenParams['format'] : 'Y-m-d';
      $token = date($format);
    }

    return $token;
  }

  /**
   * Search and replace tokens with content
   *
   * @param EmailSendEvent $event
   */
  public function onEmailGenerate(EmailSendEvent $event)
  {
    $currentContact = $event->getLead();
    $content = $event->getContent();

    if (isset($content) && isset($currentContact['varaid'])) {

      /**
       * Matches VARA token fields
       * Examples: `(vara-identifier)`, `(vara-identifier::{"end":"2 days"})`, `(vara-date::{"format":{"before":"10 days"}})`
       */
      $VARATokenRegex = '/(\(|%28)vara-(.*?)(\)|%29)/';

      $foundMatches = preg_match_all($VARATokenRegex, $content, $matches);
      if ($foundMatches) {
        foreach ($matches[2] as $key => $match) {
          $VARAToken = $matches[0][$key];
          $tokenParams = NULL;

          // (vara-identifier::{{JSON_PARAMS_HERE}})
          $paramSplit = explode('::', $VARAToken);

          if (count($paramSplit) > 1) {
            $rawParams = rtrim($paramSplit[1], ')'); // Remove trailing `)`

            // Double quotes cannot be used during Mautic email updates as some of the content are truncated when being saved.
            $rawParams = str_replace('`', '"', $rawParams);
            $tokenParams = json_decode($rawParams, TRUE);
          }

          $resolvedTokenValue = $this->processVARAToken($currentContact, $VARAToken, $tokenParams);
          if (isset($resolvedTokenValue)) {
            $content = str_replace($VARAToken, $resolvedTokenValue, $content);
            $event->setContent($content);
          }
        }
      }
    }
  }
}

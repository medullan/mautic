<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Model;

use Mautic\EmailBundle\Exception\EmailCouldNotBeSentException;
use Mautic\EmailBundle\OptionsAccessor\EmailToUserAccessor;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\UserBundle\Hash\UserHash;
use Mautic\LeadBundle\Helper\TokenHelper;

class SendEmailToUser
{
    /** @var EmailModel */
    private $emailModel;

    public function __construct(EmailModel $emailModel)
    {
        $this->emailModel = $emailModel;
    }

    /**
     * @param array $config - Send Email to User action properties. This includes the email recipient fields configured via the UI.
     * @param Lead  $lead - Current contact being processed.
     *
     * When emails are being sent to the configured `to` recipient, Mautic sends a separate email to
     * each email address present in the comma separated list.
     *
     * @throws EmailCouldNotBeSentException
     */
    public function sendEmailToUsers(array $config, Lead $lead)
    {
        $leadCredentials = $lead->getProfileFields();

        // Enables support for having the `to` configuration field to contain a Mautic custom field syntax value.
        // The `TokenHelper` replaces the token with the comma separated list of email addresses contained in the
        // custom field for the contact.
        // Validation of resolved email addresses against RFC 2822 standard is handled automatically by Mautic
        // for each email address configured. Validation errors are logged to the lead or contact's event log.
        if (isset($config['to'])) {
            $resolvedEmailRecipients = TokenHelper::findLeadTokens($config['to'], $leadCredentials, TRUE);
            $config['to'] = $resolvedEmailRecipients;
            $config['properties']['to'] = $resolvedEmailRecipients;
        }

        $emailToUserAccessor = new EmailToUserAccessor($config);

        $email = $this->emailModel->getEntity($emailToUserAccessor->getEmailID());

        if (!$email || !$email->isPublished()) {
            throw new EmailCouldNotBeSentException('Email not found or published');
        }

        $to  = $emailToUserAccessor->getToFormatted();
        $cc  = $emailToUserAccessor->getCcFormatted();
        $bcc = $emailToUserAccessor->getBccFormatted();

        $owner = $lead->getOwner();
        $users = $emailToUserAccessor->getUserIdsToSend($owner);

        $idHash = UserHash::getFakeUserHash();
        $tokens = $this->emailModel->dispatchEmailSendEvent($email, $leadCredentials, $idHash)->getTokens();
        $errors = $this->emailModel->sendEmailToUser($email, $users, $leadCredentials, $tokens, [], false, $to, $cc, $bcc);

        if ($errors) {
            throw new EmailCouldNotBeSentException(implode(', ', $errors));
        }
    }
}

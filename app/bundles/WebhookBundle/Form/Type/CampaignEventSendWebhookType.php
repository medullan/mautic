<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\SortableListType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Mautic\WebhookBundle\Validator\Constraints\IsJson;

/**
 * Class CampaignEventRemoteUrlType.
 */
class CampaignEventSendWebhookType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ConfigType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'url',
            UrlType::class,
            [
                'label'       => 'mautic.webhook.event.sendwebhook.url',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => true,
                'constraints' => [
                    new Url(
                        [
                            'message' => 'mautic.form.submission.url.invalid',
                        ]
                    ),
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'method',
            'choice',
            [
                'choices' => [
                    'get'    => 'GET',
                    'post'   => 'POST',
                    'put'    => 'PUT',
                    'patch'  => 'PATCH',
                    'delete' => 'DELETE',
                ],
                'multiple'   => false,
                'label_attr' => ['class' => 'control-label'],
                'label'      => 'mautic.webhook.event.sendwebhook.method',
                'attr'       => [
                    'class' => 'form-control',
                ],
                'empty_value' => false,
                'required'    => false,
            ]
        );

        $builder->add(
            'headers',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'mautic.webhook.event.sendwebhook.headers',
                'option_required' => false,
                'with_labels'     => true,
            ]
        );

        $choices = [
            '0'  => 'mautic.webhook.event.sendwebhook.dataType.pairs',
            '1'  => 'mautic.webhook.event.sendwebhook.dataType.raw',
        ];

        $dataType = (empty($options['data']['dataType'])) ? 0 : $options['data']['dataType'];

        $builder->add(
            'dataType',
            'button_group',
            [
                'choices'     => $choices,
                'expanded'    => true,
                'multiple'    => false,
                'label_attr'  => ['class' => 'control-label'],
                'label'       => 'mautic.webhook.event.sendwebhook.dataType',
                'required'    => false,
                'attr'        => [
                    'onchange' => 'Mautic.webhookToggleTypes(this);',
                    'tooltip'  => 'mautic.campaign.form.type.help'
                ],
                'data'        => $dataType,
            ]
        );
        $builder->add(
           'additional_data',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'mautic.webhook.event.sendwebhook.data',
                'label_attr' => ['class' => 'control-label'],
                'option_required' => false,
                'with_labels'     => true,
                'attr'       => [
                    'class'           => 'form-control',
                    'data-slot-param' => 'content',
                ],
            ]
        );

        $builder->add(
            'additional_data_raw',
            TextareaType::class,
            [
                'label'      => 'mautic.webhook.event.sendwebhook.data',
                'label_attr' => ['class' => 'control-label hide'],
                'required'   => false,
                'attr'       => [
                    'class'           => 'form-control hide',
                    'data-slot-param' => 'content',
                ],
                'constraints' => [
                    new IsJson()
                ]
            ]
        );

        $builder->add(
            'timeout',
            'number',
            [
                'label'      => 'mautic.webhook.event.sendwebhook.timeout',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'          => 'form-control',
                    'postaddon_text' => $this->translator->trans('mautic.core.time.seconds'),
                ],
                'data' => !empty($options['data']['timeout']) ? $options['data']['timeout'] : 10,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'campaignevent_sendwebhook';
    }
}

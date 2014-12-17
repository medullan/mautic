<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\AssetBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
/**
 * Class BuilderSubscriber
 *
 * @package Mautic\AssetBundle\EventListener
 */
class ConfigSubscriber extends CommonSubscriber
{

    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            ConfigEvents::CONFIG_ON_GENERATE   => array('onConfigGenerate', 0)
        );
    }

    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $paramsFile = $event->getContainer()->getParameter('kernel.root_dir') . '/bundles/AssetBundle/Config/parameters.php';

        if (file_exists($paramsFile)) {
            // Import the bundle configuration, $parameters is defined in this file
            include $paramsFile;
        } else {
            $parameters = array();
        }

        $event->addForm(array(
            'bundle' => 'AssetBundle',
            'formClass' => '\Mautic\AssetBundle\Form\Type\ConfigType',
            'parameters' => $parameters
        ));
    }
}
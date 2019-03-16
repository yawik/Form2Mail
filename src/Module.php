<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    @author Carsten Bleek <bleek@cross-solution.de>
 */

/** */
namespace Form2Mail;

use Core\ModuleManager\ModuleConfigLoader;
use Zend\ModuleManager\Feature;

/**
 * ${CARET}
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class Module implements Feature\ConfigProviderInterface
{
    public function getConfig()
    {
        return ModuleConfigLoader::load(__DIR__ . '/../config');
    }

    function onBootstrap(MvcEvent $e)
    {
        self::$isLoaded=true;
        $eventManager = $e->getApplication()->getEventManager();
        $services     = $e->getApplication()->getServiceManager();
        /*
         * remove Submenu from "applications"
         */
        $config=$services->get('config');
        unset($config['navigation']['default']['apply']['pages']);
        $services->setAllowOverride(true);
        $services->setService('config', $config);
        $services->setAllowOverride(false);
        if (!Console::isConsole()) {
            $sharedManager = $eventManager->getSharedManager();
            /*
             * use a neutral layout, when rendering the application form and its result page.
             * Also the application preview should be rendered in this layout.
             *
             * We need a post dispatch hook on the controller here as we need to have
             * the application entity to determine how to set the layout in the preview page.
             */
            $callback=function ($event) {
                    $viewModel  = $event->getViewModel();
                    $template   = 'layout/application-form';
                    $controller = $event->getTarget();
                    if ($controller instanceof \Applications\Controller\ApplyController) {
                        $viewModel->setTemplate($template);
                        return;
                    }
                    if ($controller instanceof \Applications\Controller\ManageController
                        && 'detail' == $event->getRouteMatch()->getParam('action')
                        && 200 == $event->getResponse()->getStatusCode()
                    ) {
                        $result = $event->getResult();
                        if (!is_array($result)) {
                            $result = $result->getVariables();
                        }
                        if ($result['application']->isDraft()) {
                            $viewModel->setTemplate($template);
                        }
                    }
                };

            foreach (array('Applications') as $identifier) {
                $sharedManager->attach($identifier, MvcEvent::EVENT_DISPATCH, $callback, -2 /*postDispatch, but before most of the other zf2 listener*/ );
            }
        }
    }

}

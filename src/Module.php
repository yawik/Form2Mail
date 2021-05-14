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
use Form2Mail\Controller\SendMailController;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent as MvcMvcEvent;
use Zend\ModuleManager\Feature;
use Zend\Console\Console;
use Zend\Mvc\MvcEvent;

/**
 * ${CARET}
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class Module implements Feature\ConfigProviderInterface
{
   /**
    * indicates, that the autoload configuration for this module should be loaded.
    * @see
    *
    * @var bool
    */
    public static $isLoaded=false;

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

            $sharedManager->attach(
                SendMailController::class,
                MvcMvcEvent::EVENT_DISPATCH,
                function (MvcEvent $e) use ($config) {
                    /** @var \Laminas\Http\PhpEnvironment\Request $request */
                    $request = $e->getRequest();
                    $response = $e->getResponse();
                    $origins = $config['f2m_origins'] ?? [];
                    $origin = $request->getHeader('Origin');
                    $origin = is_bool($origin) ? "*" : $origin->getFieldValue();
                    if (in_array($origin, $origins)) {
                        $e->getResponse()->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', $origin);
                    }
                    if ($request->isOptions()) {
                        $response->setStatusCode(Response::STATUS_CODE_204);
                        $response->getHeaders()->addHeaderLine('Allow', join(',', [Request::METHOD_GET, Request::METHOD_OPTIONS, Request::METHOD_POST]));
                        $routeMatch = $e->getRouteMatch();
                        $routeName = $routeMatch->getMatchedRouteName();
                        $methods = $config['f2m_methods'][$routeName] ?? '';
                        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Methods', $methods);
                        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Headers', 'Content-Type');
                        return $response;
                    }
                },
                100
            );

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

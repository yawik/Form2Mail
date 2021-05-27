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

use Core\ModuleManager\Feature\VersionProviderInterface;
use Core\ModuleManager\Feature\VersionProviderTrait;
use Core\ModuleManager\ModuleConfigLoader;
use Form2Mail\Controller\AbstractApiResponseController;
use Form2Mail\Controller\DetailsController;
use Form2Mail\Controller\ExtractEmailsController;
use Form2Mail\Controller\SendMailController;
use Form2Mail\Options\ModuleOptions;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\ModuleManager\Feature;
use Laminas\Console\Console;

/**
 * ${CARET}
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class Module implements Feature\ConfigProviderInterface, VersionProviderInterface
{
    use VersionProviderTrait;

    const VERSION = '0.1.0';

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

    public function onBootstrap(MvcEvent $e)
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
            $callback = function (MvcEvent $e) use ($config, $services) {
                /** @var \Laminas\Http\PhpEnvironment\Request $request */
                $request = $e->getRequest();
                $response = $e->getResponse();
                /** @var ModuleOptions $options */
                $options = $services->get(ModuleOptions::class);
                $origins = $options->getAllowedOrigins();
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
                    $methods = $options->getAllowedMethods($routeName);
                    $response->getHeaders()->addHeaderLine('Access-Control-Allow-Methods', $methods);
                    $response->getHeaders()->addHeaderLine('Access-Control-Allow-Headers', 'Content-Type');
                    return $response;
                }
            };

            $sharedManager->attach(SendMailController::class, MvcEvent::EVENT_DISPATCH, $callback, 100);
            $sharedManager->attach(DetailsController::class, MvcEvent::EVENT_DISPATCH, $callback, 100);
            $sharedManager->attach(ExtractEmailsController::class, MvcEvent::EVENT_DISPATCH, $callback, 100);
            $sharedManager->attach(AbstractApiResponseController::class, MvcEvent::EVENT_DISPATCH, $callback, 100);

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

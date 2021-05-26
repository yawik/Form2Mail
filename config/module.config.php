<?php
/**
 * @filesource
 * @copyright (c) 2019 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Carsten Bleek <bleek@cross-solution.de>
 */
namespace Form2Mail;

use Form2Mail\Controller\DetailsController;
use Form2Mail\Controller\DetailsControllerFactory;
use Form2Mail\Controller\SendMailController;
use Form2Mail\Controller\SendMailControllerFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'sendmail' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/sendmail',
                    'defaults' => [
                        'controller' => SendMailController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
            'details' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/details',
                    'defaults' => [
                        'controller' => DetailsController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            SendMailController::class => SendMailControllerFactory::class,
            DetailsController::class => DetailsControllerFactory::class,
        ],
    ],

    'controller_plugins' => [
        'factories' => [
            Controller\Plugins\SendMail::class => Controller\Plugins\SendMailFactory::class,
            Controller\Plugins\StoreApplication::class => Controller\Plugins\StoreApplicationFactory::class,
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'startpage'  => __DIR__ . '/../view/startpage.phtml',
            'layout/application-form' => __DIR__ . '/../view/application-form.phtml',
            'form2mail/mail/sendmail' => __DIR__ . '/../view/mail/sendmail.phtml',
            'form2mail/mail/conduent' => __DIR__ . '/../view/mail/conduent.phtml',
            'form2mail/mail/header' => __DIR__ . '/../view/mail/header.phtml',
            'form2mail/mail/footer' => __DIR__ . '/../view/mail/footer.phtml',
        ]
    ],

    'view_helper_config' => [
        'headscript' => [
            'lang/apply' => [
                [\Laminas\View\Helper\HeadScript::SCRIPT, ';(function($) {
                    $(function() {
                        $("#attributes-acceptedPrivacyPolicy").click();
                        var $div = $("#attributes > div:nth-child(2)");
                        $div.find("> div").hide();
                        $div.append("<div class=\\"col-md-12\\"><p class=\\"small\\">Ihre Daten werden direkt an den Arbeitgeber weitergeleitet und unterliegen den Datenschutzbedingungen des Unternehmens. Für weitere Informationen zu den geltenden Datenschutzbedingungen, kontaktieren Sie bitte direkt das Unternehmen.</p></div>");
                        $("#application_incomplete").addClass("hidden");
                    });
                })(jQuery);'],
            ],
        ],
    ],

    'hydrators' => [
        'factories' => [
            Hydrator\ApplicationHydrator::class => InvokableFactory::class,
        ],
    ],

    'filters' => [
        'factories' => [
            Filter\JsonDataFilter::class => InvokableFactory::class,
        ],
    ],

    'options' => [
        Options\SendmailOrganizationOptionsCollection::class => [],
        Options\ModuleOptions::class => [],

        \Auth\Options\UserInfoFieldsetOptions::class => [
            'options' => [
                'fields' => [
                    'gender' => [
                        'enabled' => false,
                        'required' => false,
                    ],
                    'phone' => [
                        'enabled' => false,
                        'required' => false,
                    ],
                    'street' => [
                        'enabled' => false,
                        'required' => false,
                    ],
                    'houseNumber' => [
                        'enabled' => false,
                        'required' => false,
                    ],
                    'city' => [
                        'enabled' => false,
                        'required' => false,
                    ],
                    'postalCode' => [
                        'enabled' => false,
                        'required' => false,
                    ],
                    'country' => [
                        'enabled' => false,
                        'required' => false,
                    ],
                ],
            ],
        ],
    ],
];

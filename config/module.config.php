<?php
/**
 * @filesource
 * @copyright (c) 2019 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Carsten Bleek <bleek@cross-solution.de>
 */
namespace Form2Mail;

return [
    'view_manager' => [
        'template_map' => [
            'startpage'  => __DIR__ . '/../view/startpage.phtml',
            'layout/application-form' => __DIR__ . '/../view/application-form.phtml',
            'form/auth/contact.form' => __DIR__ . '/../view/form/contact-form.phtml',
            'form/auth/contact.view' => __DIR__ . '/../view/form/contact-view.phtml',
        ]
    ],

    'form_elements' => [
        'factories' => [
            Form\AuthUserInfoFieldset::class => Form\AuthUserInfoFieldsetFactory::class
        ],
        'aliases' => [
            'Auth/UserInfoFieldset' => Form\AuthUserInfoFieldset::class,
        ],
    ],

    'options' => [
        Options\AuthUserInfoFieldsetOptions::class => []
    ],
];

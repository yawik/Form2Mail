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
        ]
    ],

    'options' => [
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

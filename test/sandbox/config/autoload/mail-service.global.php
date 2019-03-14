<?php
/**
 * YAWIK
 *
 * Configuration file of the MailService
 *
 * Copy this file into your autoload directory (without .dist) and adjust it for your needs
 *
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/**
 * Mail transport. Possible Values "smtp", "sendmail". If "sendmail" is used, YAWIK will use the php mail() function
 * for sending mails. This requires a local MTA.
 *
 * @var string
 */
$transport = 'file';

/**
 * Fully-qualified classname or short name resolvable via Zend\Mail\Protocol\SmtpLoader.
 * Typically, this will be one of “smtp”, “plain”, “login”, or “crammd5”, and defaults to “smtp”.
 */
$auth = 'login';

/**
 * @var string Local client hostname
 */
$name = 'localhost';

/**
 * 'ssl' or 'tls' one null.
 */
$ssl = "tls";

/**
 * @var string Remote SMTP hostname or IP
 */
$host = 'smtp.gmail.com';

/**
 * @var int
 */
$port = 587;

/**
 * Username
 */
$username = "test.yawik@gmail.com";

/**
 * password
 */
$password = "y4w1ktest";


//
// Do not change below this line!
//

$config = [
    'options' => [
        'Core/MailServiceOptions' => [
            'options' => [
                'username' => $username,
                'transportClass' => $transport,
                'path' => realpath(__DIR__.'/../../../../').'/build/mails'
            ],
        ],
    ]
];
return $config;

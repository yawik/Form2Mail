# Form2Mail

form2mail takes formular data of an [application form](https://gitlab.com/yawik/applicationform) and generates a Mail.

## Requirements

- php 7.4
- [yawik](https://yawik.org)

## Install

``` bash linenums="1"
git clone https://github.com/yawik/Form2Mail.git
cd Form2Mail 
composer install
```

## CORS

you can configure cors 

``` php linenums="1"
<?php

declare(strict_types=1);

namespace Form2Mail;

/*
 * List of allowed origins
 * Needed for CORS;
 */
$options['allowedOrigins'] = [
     'https://form.yawik.org',
];

/*
 * Allowed methods for above origins.
 * Map route names to request methods
 * Multiple methods are specified as a comma separated string.
 */
//$options['allowedMethods'] = [
//    'sendmail' => 'POST',
//    'details' => 'GET',
//];


/*
 * Do not change below
 */
return ['options' => [Options\ModuleOptions::class => ['options' => $options]]];
```
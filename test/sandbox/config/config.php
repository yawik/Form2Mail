<?php

// chdir in config file so tests environment can chdir to this sandbox
chdir(dirname(__DIR__));
return [
    'modules' => [
        'Core',
        'Auth',
        'Jobs',
        'Settings',
        'Applications',
        'Cv',
        "Form2Mail"
    ],
];

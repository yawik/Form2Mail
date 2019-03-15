<?php
namespace Deployer;

require 'recipe/zend_framework.php';

// Project name
set('application', 'Form2Mail');

// Project repository
set('repository', 'git@gitlab.cross-solution.de:Personalwerk/form2mail.git');

// Shared files/dirs between deploys 
add('shared_files', ['test/sandbox/public/.htaccess']);
add('shared_dirs', [
    'test/sandbox/var/log',
    'test/sandbox/var/cache',
    'test/sandbox/config/autoload',
]);

// Writable dirs by web server 
add('writable_dirs', [
    'test/sandbox/var/log',
    'test/sandbox/var/log',
    ]);

set('default_stage', 'prod');

// Hosts

host('jobs-deutschland.de')
    ->user('yawik')
    ->stage('prod')
    ->multiplexing(false) 
    ->set('deploy_path', '/var/www/production');   

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');


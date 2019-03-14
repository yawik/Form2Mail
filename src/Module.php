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
}

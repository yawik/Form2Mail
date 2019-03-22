<?php
/**
 * AMS Form2Mail
 *
 * @filesource
 * @copyright 2019 Cross Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Form2Mail\Form;

use Core\Factory\Form\AbstractCustomizableFieldsetFactory;
use Form2Mail\Options\AuthUserInfoFieldsetOptions;

/**
 * Factory for Form2Mail\Form\AuthUserInfoFieldset
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write tests
 */
class AuthUserInfoFieldsetFactory extends AbstractCustomizableFieldsetFactory
{
    const OPTIONS_NAME = AuthUserInfoFieldsetOptions::class;
    const CLASS_NAME = AuthUserInfoFieldset::class;
}

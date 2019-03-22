<?php
/**
 * AMS Form2Mail
 *
 * @filesource
 * @copyright 2019 Cross Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Form2Mail\Options;

use Core\Options\FieldsetCustomizationOptions;

/**
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write tests
 */
class AuthUserInfoFieldsetOptions extends FieldsetCustomizationOptions
{
    protected $fields = [
        'gender' => [
            'enabled' => false,
            'required' => false,
        ],
        'phone' => [
            'enabled' => false,
            'required' => false,
        ],
    ];
}

<?php
/**
 * AMS Form2Mail
 *
 * @filesource
 * @copyright 2019 Cross Solution <https://www.cross-solution.de>
 * @license
 */

declare(strict_types=1);

namespace Form2Mail\Form;

use Auth\Form\UserInfoFieldset;

use Core\Form\CustomizableFieldsetTrait;
use Core\Form\CustomizableFieldsetInterface;

/**
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write tests
 */
class AuthUserInfoFieldset extends UserInfoFieldset implements CustomizableFieldsetInterface
{
    use CustomizableFieldsetTrait;

    public function getDefaultInputFilterSpecification()
    {
        return parent::getInputFilterSpecification();
    }
}

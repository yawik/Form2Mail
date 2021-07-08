<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Filter;

use Jobs\Entity\JobInterface;
use Laminas\Filter\AbstractFilter;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class FormFrontendUri extends AbstractFilter
{
    private $uriTmpl;

    public function __construct($formFrontendUri)
    {
        if (!preg_match('~%(?:job|apply)Id%~', $formFrontendUri)) {
            $formFrontendUri .= '?job=%applyId%';
        }
        $this->uriTmpl = $formFrontendUri;
    }

    public function filter($value)
    {
        switch (true) {
            case $value instanceof JobInterface:
                $replace = [$value->getId(), $value->getApplyId()];
                break;

            case is_array($value):
                $replace = $value;
                break;

            case is_scalar($value):
                $replace = [$value, $value];
                break;

            default:
                throw new \InvalidArgumentException('Expect JobInterface, array or scalar');
        }

        return str_replace(['%jobId%', '%applyId%'], $replace, $this->uriTmpl);
    }
}

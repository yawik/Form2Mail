<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Mail;

use Interop\Container\ContainerInterface;
use Laminas\Mail\Header\ContentType;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class TextTemplateMailFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /** @var \Core\Mail\HTMLTemplateMessage $mail */
        $mail = $container->get('Core/MailService')->build('htmltemplate');
        $headers = $mail->getHeaders();
        $headers->removeHeader('Content-Type');
        $headers->addHeader(ContentType::fromString('Content-Type: text/plain; charset=UTF-8'));

        return $mail;
    }
}

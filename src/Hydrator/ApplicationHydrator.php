<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Hydrator;

use Applications\Entity\Attachment;
use Applications\Entity\Contact;
use Core\Entity\Hydrator\EntityHydrator;
use Form2Mail\Filter\JsonDataFilter;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class ApplicationHydrator extends EntityHydrator
{

    private $jsonDataFilter;

    public function setJsonDataFilter($filter)
    {
        $this->jsonDataFilter = $filter;
    }

    public function getJsonDataFilter()
    {
        if (!$this->jsonDataFilter) {
            $this->jsonDataFilter = new JsonDataFilter();
        }

        return $this->jsonDataFilter;
    }

    public function hydrate(array $data, $object)
    {
        $filter = $this->getJsonDataFilter();
        $data = $filter($data);

        if ($data['photo'] && $data['photo']['error'] === UPLOAD_ERR_OK) {
            $image = new Attachment();
            $image->setName($data['photo']['name']);
            $image->setType(mime_content_type($data['photo']['tmp_name']));
            $image->setFile($data['photo']['tmp_name']);

            $data['contact']['image'] = $image;
        }

        $contact = new Contact();
        $data['contact'] = parent::hydrate($data['contact'], $contact);

        $attachments = $object->getAttachments();
        foreach ($data['attachments'] as $tmpFile) {
            if ($tmpFile['error'] != UPLOAD_ERR_OK) {
                continue;
            }
            $file = new Attachment();
            $file->setName($tmpFile['name']);
            $file->setType(mime_content_type($tmpFile['tmp_name']));
            $file->setFile($tmpFile['tmp_name']);

            $attachments->add($file);
        }
        $data['attachments'] = $attachments;

        return parent::hydrate($data, $object);
    }
}

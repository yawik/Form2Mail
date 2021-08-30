<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Hydrator;

use Applications\Entity\Attachment;
use Applications\Entity\Contact;
use Core\Entity\FileMetadata;
use Core\Entity\Hydrator\EntityHydrator;
use Core\Service\FileManager;
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
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

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
        $fileManager = $this->fileManager;
        $data = $filter($data);

        if ($data['photo'] && $data['photo']['error'] === UPLOAD_ERR_OK) {

            $metadata = new FileMetadata();
            $metadata->setContentType(mime_content_type($data['photo']['tmp_name']));
            $metadata->setName($data['photo']['name']);

            $image = $fileManager->uploadFromFile(
                Attachment::class,
                $metadata,
                $data['photo']['tmp_name'],
                $data['photo']['name']
            );

            $data['contact']['image'] = $image;
        }

        $contact = new Contact();
        $data['contact'] = parent::hydrate($data['contact'], $contact);

        $attachments = $object->getAttachments();
        foreach ($data['attachments'] as $tmpFile) {
            if ($tmpFile['error'] != UPLOAD_ERR_OK) {
                continue;
            }
            $metadata = new FileMetadata();
            $metadata->setContentType(mime_content_type($tmpFile['tmp_name']));
            $metadata->setName($tmpFile['name']);

            $file = $fileManager->uploadFromFile(
                Attachment::class,
                $metadata,
                $tmpFile['tmp_name'],
                $tmpFile['name']
            );

            $attachments->add($file);
        }
        $data['attachments'] = $attachments;

        return parent::hydrate($data, $object);
    }
}

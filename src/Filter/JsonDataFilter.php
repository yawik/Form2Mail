<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Stdlib\ArrayUtils;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class JsonDataFilter extends AbstractFilter
{

    public function filter($valueAndFiles)
    {
        [$value, $uploads] = $valueAndFiles;

        if (!is_array($value)) {
            throw new \InvalidArgumentException('Expected array, but got ' . gettype($value));
        }

        $contact = isset($value['user']) && is_array($value['user']) ? $value['user'] : [];
        $photo = isset($uploads['photo']) ? $uploads['photo'] : false;
        $files = isset($uploads['attached']) && is_array($uploads['attached']) ? $uploads['attached'] : [];
        $summary = $value['summary'] ?? '';

        foreach (($value['extras'] ?? []) as $key => $extra) {
            $summary .= "\n\n";
            if (is_array($extra)) {
                if (ArrayUtils::hasNumericKeys($extra)) {
                    $summary .= "$key: " . join(', ', $extra);
                } else {
                    $summary .= "$key:";
                    foreach ($extra as $k => $v) {
                        $summary .= "\n    - $k: $v";
                    }
                }
            } else {
                $summary .= "$key: $extra";
            }
        }

        return [
            'contact' => $contact,
            'photo' => $photo,
            'attachments' => $files,
            'summary' => $summary,
        ];
    }
}

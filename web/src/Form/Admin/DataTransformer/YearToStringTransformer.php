<?php

namespace App\Form\Admin\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use DateTimeImmutable;

class YearToStringTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof DateTimeImmutable) {
            throw new TransformationFailedException('Expected a DateTimeImmutable.');
        }

        return $value->format('Y');
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value . '-01-01');
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid date format.');
        }
    }
}

<?php

namespace App\Form\Admin\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BooleanToIntTransformer implements DataTransformerInterface
{
    /**
     * Transforms an integer to a boolean.
     *
     * @param mixed $value The value in the original representation
     * @return mixed The value in the transformed representation
     */
    public function transform(mixed $value): mixed
    {
        // Transformuje integer (0, 1) na boolean (false, true)
        return (bool) $value;
    }

    /**
     * Transforms a boolean back to an integer.
     *
     * @param mixed $value The value in the transformed representation
     * @return mixed The value in the original representation
     */
    public function reverseTransform(mixed $value): mixed
    {
        // Transformuje boolean (false, true) zpět na integer (0, 1)
        return $value ? 1 : 0;
    }
}

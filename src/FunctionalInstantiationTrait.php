<?php

namespace AdinanCenci\Psr7;

/**
 * Allows objects to instantiate copies of themselves with slightly
 * different properties.
 */
trait FunctionalInstantiationTrait
{
    /**
     * Returns parameters that will be used in the classes' constructor.
     *
     * To construct a new object just like this one.
     *
     * @return array
     *   This object properties.
     */
    protected function getConstructorParameters(): array
    {
        return [];
    }

    /**
     * Instantiate a new object with slightly different properties.
     *
     * @param array $parameters
     *   Key => value pairs to instantiate the new object.
     *   Properties not represented in $parameters will be copied from the
     *   original.
     *
     * @return static
     *   A new instance.
     */
    protected function instantiate(array $parameters): static
    {
        $parameters = array_merge($this->getConstructorParameters(), $parameters);
        $objectReflection = new \ReflectionClass(get_class($this));
        return $objectReflection->newInstanceArgs($parameters);
    }
}

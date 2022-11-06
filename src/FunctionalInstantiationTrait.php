<?php
namespace AdinanCenci\Psr7;

trait FunctionalInstantiationTrait 
{
    protected function getConstructorParameters() 
    {
        return [];
    }

    protected function instantiate($parameters) 
    {
        $parameters = array_merge($this->getConstructorParameters(), $parameters);
        $objectReflection = new \ReflectionClass(get_class($this));
        return $objectReflection->newInstanceArgs($parameters);
    }
}

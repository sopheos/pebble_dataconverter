<?php

namespace Pebble\DataConverter;

use InvalidArgumentException;
use ReflectionClass;

class HydrateConverter extends PrepareConverterAbstract
{
    private object $object;
    private array $properties = [];
    private array $methods = [];

    public function __construct(string|object $object)
    {
        $this->setObject($object);
        $this->setReflection();
    }

    public static function create(string|object $object): static
    {
        return new static($object);
    }

    private function setObject(string|object $object)
    {
        if (is_object($object)) {
            $this->object = clone $object;
        } elseif (is_string($object) && class_exists($object)) {
            $this->object = new $object();
        } else {
            throw new InvalidArgumentException(sprintf(
                'Expected object or class name got `%s`',
                gettype($object)
            ));
        }
    }

    private function setReflection()
    {
        $reflect = new ReflectionClass($this->object);

        foreach ($reflect->getProperties() as $property) {
            if ($property->isPublic() && !$property->isStatic()) {
                $this->properties[$property->getName()] = true;
            }
        }

        foreach ($reflect->getMethods() as $method) {
            if ($method->isPublic() && !$method->isStatic()) {
                $this->methods[$method->getName()] = true;
            }
        }
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    protected function prepare(mixed $input): mixed
    {
        if (!is_array($input)) {
            return $input;
        }

        $object = clone $this->object;
        foreach ($input as $key => $value) {
            $object = $this->hydrate($object, $key, $value);
        }

        return $object;
    }

    private function hasProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    private function hasMethod(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * @param mixed $value
     */
    private function hydrate(object $object, string $key, $value): object
    {
        if ($this->hasProperty($key)) {
            $object->$key = $value;
            return $object;
        }

        $setter = 'set' . ucfirst($key);
        if ($this->hasMethod($setter)) {
            $object->$setter($value);

            return $object;
        }

        $immutableSetter = 'with' . ucfirst($key);
        if ($this->hasMethod($immutableSetter)) {
            return $object->$immutableSetter($value);
        }

        return $object;
    }
}

<?php

declare(strict_types=1);

namespace App\DependencyInjection;

/**
 * Class Container.
 * Dependency Injection Container.
 *
 * website : https:// gist.github.com/MustafaMagdi/2bb27aebf6ab078b1f3e5635c0282fac#file-container-php
 */
class Container
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @param null  $concrete
     * @param mixed $abstract
     */
    public function set($abstract, $concrete = null)
    {
        if (null === $concrete) {
            $concrete = $abstract;
        }
        $this->instances[$abstract] = $concrete;
    }

    /**
     * @param array $parameters
     * @param mixed $abstract
     *
     * @return null|mixed|object
     *
     * @throws \Exception
     */
    public function get($abstract, $parameters = [])
    {
        // if we don't have it, just register it
        if (!isset($this->instances[$abstract])) {
            $this->set($abstract);
        }

        return $this->resolve($this->instances[$abstract], $parameters);
    }

    /**
     * resolve single.
     *
     * @param mixed $concrete
     * @param mixed $parameters
     *
     * @return mixed|object
     *
     * @throws \Exception
     */
    public function resolve($concrete, $parameters = [])
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new \ReflectionClass($concrete);
        // check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new \Exception("La classe {$concrete} n'est pas instantiable");
        }

        // get class constructor
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            // get new instance from class
            return $reflector->newInstance();
        }

        // get constructor params
        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        // get new instance with dependencies resolved
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * get all dependencies resolved.
     *
     * @param mixed $parameters
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getDependencies($parameters)
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            // get the type hinted class
            if ($parameter->getType() && !$parameter->getType()->isBuiltin()) {
                $dependencies[] = $this->get($parameter->getType()->getName());

                continue;
            }

            // check if default value for a parameter is available
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();

                continue;
            }

            // Get the parameter name
            $paramName = $parameter->name;
            // Check if the container has the parameter name registered
            if (isset($this->instances[$paramName])) {
                $dependencies[] = $this->get($paramName);

                continue;
            }

            throw new \Exception("Impossible de résoudre la dépendance de classe {$parameter->name}");
        }

        return $dependencies;
    }

    public function injectProperties(object $object): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $propertyType = $property->getType();
            if (null === $propertyType) {
                continue;
            }
            $dependency = $propertyType->getName();

            try {
                $dependencyInstance = $this->get($dependency);
                $property->setAccessible(true);
                $property->setValue($object, $dependencyInstance);
            } catch (\Exception $e) {
                throw new \Exception("Ne peut pas injecter la propriété {$property->name} de la classe {$reflectionClass->name}");
            }
        }
    }
}

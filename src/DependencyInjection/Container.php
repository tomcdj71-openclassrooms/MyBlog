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
    public function resolve($concrete, $parameters)
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new \ReflectionClass($concrete);
        // check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
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
            $dependency = $parameter->getType() && !$parameter->getType()->isBuiltin() ? new \ReflectionClass($parameter->getType()->getName()) : null;
            if (null === $dependency) {
                // check if default value for a parameter is available
                $dependencies[] = $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                    : throw new \Exception("Can not resolve class dependency {$parameter->name}");
            } else {
                // get dependency resolved
                $dependencies[] = $this->get($dependency->name);
            }
        }

        return $dependencies;
    }

    public function injectProperties(object $object): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $dependency = $property->getType()->getName();

            try {
                $dependencyInstance = $this->get($dependency);
                $property->setAccessible(true);
                $property->setValue($object, $dependencyInstance);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}

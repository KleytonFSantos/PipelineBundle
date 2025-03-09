<?php

namespace KleytonSantos\Pipeline;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Pipeline
{
    private array $config = [];
    private mixed $passable = null;
    private array $pipes = [];
    private string $method = 'handle';

    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly ContainerInterface $container
    )
    {
        $this->config = $this->parameterBag->get('pipeline.config');
    }

    public function send(mixed $passable): static
    {
        $this->passable = $passable;
        return $this;
    }

    public function withConfig(string $configName = ''): static
    {
        if (!isset($this->config[$configName])) {
            throw new RuntimeException("Configuration for '$configName' not found in pipeline config");
        }

        $this->pipes = $this->config[$configName];

        return $this;
    }

    /**
     * Sets the pipes (stages) through which the passable will be processed.
     *
     * This method allows you to manually specify the pipes to be used in the pipeline.
     *
     * @param array $pipes An array of pipes (callables or class names) to process the passable.
     * @return static Returns the current pipeline instance for method chaining.
     */
    public function through(array $pipes): static
    {
        $this->pipes = $pipes;

        return $this;
    }

    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            fn (mixed $passable) => $destination($passable)
        );

        return $pipeline($this->passable);
    }

    public function thenReturn(): mixed
    {
        return $this->then(fn (mixed $passable) => $passable);
    }

    private function carry(): Closure
    {
        return function (Closure $stack, Closure | string $pipe) {
            return function (mixed $passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                } elseif (is_object($pipe)) {
                    return $pipe->{$this->method}($passable, $stack);
                } elseif (is_string($pipe) && class_exists($pipe)) {
                    $pipeInstance = $this->container->get($pipe);
                    return $pipeInstance->{$this->method}($passable, $stack);
                } else {
                    throw new InvalidArgumentException('Invalid pipe type.');
                }
            };
        };
    }
}

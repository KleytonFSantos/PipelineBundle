<?php

namespace KleytonSantos\Pipeline;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Yaml\Yaml;

class Pipeline extends AbstractBundle
{
    private array $config = [];
    private string $configName = '';
    protected mixed $passable;
    protected array $pipes = [];
    protected string $method = 'execute';

    /**
     * Sends the passable (data) through the pipeline.
     *
     * This method initializes the pipeline with the given passable data.
     *
     * @param mixed $passable The data to be processed by the pipeline.
     * @return static Returns the current pipeline instance for method chaining.
     */
    public static function send(mixed $passable): static
    {
        $pipeline = new static;

        $pipeline->passable = $passable;

        return $pipeline;
    }

    /**
     * Loads and applies configuration for the pipeline.
     *
     * This method reads a YAML configuration file and sets the pipes based on the provided configuration name.
     *
     * @param string $configName The name of the configuration to load from the YAML file.
     * @return static Returns the current pipeline instance for method chaining.
     * @throws RuntimeException If the configuration file is missing or the specified configuration is not found.
     */
    public function withConfig(string $configName = ''): static
    {
        $configFile = __DIR__ . '/../../config/packages/pipeline.yaml';
        if (!file_exists($configFile)) {
            throw new RuntimeException("Configuration file not found: $configFile");
        }

        $config = Yaml::parseFile($configFile);
        if (!isset($config['pipeline'][$configName])) {
            throw new RuntimeException("Configuration for '$configName' not found in pipeline.yaml");
        }

        $this->pipes = $config['pipeline'][$configName];

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

    /**
     * Processes the passable through the pipeline and returns the final result.
     *
     * This method applies all the pipes in reverse order and passes the passable through them.
     * The final result is determined by the destination closure.
     *
     * @param Closure $destination The final closure to process the passable after all pipes.
     * @return mixed The result of processing the passable through the pipeline.
     */
    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            function (mixed $passable) use ($destination) {
                return $destination($passable);
            }
        );

        return $pipeline($this->passable);
    }

    /**
     * Processes the passable through the pipeline and returns the passable itself.
     *
     * This is a convenience method that uses `then` with an identity function.
     *
     * @return mixed The passable after being processed by the pipeline.
     */
    public function thenReturn(): mixed
    {
        return $this->then(function (mixed $passable) {
            return $passable;
        });
    }

    /**
     * Creates a closure that carries the passable through each pipe.
     *
     * This method is used internally to build the pipeline stack.
     *
     * @return Closure A closure that processes the passable through the pipes.
     */
    protected function carry(): Closure
    {
        return function (Closure $stack, Closure | string $pipe) {
            return function (mixed $passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                } elseif (is_object($pipe)) {
                    return $pipe->{$this->method}($passable, $stack);
                } elseif (is_string($pipe) && class_exists($pipe)) {
                    $pipeInstance = new $pipe;
                    return $pipeInstance->{$this->method}($passable, $stack);
                } else {
                    throw new InvalidArgumentException('Invalid pipe type.');
                }
            };
        };
    }
}

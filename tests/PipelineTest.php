<?php

namespace KleytonSantos\Pipeline\Tests;

use KleytonSantos\Pipeline\Tests\PipesMock\CapitalizePipe;
use KleytonSantos\Pipeline\Tests\PipesMock\TrimPipe;
use PHPUnit\Framework\TestCase;
use KleytonSantos\Pipeline\Pipeline;
use Mockery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PipelineTest extends TestCase
{
    private Pipeline $pipeline;
    private ContainerInterface $containerMock;

    protected function setUp(): void
    {
        $parameterBagMock = Mockery::mock(ParameterBagInterface::class);
        $this->containerMock = Mockery::mock(ContainerInterface::class);

        $parameterBagMock
            ->shouldReceive('get')
            ->with('pipeline.config')
            ->andReturn([
                'test_config' => [
                    CapitalizePipe::class,
                    TrimPipe::class
                ]
            ]);

        $this->pipeline = new Pipeline($parameterBagMock, $this->containerMock);
    }

    public function testSendAssignsPassable()
    {
        $result = $this->pipeline->send('Test Value');

        $this->assertInstanceOf(Pipeline::class, $result);
    }

    public function testWithConfigThrowsExceptionForInvalidConfig()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Configuration for 'invalid_config' not found in pipeline config");

        $this->pipeline->withConfig('invalid_config');
    }

    public function testWithConfigLoadsPipes()
    {
        $this->pipeline->withConfig('test_config');

        $reflection = new \ReflectionClass($this->pipeline);
        $pipesProperty = $reflection->getProperty('pipes');

        $this->assertSame([CapitalizePipe::class, TrimPipe::class], $pipesProperty->getValue($this->pipeline));
    }

    public function testThroughSetsPipes()
    {
        $pipes = ['PipeA', 'PipeB'];
        $this->pipeline->through($pipes);

        $reflection = new \ReflectionClass($this->pipeline);
        $pipesProperty = $reflection->getProperty('pipes');

        $this->assertSame($pipes, $pipesProperty->getValue($this->pipeline));
    }

    public function testThenProcessesPipeline()
    {
        $this->containerMock
            ->shouldReceive('get')
            ->with(CapitalizePipe::class)
            ->andReturn(new CapitalizePipe());

        $this->containerMock
            ->shouldReceive('get')
            ->with(TrimPipe::class)
            ->andReturn(new TrimPipe());

        $result = $this->pipeline
            ->send('test')
            ->withConfig('test_config')
            ->then(fn ($passable) => $passable . ' done');

        $this->assertSame('Test done', $result);
    }

    public function testThenReturnReturnsFinalValue()
    {
        $this->containerMock
            ->shouldReceive('get')
            ->with(CapitalizePipe::class)
            ->andReturn(new CapitalizePipe());

        $this->containerMock
            ->shouldReceive('get')
            ->with(TrimPipe::class)
            ->andReturn(new TrimPipe());

        $result = $this->pipeline
            ->send('   test      ')
            ->through([CapitalizePipe::class, TrimPipe::class])
            ->thenReturn();

        $this->assertSame('Test', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

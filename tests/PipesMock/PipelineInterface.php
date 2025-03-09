<?php

namespace KleytonSantos\Pipeline\Tests\PipesMock;

interface PipelineInterface
{
    public function handle(mixed $passable, \Closure $next): mixed;
}

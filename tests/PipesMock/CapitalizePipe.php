<?php

namespace KleytonSantos\Pipeline\Tests\PipesMock;

class CapitalizePipe implements PipelineInterface
{
    public function handle(mixed $passable, \Closure $next): mixed
    {
        return $next(ucwords($passable));
    }
}

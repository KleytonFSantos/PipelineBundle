<?php

declare(strict_types=1);

namespace KleytonSantos\Pipeline\Tests\PipesMock;

class TrimPipe implements PipelineInterface
{
    public function handle(mixed $passable, \Closure $next): mixed
    {
        return $next(trim($passable));
    }
}

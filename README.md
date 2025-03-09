# PipelineBundle

**PipelineBundle** is a Symfony component that allows you to create and execute data processing pipelines in a flexible and dynamic way.

## 📦 Instalação

Installing the bundle with **Composer**, run the following command:

```bash
composer require kleytonsantos/pipeline-bundle
```

```php
// config/bundles.php
return [
    KleytonSantos\Pipeline\PipelineBundle::class => ['all' => true],
];
```

# 🚀 Uso Básico

### 1️⃣  Create a Pipeline Configuration

Adding a pipeline configuration to config/packages/pipeline.yaml:

```yaml
# config/packages/pipeline.yaml
pipeline:
    pipelines:
        my_pipeline:
            - my_step1
            - my_step2
```

### 2️⃣  Custom Pipes

```php
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
```

### 3️⃣ Execute the Pipeline

```php
$result = $pipeline
    ->send($userRegisterDTO)
    ->through([
        App\Pipe\SetPasswordPipe::class,
        App\Pipe\SendWelcomeEmailPipe::class,
    ])
    ->thenReturn();
```

##  📄 Methods Summary

| Methods                                  | Description                                        |
|------------------------------------------|----------------------------------------------------|
| `send(mixed $passable): static`          | Defines the data to be processed in the pipeline.  |
| `withConfig(string $configName): static` | Use config from pipeline.yaml.                     |
| `through(array $pipes): static`          | Pass the pipes manually.                           |
| `then(Closure $destination): mixed`      | Process the pipeline and execute a final function. |
| `thenReturn(): mixed`                    | Process the pipeline and return the final result.  |

<?php

namespace Fukibay\StarterPack\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Console\Input\InputOption;

class MakeRepositoryCommand extends GeneratorCommand
{
    protected $name = 'fukibay:make:repository';
    protected $description = 'Yeni bir repository sınıfı ve arayüzü oluşturur';
    protected $type = 'Repository';

    protected function getStub(): string
    {
        // Önemli: Stub dosyasının yolunu doğru şekilde belirtiyoruz.
        return __DIR__ . '/../../stubs/repository.class.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        // Config dosyasından sürücü klasörünün adını dinamik olarak okuyoruz.
        $driver = config('fukibay-starter-pack.repository_driver', 'PostgreSql');
        return $rootNamespace . '\\Repositories\\' . $driver;
    }

    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Bu repository\'nin ilişkili olduğu model sınıfı'],
            ['soft-deletes', 's', InputOption::VALUE_NONE, 'Soft Deletes arayüzünü ve trait\'ini uygula'],
            ['force', null, InputOption::VALUE_NONE, 'Mevcut dosyaların üzerine yaz'],
        ];
    }
    
    public function handle(): bool|null
    {
        if (!$this->option('model')) {
            $this->error('Lütfen --model seçeneği ile bir model belirtin.');
            return self::FAILURE;
        }

        if (parent::handle() === false && !$this->option('force')) {
            return self::FAILURE;
        }

        $this->createInterface();
        return self::SUCCESS;
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $serviceName = class_basename($name);
        $repositoryInterface = str_replace('Service', 'RepositoryInterface', $serviceName);
        $repositoryInterfaceFullName = $this->rootNamespace() . 'Repositories\\Contracts\\' . $repositoryInterface;
        
        // Değiştirilecek değerleri hazırla
        $replace = [
            '{{ rootNamespace }}' => rtrim($this->rootNamespace(), '\\') . '\\',
            '{{ repositoryInterfaceNamespace }}' => $repositoryInterfaceFullName,
            '{{ repositoryInterface }}' => $repositoryInterface,
            '{{ repositoryVariable }}' => Str::camel(str_replace('Interface', '', $repositoryInterface)),
            '{{ useProxyTrait }}' => '',
            '{{ proxyTrait }}' => '',
        ];

        // KRİTİK GELİŞTİRME: Repository arayüzü SoftDeletes'i destekliyor mu?
        $softDeletesInterface = 'Fukibay\\StarterPack\\Repositories\\Contracts\\SoftDeletesRepositoryInterface';
        if (class_exists($repositoryInterfaceFullName) && is_subclass_of($repositoryInterfaceFullName, $softDeletesInterface)) {
            $replace['{{ useProxyTrait }}'] = "use Fukibay\\StarterPack\\Traits\\ProxiesSoftDeletes;";
            $replace['{{ proxyTrait }}'] = "use ProxiesSoftDeletes;\n";
        }

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }
    
    protected function createInterface(): void
    {
        $name = $this->qualifyClass($this->getNameInput());
        $interfaceName = class_basename($name) . 'Interface';
        $interfaceNamespace = $this->rootNamespace() . 'Repositories\\Contracts';
        $interfacePath = $this->getPath($interfaceNamespace . '\\' . $interfaceName);
        
        if ($this->files->exists($interfacePath) && !$this->option('force')) {
            $this->line("<fg=yellow>Arayüz zaten var:</> {$interfaceName}");
            return;
        }

        $this->makeDirectory($interfacePath);

        $stub = $this->files->get(__DIR__ . '/../../stubs/repository.interface.stub');

        $replace = [
            '{{ namespace }}' => $interfaceNamespace,
            '{{ rootNamespace }}' => rtrim($this->rootNamespace(), '\\') . '\\',
            '{{ class }}' => $interfaceName,
            '{{ useSoftDeletesInterface }}' => '',
            '{{ interfaceExtends }}' => '',
        ];

        if ($this->needsSoftDeletes()) {
            $replace['{{ useSoftDeletesInterface }}'] = "use {$this->rootNamespace()}Repositories\Contracts\SoftDeletesRepositoryInterface;";
            $replace['{{ interfaceExtends }}'] = ' extends SoftDeletesRepositoryInterface';
        }

        $this->files->put($interfacePath, str_replace(array_keys($replace), array_values($replace), $stub));
        $this->info("Arayüz oluşturuldu: {$interfaceName}");
    }

    protected function needsSoftDeletes(): bool
    {
        if ($this->option('soft-deletes')) {
            return true;
        }
        $modelClass = $this->parseModel($this->option('model'));
        if (!class_exists($modelClass)) {
            return false;
        }
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);
    }
}
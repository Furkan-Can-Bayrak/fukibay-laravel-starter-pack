<?php

namespace Fukibay\StarterPack\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeServiceCommand extends GeneratorCommand
{
    protected $name = 'fukibay:make:service';
    protected $description = 'Yeni bir servis sınıfı oluşturur ve ilgili repository\'yi inject eder';
    protected $type = 'Service';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/service.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Services';
    }

    /**
     * Stub dosyasını alıp, placeholder'ları gerçek değerlerle doldurarak sınıfı oluşturur.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $serviceName = class_basename($name);
        $repositoryInterface = str_replace('Service', 'RepositoryInterface', $serviceName);
        $repositoryInterfaceFullName = $this->rootNamespace() . '\\Repositories\\Contracts\\' . $repositoryInterface;

        $replace = [
            '{{ rootNamespace }}' => rtrim($this->rootNamespace(), '\\') . '\\',
            '{{ repositoryInterfaceNamespace }}' => $repositoryInterfaceFullName,
            '{{ repositoryInterface }}' => $repositoryInterface,
            '{{ repositoryVariable }}' => Str::camel(str_replace('Interface', '', $repositoryInterface)),
            // Başlangıçta Soft Deletes ile ilgili placeholder'ları boş bırakalım.
            '{{ useProxyTrait }}' => '',
            '{{ proxyTrait }}' => '',
        ];

        // AKILLI KISIM: Repository'nin Soft Deletes destekleyip desteklemediğini kontrol et.
        $softDeletesInterface = $this->rootNamespace() . '\\Repositories\\Contracts\\SoftDeletesRepositoryInterface';
        if (interface_exists($repositoryInterfaceFullName) && is_subclass_of($repositoryInterfaceFullName, $softDeletesInterface)) {
            $this->line('<fg=cyan>Info:</> Soft Deletes destekli repository algılandı, ProxiesSoftDeletes traiti ekleniyor.');

            $proxiesSoftDeletesTrait = 'Fukibay\StarterPack\Traits\ProxiesSoftDeletes';

            $replace['{{ useProxyTrait }}'] = "use {$proxiesSoftDeletesTrait};";
            $replace['{{ proxyTrait }}'] = "    use ProxiesSoftDeletes;\n";
        }

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Servis zaten mevcut olsa bile üzerine yazarak oluşturur'],
        ];
    }
}
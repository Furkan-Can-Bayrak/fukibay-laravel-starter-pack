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
     * Stub'u kendimiz dolduruyoruz (parent::buildClass KULLANMIYORUZ).
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        // Sınıf adı ve namespace
        $class = class_basename($name);
        $namespace = $this->getNamespace($name);
        $rootNs = rtrim($this->rootNamespace(), '\\') . '\\';

        // Repository interface ismi ve FQN’i
        $repositoryInterface = str_replace('Service', 'RepositoryInterface', $class);
        $repositoryInterfaceFqn = $rootNs . 'Repositories\\Contracts\\' . $repositoryInterface;

        // Varsayılan replace seti
        $replace = [
            '{{ namespace }}'              => $namespace,
            '{{ class }}'                  => $class,
            '{{ rootNamespace }}'          => $rootNs,
            '{{ repositoryInterfaceUse }}' => "use {$repositoryInterfaceFqn};",
            '{{ repositoryInterface }}'    => $repositoryInterface,
            '{{ repositoryVariable }}'     => Str::camel(str_replace('Interface', '', $repositoryInterface)),
            '{{ useProxyUse }}'            => '',
            '{{ proxyTrait }}'             => '',
        ];

        // SoftDeletes desteği algılama (opsiyonel)
        $softDeletesInterfaceFqn = $rootNs . 'Repositories\\Contracts\\SoftDeletesRepositoryInterface';
        $proxiesSoftDeletesTraitFqn = 'Fukibay\\StarterPack\\Traits\\ProxiesSoftDeletes';

        if (interface_exists($repositoryInterfaceFqn)
            && interface_exists($softDeletesInterfaceFqn)
            && is_subclass_of($repositoryInterfaceFqn, $softDeletesInterfaceFqn)) {

            $this->line('<fg=cyan>Info:</> Soft Deletes destekli repository algılandı, ProxiesSoftDeletes traiti ekleniyor.');

            $replace['{{ useProxyUse }}'] = "use {$proxiesSoftDeletesTraitFqn};";
            $replace['{{ proxyTrait }}']  = "use ProxiesSoftDeletes;\n";
        }

        return str_replace(array_keys($replace), array_values($replace), $stub);
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Servis zaten mevcut olsa bile üzerine yazarak oluşturur'],
        ];
    }
}

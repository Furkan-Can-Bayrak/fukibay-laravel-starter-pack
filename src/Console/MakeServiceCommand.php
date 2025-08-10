<?php

namespace Fukibay\StarterPack\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeServiceCommand extends GeneratorCommand
{
    /**
     * Komutun adı ve imzası.
     */
    protected $name = 'fukibay:make:service';

    /**
     * Komutun açıklaması.
     */
    protected $description = 'Yeni bir servis sınıfı oluşturur ve ilgili repository\'yi inject eder';

    /**
     * Oluşturulan dosyanın türü (hata mesajları için).
     */
    protected $type = 'Service';

    /**
     * Oluşturulacak sınıf için kullanılacak stub dosyasının yolunu döndürür.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/service.stub';
    }

    /**
     * Sınıf için varsayılan namespace'i belirler.
     * Servisler her zaman 'App\Services' altına oluşturulur.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Services';
    }

    /**
     * Stub dosyasındaki placeholder'ları gerçek değerlerle değiştirir.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $serviceName = class_basename($name); // Örn: UserService
        
        // Servis adından Repository adını tahmin et
        // "UserService" -> "UserRepositoryInterface"
        $repositoryInterface = str_replace('Service', 'RepositoryInterface', $serviceName);

        // Değiştirilecek değerleri hazırla
        $replace = [
            '{{ rootNamespace }}' => rtrim($this->rootNamespace(), '\\') . '\\',
            '{{ repositoryInterfaceNamespace }}' => $this->rootNamespace() . 'Repositories\\Contracts\\' . $repositoryInterface,
            '{{ repositoryInterface }}' => $repositoryInterface,
            '{{ repositoryVariable }}' => Str::camel(str_replace('Interface', '', $repositoryInterface)), // Örn: userRepository
        ];

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }
}
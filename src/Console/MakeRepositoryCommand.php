<?php

namespace Fukibay\StarterPack\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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

    /**
     * Stub dosyasını alıp, placeholder'ları gerçek değerlerle doldurarak sınıfı oluşturur.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $modelClass = $this->parseModel($this->option('model'));
        $modelName = class_basename($modelClass);
        $interfaceName = class_basename($name) . 'Interface';

        $replace = [
            '{{ interface }}' => $interfaceName,
            '{{ modelNamespace }}' => $modelClass,
            '{{ modelName }}' => $modelName,
            // Başlangıçta Soft Deletes ile ilgili her şeyi boş bırak
            '{{ useSoftDeletesInterface }}' => '',
            '{{ useHandlesSoftDeletesTrait }}' => '', // <-- YENİ
            '{{ classImplements }}' => '',
            '{{ useAndImplementHandlesSoftDeletes }}' => '',
        ];

        if ($this->needsSoftDeletes()) {
            $this->line('<fg=cyan>Info:</> Soft Deletes algılandı, HandlesSoftDeletes traiti ve arayüzü ekleniyor.');

            $softDeletesInterface = $this->rootNamespace() . 'Repositories\Contracts\SoftDeletesRepositoryInterface';
            $handlesSoftDeletesTrait = 'Fukibay\StarterPack\Traits\HandlesSoftDeletes'; // <-- YENİ

            $replace['{{ useSoftDeletesInterface }}'] = "use {$softDeletesInterface};";
            $replace['{{ useHandlesSoftDeletesTrait }}'] = "use {$handlesSoftDeletesTrait};"; // <-- YENİ
            $replace['{{ classImplements }}'] = ', SoftDeletesRepositoryInterface';
            // DÜZELTME: Başındaki '\' karakterini kaldırdık.
            $replace['{{ useAndImplementHandlesSoftDeletes }}'] = "    use HandlesSoftDeletes;\n";
        }

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $stub
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
            // === DÜZELTME BURADA: 'extends' yerine ',' kullanıyoruz ===
            $replace['{{ interfaceExtends }}'] = ', SoftDeletesRepositoryInterface';
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

    /**
     * Verilen model adını tam ve nitelikli bir class adına çevirir.
     * Örn: "User" -> "App\Models\User"
     *
     * @param  string  $model
     * @return string
     */
    protected function parseModel($model)
    {
        // Eğer kullanıcı zaten tam yolu verdiyse (App\Models\User), dokunma.
        if (Str::startsWith($model, $this->rootNamespace())) {
            return $model;
        }

        // Standart model namespace'ini al (App\Models\)
        $modelNamespace = $this->laravel->getNamespace() . 'Models\\';

        // Kısa adı tam yola çevir ve döndür.
        return $modelNamespace . str_replace('/', '\\', $model);
    }
}
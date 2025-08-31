<?php
declare(strict_types=1);

namespace Fukibay\StarterPack\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'fukibay:install {--force : Mevcut dosyaların üzerine yazılsın mı?}';
    protected $description = 'Fukibay Laravel Starter Pack için temel dosyaları kurar.';

    public function handle(Filesystem $filesystem): int
    {
        $this->info('Fukibay Laravel Starter Pack kuruluyor...');

        // KRİTİK DÜZELTME: Config'den sürücü adını al
        $driver = config('fukibay-starter-pack.repository_driver', 'PostgreSql');
        $baseRepoDestination = app_path('Repositories/' . $driver . '/BaseRepository.php');

        $stubs = [
            // Gerekli arayüzler ve sınıflar
            'App/Repositories/Contracts/BaseRepositoryInterface.stub'        => app_path('Repositories/Contracts/BaseRepositoryInterface.php'),
            'App/Repositories/Contracts/SoftDeletesRepositoryInterface.stub' => app_path('Repositories/Contracts/SoftDeletesRepositoryInterface.php'),
            'App/Repositories/Criteria/QueryParameters.stub'                 => app_path('Repositories/Criteria/QueryParameters.php'),
            'App/Repositories/PostgreSql/BaseRepository.stub'                => $baseRepoDestination, // Dinamik yol kullanıldı
            'App/Services/BaseService.stub'                                  => app_path('Services/BaseService.php'),
            'App/Traits/ApiResponder.stub'                                   => app_path('Traits/ApiResponder.php'),
            'App/Exceptions/Handler.stub'                                    => app_path('Exceptions/Handler.php'),
            'App/Traits/HandlesFiles.stub' => app_path('Traits/HandlesFiles.php'),
        ];

        foreach ($stubs as $sourceStub => $destinationPath) {
            $sourcePath = dirname(__DIR__, 2) . '/stubs/' . $sourceStub;

            if ($filesystem->exists($destinationPath) && !$this->option('force')) {
                $this->line('Atlandı (dosya zaten var): ' . str_replace(base_path() . '/', '', $destinationPath));
                continue;
            }

            $filesystem->ensureDirectoryExists(dirname($destinationPath));
            
            // Placeholderları değiştirerek kopyala
            $content = $this->replacePlaceholders($filesystem->get($sourcePath), $driver);
            $filesystem->put($destinationPath, $content);

            $this->info('Yazıldı: ' . str_replace(base_path() . '/', '', $destinationPath));
        }

        $this->comment('✅ Kurulum başarıyla tamamlandı.');
        return self::SUCCESS;
    }
    
    protected function replacePlaceholders(string $content, string $driver): string
    {
        $appNamespace = rtrim(app()->getNamespace(), '\\');

        // BaseRepository.stub dosyasının namespace'ini dinamik yap
        return str_replace(
            ['{{ namespace }}', 'App\\Repositories\\PostgreSql'],
            [$appNamespace, $appNamespace . '\\Repositories\\' . $driver],
            $content
        );
    }
}
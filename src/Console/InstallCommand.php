<?php
declare(strict_types=1);

namespace Fukibay\StarterPack\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * Komutun terminaldeki imzası ve tanımı.
     * @var string
     */
    protected $signature = 'fukibay:install {--force : Mevcut dosyaların üzerine yazılsın mı?}';

    /**
     * Komutun açıklaması.
     * @var string
     */
    protected $description = 'Fukibay Laravel Starter Pack için temel dosyaları ve yapılandırmayı interaktif olarak kurar.';

    /**
     * Komutun ana iş mantığını yürütür.
     */
    public function handle(Filesystem $filesystem): int
    {
        $this->displayHeader();

        // Adım 1: Yapılandırma dosyasını vendor'dan projenin config klasörüne yayınla.
        $this->publishConfiguration();
        
        // Adım 2: Kullanıcıya interaktif olarak desteklenen veritabanı sürücülerinden birini seçtir.
        $driver = $this->askForDatabaseDriver();
        
        // Adım 3: Kullanıcının seçimini config dosyasına otomatik olarak yaz.
        $this->updateDriverInConfigFile($driver);
        $this->info("Veritabanı sürücüsü <fg=yellow>{$driver}</> olarak ayarlandı.");
        $this->newLine();

        // Adım 4: Gerekli tüm temel dosyaları (stub'ları) kullanıcının app dizinine kopyala.
        $this->info('2. Gerekli arayüz, trait ve temel sınıflar kopyalanıyor...');
        $this->copyStubs($filesystem, $driver);
        $this->newLine();

        // Adım 5: Kurulum sonrası kullanıcıya sonraki adımlar hakkında rehberlik et.
        $this->displayNextSteps();

        return self::SUCCESS;
    }

    /**
     * Komut için güzel bir başlık gösterir.
     */
    protected function displayHeader(): void
    {
        $this->line(" ╔═══════════════════════════════════════════════╗");
        $this->line(" ║ <fg=blue;options=bold>Fukibay Laravel Starter Pack Kurulum Sihirbazı</> ║");
        $this->line(" ╚═══════════════════════════════════════════════╝");
        $this->newLine();
    }

    /**
     * Paketin yapılandırma dosyasını `config` klasörüne yayınlar.
     */
    protected function publishConfiguration(): void
    {
        $this->info('1. Yapılandırma dosyası yayınlanıyor...');
        
        $configPath = config_path('fukibay-starter-pack.php');
        if (file_exists($configPath) && !$this->option('force')) {
            $this->line('<fg=yellow>Atlandı:</> Yapılandırma dosyası zaten mevcut. Üzerine yazmak için --force kullanın.');
            $this->newLine();
            return;
        }

        $this->call('vendor:publish', [
            '--provider' => 'Fukibay\StarterPack\StarterPackServiceProvider',
            '--tag' => 'fukibay-config',
            '--force' => $this->option('force'),
        ]);
        $this->newLine();
    }

    /**
     * Kullanıcıya desteklenen veritabanı sürücüleri arasından seçim yapmasını ister.
     */
    protected function askForDatabaseDriver(): string
    {
        $this->comment('Repository\'leriniz için bir veritabanı sürücüsü seçin.');
        $this->comment('Bu seçim, BaseRepository dosyasının hangi alt klasöre oluşturulacağını belirleyecektir.');

        return $this->choice(
            'Hangi veritabanı sürücüsünü kullanıyorsunuz?',
            ['PostgreSql', 'MySql'],
            'PostgreSql'
        );
    }

    /**
     * Kullanıcının seçtiği sürücüyü `config/fukibay-starter-pack.php` dosyasına yazar.
     */
    protected function updateDriverInConfigFile(string $driver): void
    {
        $configPath = config_path('fukibay-starter-pack.php');
        
        if (! file_exists($configPath)) { return; }
        
        $content = file_get_contents($configPath);
        
        $newContent = preg_replace(
            "/('repository_driver'\s*=>\s*)'[^']*'/",
            "\${1}'{$driver}'",
            $content
        );

        file_put_contents($configPath, $newContent);
    }

    /**
     * Gerekli stub dosyalarını `app` dizinine, doğru yollara kopyalar.
     */
    protected function copyStubs(Filesystem $filesystem, string $driver): void
    {
        $baseRepoDestination = app_path('Repositories/' . $driver . '/BaseRepository.php');

        $stubs = [
            'App/Repositories/Contracts/BaseRepositoryInterface.stub' => app_path('Repositories/Contracts/BaseRepositoryInterface.php'),
            'App/Repositories/Contracts/SoftDeletesRepositoryInterface.stub' => app_path('Repositories/Contracts/SoftDeletesRepositoryInterface.php'),
            'App/Repositories/Criteria/QueryParameters.stub' => app_path('Repositories/Criteria/QueryParameters.php'),
            'App/Repositories/BaseRepository.stub' => $baseRepoDestination,
            
            // DÜZELTME: Hedef dosya adı .stub değil .php olmalı.
            'App/Services/BaseService.stub' => app_path('Services/BaseService.php'),
            
            'App/Traits/ApiResponder.stub' => app_path('Traits/ApiResponder.php'),
            'App/Exceptions/Handler.stub' => app_path('Exceptions/Handler.php'),
            'App/Traits/HandlesFiles.stub' => app_path('Traits/HandlesFiles.php'),
        ];
        
        foreach ($stubs as $sourceStub => $destinationPath) {
            $sourcePath = dirname(__DIR__, 2) . '/stubs/' . $sourceStub;

            if ($filesystem->exists($destinationPath) && !$this->option('force')) {
                $this->line('  <fg=yellow>Atlandı:</> ' . str_replace(base_path() . '/', '', $destinationPath));
                continue;
            }

            $filesystem->ensureDirectoryExists(dirname($destinationPath));
            $content = $this->replacePlaceholders($filesystem->get($sourcePath), $driver);
            $filesystem->put($destinationPath, $content);
            $this->line('  <fg=green>Yazıldı:</> ' . str_replace(base_path() . '/', '', $destinationPath));
        }
    }
    
    /**
     * Stub dosyalarının içeriğindeki placeholder'ları gerçek değerlerle değiştirir.
     */
    protected function replacePlaceholders(string $content, string $driver): string
    {
        $appNamespace = rtrim(app()->getNamespace(), '\\');

        return str_replace(
            ['{{ namespace }}', '{{ driver }}'],
            [$appNamespace, $driver],
            $content
        );
    }
    
    /**
     * Kurulum sonrası kullanıcıya sonraki adımlar hakkında rehberlik eder.
     */
    protected function displayNextSteps(): void
    {
        $this->line(" <fg=green;options=bold>✅ Kurulum başarıyla tamamlandı!</>");
        $this->line(" Sonraki Adımlar:");
        $this->line("   1. İlk repository'nizi oluşturun: <fg=yellow>php artisan fukibay:make:repository UserRepository --model=User</>");
        $this->line("   2. İlgili servisi oluşturun: <fg=yellow>php artisan fukibay:make:service UserService</>");
        $this->line("   3. `AppServiceProvider` içinde arayüzü bağlayın (bind).");
    }
}
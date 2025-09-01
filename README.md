# Fukibay Laravel Starter Pack

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

**Fukibay Laravel Starter Pack**, Repository ve Service katmanlarını kullanarak temiz mimariyle proje geliştirenler için tasarlanmış, akıllı bir kod üretim (scaffolding) ve altyapı kurulum paketidir. Tekrarlayan kurulum ve kodlama adımlarını **tek bir interaktif komutla** otomatize ederek, doğrudan projenizin iş mantığına odaklanmanızı sağlar.

## 🎯 Felsefemiz

Bu paket, "örtük" varsayımlar yerine **açık ve net** komutları tercih eder. Amacımız, geliştirme sürecinizi hızlandırırken, kodun kontrolünün daima sizde kalmasını sağlamaktır. Ürettiğimiz kodun ve kurduğumuz altyapının güvenilir, esnek ve öngörülebilir olmasını garanti ederiz.

## 🚀 Temel Özellikler

-   **🧠 Akıllı Kurulum Sihirbazı:** Tek bir komutla (`fukibay:install`) tüm temel altyapıyı kurun. Sihirbaz, size interaktif olarak veritabanı sürücünüzü sorar ve yapılandırmanızı otomatik olarak yapar.
-   **Akıllı Kod Üretimi:** `make` komutları ile saniyeler içinde, projenizin yapısına tam uyumlu Repository ve Service sınıfları oluşturun.
-   **🤖 Otomatik Soft Deletes Entegrasyonu:**
    -   Repository oluştururken modelinizdeki `SoftDeletes` trait'ini **otomatik olarak algılar**.
    -   Service oluştururken, ilgili repository'nin soft-delete destekli olup olmadığını **anlar** ve gerekli `ProxiesSoftDeletes` trait'ini sınıfa **otomatik olarak ekler**.
-   **⚡ Güçlü ve Esnek Sorgulama:** `QueryParameters` DTO'su sayesinde karmaşık `where`, `relation`, `orderBy`, `limit`, `scope` ve `exists` gibi sorguları zincirlemeden, tek bir nesne ile temiz bir şekilde yapın.
-   **🛠️ Kullanıma Hazır Yardımcılar:** Kurulumla birlikte gelen `ApiResponder`, `HandlesFiles` gibi trait'lerle API yanıtlarınızı ve dosya yönetimini anında standartlaştırın.
-   **🛡️ Otomatik API Hata Yönetimi:** Kurulum, `ValidationException` gibi yaygın API hatalarını otomatik olarak yakalayıp standart bir JSON formatında döndüren bir `Handler.php` dosyası içerir.
-   **Veritabanı Sürücü Desteği:** Repository'lerinizi `PostgreSql` ve `MySql` için özel alt klasörlerde oluşturarak projenizi düzenli tutar.

## 📦 Kurulum

Paketi projenize kurmak ve çalışır hale getirmek sadece iki adımdır.

#### Adım 1: Composer ile Paketi Yükleyin

```bash
composer require fukibay/laravel-starter-pack
```

#### Adım 2: Akıllı Kurulum Sihirbazını Çalıştırın

```bash
php artisan fukibay:install
```
Bu komut, size hangi veritabanı sürücüsünü kullandığınızı soracak ve ardından gerekli tüm yapılandırma ve temel dosyaları projenize otomatik olarak kuracaktır.

```shell
 ╔═══════════════════════════════════════════════╗
 ║ Fukibay Laravel Starter Pack Kurulum Sihirbazı ║
 ╚═══════════════════════════════════════════════╝

 1. Yapılandırma dosyası yayınlanıyor...

 Repository'leriniz için bir veritabanı sürücüsü seçin.
 Bu seçim, dosyaların hangi alt klasöre oluşturulacağını belirleyecektir.

 Hangi veritabanı sürücüsünü kullanıyorsunuz?
  PostgreSql
  MySql
 > 0

Veritabanı sürücüsü PostgreSql olarak ayarlandı.

 2. Gerekli arayüz, trait ve temel sınıflar kopyalanıyor...
  Yazıldı: app/Repositories/Contracts/BaseRepositoryInterface.php
  ...

 ✅ Kurulum başarıyla tamamlandı!
 ...
```

## 🛠️ Kullanım Akışı

### Adım 1: Repository Oluşturma

Bir Eloquent Modeli'ne bağlı yeni bir repository ve arayüzü oluşturmak için `fukibay:make:repository` komutunu kullanın.

```bash
php artisan fukibay:make:repository UserRepository --model=User
```

Bu komut iki dosya oluşturur:
1.  **Arayüz:** `app/Repositories/Contracts/UserRepositoryInterface.php`
2.  **Sınıf:** `app/Repositories/PostgreSql/UserRepository.php` (yapılandırmanıza göre)

> **Neden `--model` parametresi zorunlu?**
> Model adını repository adından tahmin etmek (`UserRepository` -> `User`) basit durumlarda işe yarasa da, karmaşık isimlendirmelerde ve farklı namespace yapılarında hatalara yol açabilir. `--model` parametresini zorunlu kılarak, hangi modelin kullanılacağını **açıkça belirtmenizi** sağlıyor ve böylece %100 güvenilir ve hatasız kod üretiyoruz. Bu, paketin temel felsefesidir.

**Soft Deletes Algılaması:**
Eğer `User` modeliniz `Illuminate\Database\Eloquent\SoftDeletes` trait'ini kullanıyorsa, oluşturulan `UserRepository` sınıfı ve arayüzü bunu **otomatik olarak algılayıp** `SoftDeletesRepositoryInterface`'i ve ilgili trait'i uygulayacaktır.

### Adım 2: Service Oluşturma (Akıllı Kısım)

İlgili repository'yi kullanan bir servis sınıfı oluşturun.

```bash
php artisan fukibay:make:service UserService
```

**Paketin zekası burada devreye giriyor:**
-   Komut, `UserService` için `UserRepositoryInterface`'in gerektiğini anlar.
-   Daha sonra bu arayüzün `SoftDeletesRepositoryInterface`'i genişletip genişletmediğini kontrol eder.
-   Eğer cevap **evet** ise, `ProxiesSoftDeletes` trait'ini servis sınıfına **otomatik olarak ekler!**

**Örnek Çıktı (Eğer User modeli SoftDeletes kullanıyorsa):**

```php
// app/Services/UserService.php (Otomatik oluşturulan kod)
namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Fukibay\StarterPack\Traits\ProxiesSoftDeletes; // OTOMATİK EKLENDİ!

class UserService extends BaseService
{
    use ProxiesSoftDeletes; // OTOMATİK EKLENDİ!

    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct($userRepository);
    }
}```
Bu sayede, soft-delete metotlarını kullanmak için herhangi bir ek işlem yapmanıza gerek kalmaz.

### Adım 3: Service Provider ile Bağlama

Oluşturduğunuz arayüzleri ve sınıfları Laravel'in Service Container'ına tanıtın.

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\PostgreSql\UserRepository; // Yapılandırmanıza göre doğru yolu seçin

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
    }
    // ...
}
```

### Adım 4: Gelişmiş Sorgular (`QueryParameters`)

Paketin en güçlü yanlarından biri, `QueryParameters` DTO'su ile karmaşık ve okunabilir sorgular yapabilmektir.

**Örnek Senaryo:** Onaylanmış (`status=approved`), puanı 80'den yüksek (`score > 80`), profili (`profile` ilişkisi) olan ve en yeniye göre sıralanmış kullanıcıları getirelim.

```php
// Bir Controller veya başka bir Service içinde...
use App\Services\UserService;
use App\Repositories\Criteria\QueryParameters;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index()
    {
        $criteria = new QueryParameters(
            filters: [
                'status'     => 'approved',
                'score'      => ['>', 80],
                'exists'     => ['profile'], // 'profile' ilişkisi olanlar
            ],
            relations: ['profile', 'posts'], // Eager loading
            orderBy: ['created_at' => 'desc']
        );

        $users = $this->userService->paginate($criteria);

        // ...
    }
}
```

**Desteklenen Filtre Operatörleri:**
`=`, `!=`, `>`, `>=`, `<`, `<=`, `like`, `date`, `in`, `between`, `null`, `not_null`, `exists` (ilişki var mı?), `not_exists` (ilişki yok mu?). Ayrıca dot notasyonu ile ilişkisel alanlarda da filtreleme yapabilirsiniz (`profile.city` => 'Ankara').

### Adım 5: Kurulumla Gelen Yardımcıları Kullanma

`fukibay:install` komutu, işinizi kolaylaştıracak bazı trait'leri projenize kurar.

#### `ApiResponder` Trait'i
API yanıtlarınızı `app/Traits/ApiResponder.php` içindeki bu trait ile standartlaştırın.

```php
// Örnek bir Controller'da
use App\Traits\ApiResponder;

class UserController extends Controller
{
    use ApiResponder;

    public function show($id)
    {
        $user = $this->userService->findById($id);
        if (!$user) {
            return $this->error('Kullanıcı bulunamadı', 404);
        }
        return $this->success(new UserResource($user));
    }
}
```

#### `HandlesFiles` Trait'i
Dosya yükleme, silme ve güncelleme işlemlerini `app/Traits/HandlesFiles.php` içindeki bu trait ile kolayca yapın.

```php
// Örnek bir Service'te
use App\Traits\HandlesFiles;

class UserService extends BaseService
{
    use HandlesFiles;

    public function updateUserAvatar(int $userId, UploadedFile $avatar)
    {
        $user = $this->findByIdOrFail($userId);
        
        // Eski avatarı silip yenisini yükler ve yolunu döner
        $path = $this->updateFile($avatar, 'avatars', $user->avatar_path);
        
        return $this->update($userId, ['avatar_path' => $path]);
    }
}
```

## 🎯 Komutların Özeti

| Komut | Açıklama |
|---|---|
| `fukibay:ping` | Paketin doğru kurulup kurulmadığını test eder. |
| `fukibay:install` | Gerekli temel arayüz, trait ve sınıfları interaktif sihirbaz ile `app` dizinine kurar. |
| `fukibay:make:repository <Ad> --model=<Model>` | Yeni bir repository sınıfı ve arayüzü oluşturur. |
| `fukibay:make:service <Ad>` | Yeni bir servis sınıfı oluşturur ve ilgili repository'yi akıllıca enjekte eder. |

---

Bu paket, **Furkan Can Bayrak** tarafından geliştirilmiştir. Katkıda bulunmak isterseniz, lütfen GitHub reposu üzerinden pull request gönderin.
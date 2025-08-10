# Fukibay Laravel Starter Pack

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

**Fukibay Laravel Starter Pack**, Repository ve Service katmanlarını kullanarak temiz mimariyle proje geliştirenler için tasarlanmış, akıllı bir kod üretim (scaffolding) paketidir. Tekrarlayan kurulum ve kodlama adımlarını otomatize ederek, doğrudan projenizin iş mantığına odaklanmanızı sağlar.

## 🎯 Felsefemiz

Bu paket, "örtük" varsayımlar yerine **açık ve net** komutları tercih eder. Amacımız, geliştirme sürecinizi hızlandırırken, kodun kontrolünün daima sizde kalmasını sağlamaktır. Bu sayede, projeniz ne kadar büyürse büyüsün, ürettiğimiz kodun güvenilir ve tahmin edilebilir olmasını garanti ederiz.

## 🚀 Temel Özellikler

-   **Hızlı Kurulum:** Tek komutla (`fukibay:install`) tüm temel arayüz, sınıf ve trait'leri projenize kurun.
-   **Akıllı Kod Üretimi:** `make` komutları ile saniyeler içinde Repository ve Service sınıfları oluşturun.
-   **🧠 Akıllı Soft Deletes Entegrasyonu:**
    -   Repository oluştururken modelinizdeki `SoftDeletes` trait'ini **otomatik olarak algılar**.
    -   Service oluştururken, ilgili repository'nin soft-delete destekli olup olmadığını **anlar** ve gerekli `ProxiesSoftDeletes` trait'ini sınıfa **otomatik olarak ekler**.
-   **Güçlü ve Esnek Filtreleme:** `QueryParameters` DTO'su sayesinde karmaşık `where`, `relation`, `orderBy`, `limit`, `exists` gibi sorguları zincirlemeden, tek bir nesne ile temiz bir şekilde yapın.
-   **Temiz Mimari:** Repository ve Service katmanlarını standartlaştırarak kodunuzun daha okunabilir ve yönetilebilir olmasını sağlar.
-   **Dinamik Sürücü Desteği:** Repository'lerinizi `PostgreSql`, `MySql` gibi veritabanı sürücülerine özel alt klasörlerde oluşturarak projenizi düzenli tutar.

## 📦 Kurulum

1.  Composer ile paketi projenize ekleyin:
    ```bash
    composer require fukibay/laravel-starter-pack
    ```

2.  Paketin yapılandırma dosyasını projenizin `config` klasörüne yayınlayın.
    ```bash
    php artisan vendor:publish --tag="fukibay-config"
    ```
    Bu komut, `config/fukibay-starter-pack.php` dosyasını oluşturacaktır.

3.  Paketin temel iskelet dosyalarını (`BaseRepository`, arayüzler vb.) projenize kurun.
    ```bash
    php artisan fukibay:install
    ```
    Dosyalar, yapılandırmanıza uygun olarak `app/` dizini altına yerleştirilecektir.

## ⚙️ Yapılandırma

`config/fukibay-starter-pack.php` dosyasını açarak, repository'lerinizin hangi veritabanı sürücüsü klasörü altında oluşturulacağını belirleyebilirsiniz.

```php
// config/fukibay-starter-pack.php
return [
    'repository_driver' => 'PostgreSql', // Burayı 'MySql', 'MongoDb' vb. olarak değiştirebilirsiniz.
];
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
}
```
Bu sayede, soft-delete metotlarını kullanmak için herhangi bir ek işlem yapmanıza gerek kalmaz.

### Adım 3: Service Provider ile Bağlama

Oluşturduğunuz arayüzleri ve sınıfları Laravel'in Service Container'ına tanıtın.

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\PostgreSql\UserRepository;

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

        $users = $this->userService->get($criteria);

        // ...
    }
}
```

**Desteklenen Filtre Operatörleri:**
`=`, `!=`, `>`, `>=`, `<`, `<=`, `like`, `date`, `in`, `between`, `null`, `not_null`, `exists` (ilişki var mı?), `not_exists` (ilişki yok mu?). Ayrıca dot notasyonu ile ilişkisel alanlarda da filtreleme yapabilirsiniz (`profile.city` => 'Ankara').

### Adım 5: Soft Deletes Metotlarını Kullanma

`ProxiesSoftDeletes` trait'i sayesinde servis katmanı üzerinden tüm soft-delete işlemlerini rahatça yapabilirsiniz.

```php
// UserController.php

// Hem aktif hem silinmiş kullanıcıları listele
$allUsers = $this->userService->withTrashed();

// Sadece silinmiş kullanıcıları sayfalı olarak listele
$trashedUsers = $this->userService->onlyTrashedPaginate();

// Silinmiş bir kullanıcıyı geri yükle
$this->userService->restore($userId);

// Bir kullanıcıyı kalıcı olarak sil
$this->userService->forceDelete($userId);
```

## 🎯 Komutların Özeti

| Komut | Açıklama |
|---|---|
| `fukibay:ping` | Paketin doğru kurulup kurulmadığını test eder. |
| `fukibay:install` | Gerekli temel arayüz, trait ve sınıfları `app` dizinine kurar. |
| `fukibay:make:repository <Ad> --model=<Model>` | Yeni bir repository sınıfı ve arayüzü oluşturur. |
| `fukibay:make:service <Ad>` | Yeni bir servis sınıfı oluşturur ve ilgili repository'yi akıllıca enjekte eder. |

---

Bu paket, **Furkan Can Bayrak** tarafından geliştirilmiştir. Katkıda bulunmak isterseniz, lütfen GitHub reposu üzerinden pull request gönderin.````
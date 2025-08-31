<?php

// config/fukibay-starter-pack.php

return [
    /**
     * Repository sınıflarının oluşturulacağı, veritabanı sürücüsüne özel
     * klasörün adı. Örneğin: PostgreSql, MySql, MongoDb.
     * Komutlar bu değeri okuyarak dosyaları doğru yere oluşturur.
     */
    'repository_driver' => 'PostgreSql',


    'storage_disk' => 'public',
];
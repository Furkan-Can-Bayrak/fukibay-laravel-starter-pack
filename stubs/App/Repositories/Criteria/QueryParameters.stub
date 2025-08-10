<?php

namespace App\Repositories\Criteria;

/**
 * Repository sorguları için hafif DTO.
 *
 * ── Desteklenen operatörler ───────────────────────────────────────────────
 *  =, !=, >, >=, <, <=, like, date, in, between, null, not_null,
 *  exists, not_exists
 *
 * ── Kullanım (ana model sütunları) ────────────────────────────────────────
 *  'status'       => 'active'                      // =
 *  'price'        => ['>', 100]                    // >, >=, <, <=, !=
 *  'name'         => ['like', 'furkan']            // LIKE (otomatik %...%)
 *  'created_at'   => ['date', '2025-08-10']        // whereDate = '2025-08-10'
 *  'id'           => ['in', [1,2,3]]               // IN
 *  'score'        => ['between', [50, 90]]         // BETWEEN
 *  'deleted_at'   => ['null']                      // IS NULL
 *  'updated_at'   => ['not_null']                  // IS NOT NULL
 *
 * ── İlişkisel filtreleme için 2 seçenek ───────────────────────────────────
 *  1) Tek dizi + dot notasyon (pratik):
 *     'profile.city'      => ['like', 'elaz']      // whereRelation('profile','city','LIKE','%elaz%')
 *     'orders.total'      => ['>=', 500]
 *     'orders.created_at' => ['date', '2025-08-01'] // date ilişkisel alanda whereHas ile uygulanır
 *
 *  2) Ayrı dizi relationFilters (daha net/validasyonu kolay):
 *     relationFilters: [
 *       'profile' => [
 *         'city' => ['like', 'elaz'],
 *       ],
 *       'profile.categories' => [
 *         'name'     => ['=', 'Electronics'],
 *         'priority' => ['>=', 2],
 *       ],
 *       'orders' => [
 *         'created_at' => ['date', '2025-08-01'],
 *         'total'      => ['>', 500],
 *       ],
 *     ]
 *
 * ── İlişki var/yok (sadece ilişki ad(lar)ı) ───────────────────────────────
 *  'exists'      => ['orders', 'roles']            // whereHas('orders'), whereHas('roles')
 *  'not_exists'  => ['bans']                       // whereDoesntHave('bans')
 *
 * Notlar:
 * - Dot notasyonlu ilişkiler (ör. "profile.categories.name") desteklenir.
 * - İlişkisel "date" operatörü, `whereRelation` closure almadığı için
 *   repository içinde `whereHas` ile uygulanır.
 * - `relationFilters` opsiyoneldir; kullanmazsan dot notasyonla devam edebilirsin.
 */
final readonly class QueryParameters
{
    /**
     * @param array<string, mixed>                $filters
     *        Ana model sütunları ve/veya dot notasyonlu ilişkisel filtreler.
     *        Örn:
     *        [
     *          'status' => ['!=', 'inactive'],
     *          'price'  => ['>', 100],
     *          'profile.city' => ['like', 'elaz'],
     *          'exists' => ['orders'],
     *        ]
     *
     * @param array<string, array<string, mixed>> $relationFilters
     *        İlişkisel filtreleri ayrı ve net biçimde geçmek için.
     *        Key: ilişki path'i (nested olabilir, örn: 'profile.categories')
     *        Val: { kolon => kural } sözlüğü.
     *        Örn:
     *        [
     *          'orders' => [
     *            'created_at' => ['date', '2025-08-01'],
     *            'total'      => ['>=', 500],
     *          ],
     *        ]
     *
     * @param array<int, string>                  $relations
     *        Eager load edilecek ilişkiler. Örn: ['profile', 'orders']
     *
     * @param array<string, 'asc'|'desc'>         $orderBy
     *        Sıralama. Örn: ['created_at' => 'desc']
     *
     * @param int|null                            $limit
     *        get() çağrılarında maksimum kayıt sayısı.
     *
     * @param array<int, string>                  $columns
     *        Seçilecek kolonlar. Varsayılan: ['*']
     */
    public function __construct(
        public array $filters = [],
        public array $relationFilters = [],
        public array $relations = [],
        public array $orderBy = [],
        public ?int $limit = null,
        public array $columns = ['*'],
    ) {}
}

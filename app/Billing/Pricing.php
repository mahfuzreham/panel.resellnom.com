<?php
declare(strict_types=1);

namespace ResellNom\Billing;

use PDO;
use RuntimeException;

final class Pricing
{
    public function __construct(private PDO $db) {}

    public function price(int $userId, string $tld, string $action, ?string $promoCode = null): float
    {
        $tld = strtolower(ltrim(trim($tld), '.'));
        if (!in_array($action, ['register','transfer','renewal'], true)) {
            throw new RuntimeException('Invalid pricing action.');
        }

        $column = $action . '_price';
        $q = $this->db->prepare("SELECT r.id AS registrar_id, COALESCE(rp.$column, tp.$column) AS price
            FROM tld_prices tp
            JOIN registrars r ON r.id = tp.registrar_id
            LEFT JOIN reseller_prices rp ON rp.tld = tp.tld AND rp.user_id = ?
            WHERE tp.tld = ? AND r.status = 'active'
            ORDER BY r.priority ASC, r.id ASC LIMIT 1");
        $q->execute([$userId, $tld]);
        $row = $q->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['price'] === null) {
            throw new RuntimeException('No active price configured for this TLD.');
        }

        $price = (float)$row['price'];
        $registrarId = (int)$row['registrar_id'];

        if ($promoCode !== null && trim($promoCode) !== '') {
            $p = $this->db->prepare("SELECT discount_type, discount_value
                FROM promotions
                WHERE code = ?
                  AND status = 'active'
                  AND (registrar_id IS NULL OR registrar_id = ?)
                  AND (starts_at IS NULL OR starts_at <= NOW())
                  AND (ends_at IS NULL OR ends_at >= NOW())
                  AND (tld IS NULL OR tld = ?)
                  AND (action = 'all' OR action = ?)
                  AND (usage_limit IS NULL OR usage_limit > 0)
                ORDER BY (registrar_id IS NULL) ASC, id DESC LIMIT 1");
            $p->execute([strtoupper(trim($promoCode)), $registrarId, $tld, $action]);

            if ($promo = $p->fetch(PDO::FETCH_ASSOC)) {
                $discount = $promo['discount_type'] === 'percent'
                    ? $price * ((float)$promo['discount_value'] / 100)
                    : (float)$promo['discount_value'];
                $price = max(0, round($price - $discount, 8));
            }
        }

        return $price;
    }
}

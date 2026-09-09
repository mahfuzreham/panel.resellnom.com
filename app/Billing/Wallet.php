<?php
declare(strict_types=1);

namespace ResellNom\Billing;

use PDO;
use RuntimeException;

final class Wallet
{
    public function __construct(private PDO $db) {}

    public function credit(int $userId, float $amount, string $description = '', ?int $createdBy = null): float
    {
        return $this->change($userId, $amount, 'credit', $description, $createdBy);
    }

    public function debit(int $userId, float $amount, string $description = '', ?int $createdBy = null): float
    {
        if ($amount <= 0) throw new RuntimeException('Amount must be greater than zero.');
        return $this->change($userId, -$amount, 'debit', $description, $createdBy);
    }

    private function change(int $userId, float $delta, string $type, string $description, ?int $createdBy): float
    {
        if ($delta == 0) throw new RuntimeException('Amount cannot be zero.');
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
            $q->execute([$userId]);
            $balance = $q->fetchColumn();
            if ($balance === false) throw new RuntimeException('User not found.');
            $newBalance = round((float)$balance + $delta, 8);
            if ($newBalance < 0) throw new RuntimeException('Insufficient wallet balance.');
            $u = $this->db->prepare('UPDATE users SET balance = ? WHERE id = ?');
            $u->execute([$newBalance, $userId]);
            $i = $this->db->prepare('INSERT INTO wallet_transactions(user_id,type,amount,balance_after,description,created_by) VALUES(?,?,?,?,?,?)');
            $i->execute([$userId, $type, abs($delta), $newBalance, $description ?: null, $createdBy]);
            $this->db->commit();
            return $newBalance;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}

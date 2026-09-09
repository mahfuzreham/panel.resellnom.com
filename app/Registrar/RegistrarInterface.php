<?php
declare(strict_types=1);

namespace ResellNom\Registrar;

interface RegistrarInterface
{
    public function checkAvailability(string $domain): array;
    public function register(string $domain, array $contact, int $years = 1): array;
    public function transfer(string $domain, string $authCode, array $contact = []): array;
    public function renew(string $domain, int $years = 1): array;
    public function getAuthCode(string $domain): array;
    public function updateNameservers(string $domain, array $nameservers): array;
    public function listDnsRecords(string $domain): array;
    public function addDnsRecord(string $domain, array $record): array;
    public function updateDnsRecord(string $domain, string $recordId, array $record): array;
    public function deleteDnsRecord(string $domain, string $recordId): array;
}

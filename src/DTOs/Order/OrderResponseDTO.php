<?php

namespace LaravelTicimax\Ticimax\DTOs\Order;

class OrderResponseDTO
{
    public function __construct(
        public bool $success,
        public ?string $orderId = null,
        public ?string $message = null,
        public ?array $data = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['Durum'] === 'Basarili' || ($data['Basarili'] ?? false),
            orderId: $data['SiparisID'] ?? null,
            message: $data['Mesaj'] ?? null,
            data: $data
        );
    }
} 
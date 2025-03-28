<?php

namespace LaravelTicimax\Ticimax\DTOs\Order;

class OrderListResponseDTO
{
    public function __construct(
        public array $orders,
        public int $totalCount,
        public int $page,
        public int $pageSize
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orders: $data['Siparis'] ?? [],
            totalCount: $data['ToplamAdet'] ?? 0,
            page: $data['Sayfa'] ?? 1,
            pageSize: $data['SayfaBasiKayitSayisi'] ?? 10
        );
    }
} 
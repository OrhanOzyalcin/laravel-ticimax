<?php

namespace LaravelTicimax\Ticimax\DTOs\Order;

class OrderItemDTO
{
    public function __construct(
        public string $productCode,
        public float $quantity,
        public float $price,
        public ?float $totalPrice = null,
        public ?string $productName = null,
        public ?string $variantCode = null,
        public ?string $barcode = null,
        public ?float $tax = null,
        public ?float $discount = null
    ) {
        // Calculate total price if not provided
        if ($this->totalPrice === null) {
            $this->totalPrice = $this->price * $this->quantity;
        }
    }

    public function toArray(): array
    {
        return [
            'UrunKodu' => $this->productCode,
            'Miktar' => $this->quantity,
            'BirimFiyat' => $this->price,
            'ToplamTutar' => $this->totalPrice,
            'UrunAdi' => $this->productName,
            'VaryasyonKodu' => $this->variantCode,
            'Barkod' => $this->barcode,
            'KDV' => $this->tax,
            'Indirim' => $this->discount
        ];
    }
} 
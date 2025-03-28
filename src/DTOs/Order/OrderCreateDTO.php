<?php

namespace LaravelTicimax\Ticimax\DTOs\Order;

class OrderCreateDTO
{
    public function __construct(
        public string $orderNumber,
        public string $customerCode,
        public array $items,
        public ?string $notes = null,
        public ?string $paymentType = null,
        public ?string $paymentMethod = null,
        public ?float $totalAmount = null,
        public ?float $shippingAmount = null,
        public ?string $deliveryAddress = null,
        public ?string $deliveryCity = null,
        public ?string $deliveryCounty = null,
        public ?string $billingAddress = null,
        public ?string $billingCity = null,
        public ?string $billingCounty = null,
        public ?string $customerPhone = null,
        public ?string $customerEmail = null,
        public ?string $orderDate = null
    ) {
        // Set default order date to today if not provided
        $this->orderDate = $orderDate ?? date('Y-m-d');
    }

    public function toArray(): array
    {
        return [
            'SiparisNo' => $this->orderNumber,
            'MusteriKodu' => $this->customerCode,
            'Urunler' => array_map(fn($item) => $item->toArray(), $this->items),
            'Notlar' => $this->notes,
            'OdemeTipi' => $this->paymentType,
            'OdemeYontemi' => $this->paymentMethod,
            'ToplamTutar' => $this->totalAmount,
            'KargoTutari' => $this->shippingAmount,
            'TeslimatAdresi' => $this->deliveryAddress,
            'TeslimatIl' => $this->deliveryCity,
            'TeslimatIlce' => $this->deliveryCounty,
            'FaturaAdresi' => $this->billingAddress,
            'FaturaIl' => $this->billingCity,
            'FaturaIlce' => $this->billingCounty,
            'MusteriTelefon' => $this->customerPhone,
            'MusteriEposta' => $this->customerEmail,
            'SiparisTarihi' => $this->orderDate
        ];
    }
}
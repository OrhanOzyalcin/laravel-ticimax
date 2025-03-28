<?php

namespace LaravelTicimax\Ticimax\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use LaravelTicimax\Ticimax\Services\TicimaxService;
use LaravelTicimax\Ticimax\DTOs\Order\OrderCreateDTO;
use LaravelTicimax\Ticimax\DTOs\Order\OrderItemDTO;
use LaravelTicimax\Ticimax\DTOs\Order\OrderListResponseDTO;

class TicimaxServiceTest extends TestCase
{
    private function createMockClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    public function testCreateOrderSuccess()
    {
        $mockResponse = [
            'SiparisID' => 12345,
            'Durum' => 'Basarili',
            'Mesaj' => 'Sipariş başarıyla oluşturuldu'
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse))
        ]);

        $service = new TicimaxService('http://test.com', 'test-key');
        $service->setClient($client);

        $orderItem = new OrderItemDTO(
            productCode: 'PROD001',
            quantity: 2,
            price: 50.00,
            productName: 'Test Product'
        );

        $orderDto = new OrderCreateDTO(
            orderNumber: 'TEST123',
            customerCode: 'CUST001',
            items: [$orderItem],
            paymentType: 'Kredi Kartı',
            customerPhone: '5551234567',
            customerEmail: 'test@example.com'
        );
        
        $response = $service->createOrder($orderDto);

        $this->assertEquals(12345, $response->orderId);
        $this->assertTrue($response->success);
        $this->assertEquals('Sipariş başarıyla oluşturuldu', $response->message);
    }

    public function testCreateOrderFailure()
    {
        $mockResponse = [
            'Durum' => 'Hatali',
            'Mesaj' => 'Sipariş oluşturulurken hata oluştu'
        ];

        $client = $this->createMockClient([
            new Response(400, [], json_encode($mockResponse))
        ]);

        $service = new TicimaxService('http://test.com', 'test-key');
        $service->setClient($client);

        $orderDto = new OrderCreateDTO(
            orderNumber: 'TEST123',
            customerCode: 'CUST001',
            items: []
        );
        
        $response = $service->createOrder($orderDto);

        $this->assertFalse($response->success);
        $this->assertEquals('Sipariş oluşturulurken hata oluştu', $response->message);
    }

    public function testGetOrdersSuccess()
    {
        $mockResponse = [
            'Siparis' => [
                [
                    'SiparisID' => 12345,
                    'SiparisNo' => 'ORDER123',
                    'MusteriKodu' => 'CUST001',
                    'Tarih' => '2023-01-01',
                    'ToplamTutar' => 100.00
                ],
                [
                    'SiparisID' => 12346,
                    'SiparisNo' => 'ORDER124',
                    'MusteriKodu' => 'CUST002',
                    'Tarih' => '2023-01-02',
                    'ToplamTutar' => 200.00
                ]
            ],
            'ToplamAdet' => 2,
            'Sayfa' => 1,
            'SayfaBasiKayitSayisi' => 10
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse))
        ]);

        $service = new TicimaxService('http://test.com', 'test-key');
        $service->setClient($client);

        $filters = [
            'baslangicTarihi' => '2023-01-01',
            'bitisTarihi' => '2023-01-31'
        ];

        $response = $service->getOrders($filters);

        $this->assertInstanceOf(OrderListResponseDTO::class, $response);
        $this->assertCount(2, $response->orders);
        $this->assertEquals(2, $response->totalCount);
        $this->assertEquals(1, $response->page);
        $this->assertEquals(10, $response->pageSize);
    }

    public function testGetOrdersWithEmptyResponse()
    {
        $mockResponse = [
            'Siparis' => [],
            'ToplamAdet' => 0,
            'Sayfa' => 1,
            'SayfaBasiKayitSayisi' => 10
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse))
        ]);

        $service = new TicimaxService('http://test.com', 'test-key');
        $service->setClient($client);

        $response = $service->getOrders([
            'baslangicTarihi' => '2023-01-01',
            'bitisTarihi' => '2023-01-31'
        ]);

        $this->assertInstanceOf(OrderListResponseDTO::class, $response);
        $this->assertCount(0, $response->orders);
        $this->assertEquals(0, $response->totalCount);
    }
}
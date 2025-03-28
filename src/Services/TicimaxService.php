<?php

namespace LaravelTicimax\Ticimax\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LaravelTicimax\Ticimax\Exceptions\TicimaxApiException;
use LaravelTicimax\Ticimax\DTOs\Order\OrderCreateDTO;
use LaravelTicimax\Ticimax\DTOs\Order\OrderResponseDTO;
use LaravelTicimax\Ticimax\DTOs\Order\OrderListResponseDTO;

class TicimaxService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retrySleep;

    // API Endpoints
    const ENDPOINT_CREATE_ORDER = '/api/Siparis/SiparisEkle';
    const ENDPOINT_GET_ORDER = '/api/Siparis/SiparisDetay';
    const ENDPOINT_LIST_ORDERS = '/api/Siparis/SiparisListele';

    /**
     * TicimaxService constructor.
     *
     * @param string $baseUrl Base URL for the Ticimax API
     * @param string $apiKey Your Ticimax API key
     * @param int $timeout Request timeout in seconds
     * @param int $retryTimes Number of retry attempts for failed requests
     * @param int $retrySleep Milliseconds to wait between retries
     */
    public function __construct(
        string $baseUrl, 
        string $apiKey, 
        int $timeout = 30,
        int $retryTimes = 3,
        int $retrySleep = 100
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
        $this->retryTimes = $retryTimes;
        $this->retrySleep = $retrySleep;

        $stack = HandlerStack::create();
        
        // Add retry middleware
        $stack->push($this->retryMiddleware());

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'handler' => $stack,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);
    }

    /**
     * Creates retry middleware for handling request failures.
     *
     * @return callable
     */
    private function retryMiddleware(): callable
    {
        return Middleware::retry(
            function (
                $retries,
                Request $request,
                Response $response = null,
                \Exception $exception = null
            ) {
                // Limit the number of retries
                if ($retries >= $this->retryTimes) {
                    return false;
                }

                // Retry connection exceptions
                if ($exception instanceof ConnectException) {
                    return true;
                }

                // Retry on server errors (5xx) and rate limits (429)
                if ($response && ($response->getStatusCode() >= 500 || $response->getStatusCode() === 429)) {
                    return true;
                }

                return false;
            },
            function ($retries) {
                // Exponential backoff
                return $this->retrySleep * (2 ** $retries);
            }
        );
    }

    /**
     * Set custom HTTP client (used for testing).
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Creates a new order in Ticimax.
     *
     * @param OrderCreateDTO $orderDto Order data
     * @return OrderResponseDTO
     * @throws TicimaxApiException
     */
    public function createOrder(OrderCreateDTO $orderDto): OrderResponseDTO
    {
        try {
            $response = $this->client->post(self::ENDPOINT_CREATE_ORDER, [
                'json' => $orderDto->toArray()
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!$data) {
                throw new TicimaxApiException('Invalid response from Ticimax API', 500);
            }

            return OrderResponseDTO::fromArray($data);
        } catch (GuzzleException $e) {
            throw new TicimaxApiException(
                'Order creation failed: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        } catch (\Exception $e) {
            throw new TicimaxApiException(
                'An unexpected error occurred: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        }
    }

    /**
     * Gets an order by ID from Ticimax.
     *
     * @param string $orderId Order ID
     * @return OrderResponseDTO
     * @throws TicimaxApiException
     */
    public function getOrder(string $orderId): OrderResponseDTO
    {
        try {
            $response = $this->client->get(self::ENDPOINT_GET_ORDER, [
                'query' => ['siparisId' => $orderId]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!$data) {
                throw new TicimaxApiException('Invalid response from Ticimax API', 500);
            }

            return OrderResponseDTO::fromArray($data);
        } catch (GuzzleException $e) {
            throw new TicimaxApiException(
                'Failed to get order: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        } catch (\Exception $e) {
            throw new TicimaxApiException(
                'An unexpected error occurred: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        }
    }

    /**
     * Ticimax'ten sipariş listesini getirir
     * 
     * @param array $filters Örnek: ['baslangicTarihi' => '2023-01-01', 'bitisTarihi' => '2023-12-31', 'siparisNo' => '123', 'siparisIDleri' => '1,2,3', 'entegrasyonTuru' => '1']
     * @param int $page Sayfa numarası
     * @param int $perPage Sayfa başı kayıt sayısı
     * @return OrderListResponseDTO
     * @throws TicimaxApiException
     */
    public function getOrders(array $filters = [], int $page = 1, int $perPage = 100): OrderListResponseDTO
    {
        try {
            $query = array_merge($filters, [
                'sayfa' => $page,
                'sayfaBasiKayitSayisi' => $perPage
            ]);

            $response = $this->client->get(self::ENDPOINT_LIST_ORDERS, [
                'query' => $query
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!$data) {
                throw new TicimaxApiException('Invalid response from Ticimax API', 500);
            }

            return OrderListResponseDTO::fromArray($data);

        } catch (GuzzleException $e) {
            throw new TicimaxApiException(
                'Sipariş listesi alınamadı: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        } catch (\Exception $e) {
            throw new TicimaxApiException(
                'An unexpected error occurred: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        }
    }

    // Diğer servis metodları...
}
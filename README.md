# Laravel Ticimax

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orhanozyalcin/laravel-ticimax.svg?style=flat-square)](https://packagist.org/packages/orhanozyalcin/laravel-ticimax)
[![Total Downloads](https://img.shields.io/packagist/dt/orhanozyalcin/laravel-ticimax.svg?style=flat-square)](https://packagist.org/packages/orhanozyalcin/laravel-ticimax)

A Laravel package for integrating with the Ticimax E-Commerce platform API.

## Installation

You can install the package via composer:

```bash
composer require orhanozyalcin/laravel-ticimax
```

## Configuration

After installing the package, publish the configuration file:

```bash
php artisan vendor:publish --tag="ticimax-config"
```

Then, add your Ticimax API credentials to your `.env` file:

```
TICIMAX_BASE_URL=https://ticimaxwebservice.azurewebsites.net
TICIMAX_API_KEY=your-api-key
TICIMAX_TIMEOUT=30
TICIMAX_RETRY_TIMES=3
TICIMAX_RETRY_SLEEP=100
```

## Usage

### Retrieving Orders

```php
use LaravelTicimax\Ticimax\Facades\TicimaxFacade as Ticimax;

// Get orders with filters
$filters = [
    'baslangicTarihi' => '2023-01-01',
    'bitisTarihi' => '2023-12-31',
    'siparisNo' => '123',           // Optional
    'siparisIDleri' => '1,2,3',     // Optional
    'entegrasyonTuru' => '1'        // Optional
];

try {
    // Get first page with 20 orders per page
    $response = Ticimax::getOrders($filters, 1, 20);
    
    // Access the orders
    foreach ($response->orders as $order) {
        // Process each order
        $orderId = $order['SiparisID'];
        $orderNumber = $order['SiparisNo'];
        // Process other fields...
    }
    
    // Access pagination information
    $totalOrders = $response->totalCount;
    $currentPage = $response->page;
    $ordersPerPage = $response->pageSize;
    
} catch (\LaravelTicimax\Ticimax\Exceptions\TicimaxApiException $e) {
    // Handle exception
    $errorMessage = $e->getMessage();
    $errorDetails = $e->getDetails();
    
    // Log or report error
}
```

### Creating an Order

```php
use LaravelTicimax\Ticimax\Facades\TicimaxFacade as Ticimax;
use LaravelTicimax\Ticimax\DTOs\Order\OrderCreateDTO;
use LaravelTicimax\Ticimax\DTOs\Order\OrderItemDTO;

// Create order items
$item1 = new OrderItemDTO(
    productCode: 'PROD001',
    quantity: 2,
    price: 50.00,
    productName: 'Test Product'
);

$item2 = new OrderItemDTO(
    productCode: 'PROD002',
    quantity: 1,
    price: 75.00,
    productName: 'Another Product'
);

// Create the order
$order = new OrderCreateDTO(
    orderNumber: 'ORDER123',
    customerCode: 'CUST001',
    items: [$item1, $item2],
    paymentType: 'Kredi Kartı',
    customerPhone: '5551234567',
    customerEmail: 'customer@example.com',
    deliveryAddress: 'Delivery Address',
    deliveryCity: 'Istanbul',
    deliveryCounty: 'Kadikoy',
    billingAddress: 'Billing Address',
    billingCity: 'Istanbul',
    billingCounty: 'Kadikoy'
);

try {
    $result = Ticimax::createOrder($order);
    
    if ($result->success) {
        // Order created successfully
        $orderId = $result->orderId;
    } else {
        // Order creation failed
        $errorMessage = $result->message;
    }
} catch (\LaravelTicimax\Ticimax\Exceptions\TicimaxApiException $e) {
    // Handle exception
}
```

### Getting a Single Order

```php
use LaravelTicimax\Ticimax\Facades\TicimaxFacade as Ticimax;

try {
    $order = Ticimax::getOrder('12345');
    
    if ($order->success) {
        // Access order details
        $orderData = $order->data;
    }
} catch (\LaravelTicimax\Ticimax\Exceptions\TicimaxApiException $e) {
    // Handle exception
}
```

## Direct Usage (without Facade)

If you prefer to use dependency injection, you can inject the service directly:

```php
use LaravelTicimax\Ticimax\Services\TicimaxService;

class OrderController extends Controller
{
    protected $ticimaxService;
    
    public function __construct(TicimaxService $ticimaxService) 
    {
        $this->ticimaxService = $ticimaxService;
    }
    
    public function listOrders()
    {
        $orders = $this->ticimaxService->getOrders([
            'baslangicTarihi' => '2023-01-01',
            'bitisTarihi' => '2023-12-31'
        ]);
        
        return view('orders.index', compact('orders'));
    }
}
```

## Error Handling

All API operations throw a `TicimaxApiException` in case of failure. This exception contains:

- The error message
- The status code
- Additional error details (if available)
- The original exception (if applicable)

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 
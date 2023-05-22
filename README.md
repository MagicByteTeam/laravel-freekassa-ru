# Laravel payment processor package for FreeKassa gateway

Accept payments via FreeKassa.ru ([freekassa.ru](https://freekassa.ru/)) using this Laravel framework package ([Laravel](https://laravel.com)).

- receive payments, adding just the two callbacks

#### Laravel >= 10.*, PHP >= 8.1
>
> To use the package for Laravel 9.* use the [2.x](https://github.com/MagicByteTeam/laravel-freekassa-ru/tree/2.x) branch
> 
> To use the package for Laravel 6.* use the [1.x](https://github.com/MagicByteTeam/laravel-freekassa-ru/tree/1.x) branch

## Installation

Require this package with composer.

``` bash
composer require weishaypt/laravel-freekassa-ru
```

If you don't use auto-discovery, add the ServiceProvider to the providers array in `config/app.php`

```php
Weishaypt\FreeKassa\FreeKassaServiceProvider::class,
```

Add the `FreeKassa` facade to your facades array:

```php
'FreeKassa' => Weishaypt\FreeKassa\Facades\FreeKassa::class,
```

Copy the package config to your local config with the publish command:
``` bash
php artisan vendor:publish --provider="Weishaypt\FreeKassa\FreeKassaServiceProvider"
```

## Configuration

Once you have published the configuration files, please edit the config file in `config/freekassa.php`.

- Create an account on [freekassa.ru](freekassa.ru)
- Add your project, copy the `project_id`, `secret_key` and `secret_key_second` params and paste into `config/freekassa.php`
- After the configuration has been published, edit `config/freekassa.php`
- Set the callback static function for `searchOrder` and `paidOrder`
- Create route to your controller, and call `FreeKassa::handle` method
 
## Usage

1) Generate a payment url or get redirect:

```php
$amount = 100; // Payment`s amount

$url = FreeKassa::getPayUrl($amount, $order_id);

$redirect = FreeKassa::redirectToPayUrl($amount, $order_id);
```

You can add custom fields to your payment:

```php
$rows = [
    'time' => Carbon::now(),
    'info' => 'Local payment'
];

$url = FreeKassa::getPayUrl($amount, $order_id, $desc, $payment_methood, $rows);

$redirect = FreeKassa::redirectToPayUrl($amount, $order_id, $desc, $payment_methood, $rows);
```

`$desc` and `$payment_methood` can be null.

2) Process the request from FreeKassa:
``` php
FreeKassa::handle(Request $request)
```

## Important

You must define callbacks in `config/freekassa.php` to search the order and save the paid order.


``` php
'searchOrder' => null  // FreeKassaController@searchOrder(Request $request)
```

``` php
'paidOrder' => null  // FreeKassaController@paidOrder(Request $request, $order)
```

## Example

The process scheme:

1. The request comes from `freekassa.ru` `GET` / `POST` `http://yourproject.com/freekassa/result` (with params).
2. The function`FreeKassaController@handlePayment` runs the validation process (auto-validation request params).
3. The method `searchOrder` will be called (see `config/freekassa.php` `searchOrder`) to search the order by the unique id.
4. If the current order status is NOT `paid` in your database, the method `paidOrder` will be called (see `config/freekassa.php` `paidOrder`).

Add the route to `routes/web.php`:
``` php
 Route::get('/freekassa/result', 'FreeKassaController@handlePayment');
```

> **Note:**
don't forget to save your full route url (e.g. http://example.com/freekassa/result ) for your project on [freekassa.ru](freekassa.ru).

Create the following controller: `/app/Http/Controllers/FreeKassaController.php`:

``` php
class FreeKassaController extends Controller
{
    /**
     * Search the order in your database and return that order
     * to paidOrder, if status of your order is 'paid'
     *
     * @param Request $request
     * @param $order_id
     * @return bool|mixed
     */
    public function searchOrder(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if($order) {
            $order['_orderSum'] = $order->sum;

            // If your field can be `paid` you can set them like string
            $order['_orderStatus'] = $order['status'];

            // Else your field doesn` has value like 'paid', you can change this value
            $order['_orderStatus'] = ('1' == $order['status']) ? 'paid' : false;

            return $order;
        }

        return false;
    }

    /**
     * When paymnet is check, you can paid your order
     *
     * @param Request $request
     * @param $order
     * @return bool
     */
    public function paidOrder(Request $request, $order)
    {
        $order->status = 'paid';
        $order->save();

        //

        return true;
    }

    /**
     * Start handle process from route
     *
     * @param Request $request
     * @return mixed
     */
    public function handlePayment(Request $request)
    {
        return FreeKassa::handle($request);
    }
}
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please send me an email at ya@sanek.dev instead of using the issue tracker.

## Credits

- [Weishaypt](https://github.com/Weishaypt)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

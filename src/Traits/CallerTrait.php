<?php

namespace Weishaypt\FreeKassa\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Weishaypt\FreeKassa\Exceptions\InvalidPaidOrder;
use Weishaypt\FreeKassa\Exceptions\InvalidSearchOrder;

trait CallerTrait
{
    /**
     * @param Request $request
     * @return mixed
     *
     * @throws InvalidSearchOrder
     */
    public function callSearchOrder(Request $request)
    {
        if (is_null(config('freekassa.searchOrder'))) {
            throw new InvalidSearchOrder();
        }

        return App::call(config('freekassa.searchOrder'), [$request->input('merchant_id')]);
    }

    /**
     * @param Request $request
     * @param $order
     * @return mixed
     * @throws InvalidPaidOrder
     */
    public function callPaidOrder(Request $request, $order)
    {
        if (is_null(config('freekassa.paidOrder'))) {
            throw new InvalidPaidOrder();
        }

        return App::call(config('freekassa.paidOrder'), [$order]);
    }
}

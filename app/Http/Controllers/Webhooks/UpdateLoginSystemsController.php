<?php


namespace App\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\Controller as BaseController;

class UpdateLoginSystemsController extends BaseController
{
    public function index(Request $request)
    {
        Cache::forget('parameters_login_systems');

        return response()->json([], 201);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class ErrorController extends BaseController
{
    public function index($code)
    {
        switch ($code) {
            case "001":
                $errorMessage = "Ha ocurrido un error con el pago";
                break;
            case "002":
                $errorMessage = "El enlace de verificación es incorrecto";
                break;
            default:
                $errorMessage = "Ha ocurrido un error";
        }

        return view('error', [
            'errorMessage' => $errorMessage
        ]);
    }
}

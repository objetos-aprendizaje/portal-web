<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SugerenciasController extends Controller
{
    public function index() {
        $resources=[];
        return view("sugerencias")->with('resources', $resources);
    }
}

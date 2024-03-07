<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DudasController extends Controller
{
    public function index() {
        $resources=[];
        return view("dudas")->with('resources', $resources);
    }
}

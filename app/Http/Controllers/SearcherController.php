<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralOptionsModel;


class SearcherController extends Controller
{

    public function index() {

        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();

        return view("searcher", ["general_options" => $general_options]);

    }

}

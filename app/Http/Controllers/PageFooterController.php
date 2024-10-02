<?php

namespace App\Http\Controllers;

use App\Models\FooterPagesModel;
use Illuminate\Routing\Controller as BaseController;


class PageFooterController extends BaseController
{

    public function index($uid)
    {

        $page = FooterPagesModel::where('uid', $uid)->first();

        return view("page_footer", [
            'resources' => ["resources/js/home.js", "resources/js/carrousel.js"],
            'page' => $page,
            'title' => $page->title,
            "page_title" => "Gestión de pago"

        ]);

    }

}

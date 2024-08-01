<?php

namespace App\Http\Controllers;

use App\Models\FooterPagesModel;
use App\Models\HeaderPagesModel;
use Illuminate\Routing\Controller as BaseController;

class HeaderFooterPagesController extends BaseController
{

    public function index($slug)
    {
        // Buscamos en el header
        $page = HeaderPagesModel::where('slug', $slug)->first();

        if ($page) {
            return view("page_footer", [
                'page' => $page,
                'title' => $page->title
            ]);
        }

        // Buscamos en el footer
        $page = FooterPagesModel::where('slug', $slug)->first();
        if ($page) {
            return view("page_footer", [
                'resources' => ["resources/js/home.js", "resources/js/carrousel.js"],
                'page' => $page,
                'title' => $page->title
            ]);
        }

        // Si no está en ninguno de los dos, devolvemos un 404
        abort(404);
    }
}

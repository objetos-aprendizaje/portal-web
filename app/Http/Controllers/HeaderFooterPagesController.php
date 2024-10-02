<?php

namespace App\Http\Controllers;

use App\Models\FooterPagesModel;
use App\Models\HeaderPagesModel;
use Illuminate\Routing\Controller as BaseController;

class HeaderFooterPagesController extends BaseController
{
    public function index($slug)
    {
        $page = $this->findPageBySlug($slug);

        if (!$page) {
            abort(404);
        }

        return view("page_footer", [
            'page' => $page,
            'name' => $page->name,
            'page_title' => $page->name
        ]);
    }

    private function findPageBySlug($slug)
    {
        $page = HeaderPagesModel::where('slug', $slug)->first();

        if (!$page) {
            $page = FooterPagesModel::where('slug', $slug)->first();
        }

        return $page;
    }
}

<?php

namespace App\Http\Controllers\Profile;

use App\Models\CategoriesModel;
use App\Models\UserCategoriesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends BaseController
{

    public function index()
    {
        $categories_anidated = CategoriesModel::whereNull('parent_category_uid')->with('subcategories')->get()->toArray();

        $user_categories = auth()->user()->categories->pluck('uid')->toArray();

        return view("profile.categories.index", [
            "resources" => [
                'resources/js/profile/categories.js'
            ],
            "user_categories" => $user_categories,
            "categories" => $categories_anidated,
            "currentPage" => "categories",
            "page_title" => "Categorías interesadas"
        ]);
    }

    public function saveCategories(Request $request)
    {

        $categories = $request->input('categories');

        $this->syncCategories($categories);

        return response()->json([
            'message' => 'Categorias guardadas correctamente'
        ]);
    }

    private function syncCategories($categories) {
        DB::transaction(function () use ($categories) {
            $user_uid = auth()->user()->uid;
            $categories_bd = CategoriesModel::whereIn('uid', $categories)->get()->pluck('uid');

            UserCategoriesModel::where('user_uid', $user_uid)->whereIn('category_uid', $categories_bd)->delete();
            $categories_to_sync = [];

            foreach ($categories_bd as $category_uid) {
                $categories_to_sync[] = [
                    'uid' => generate_uuid(),
                    'user_uid' => $user_uid,
                    'category_uid' => $category_uid
                ];
            }

            auth()->user()->categories()->sync($categories_to_sync);
        });
    }
}

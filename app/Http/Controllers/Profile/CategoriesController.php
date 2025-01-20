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
        $categoriesAnidated = CategoriesModel::whereNull('parent_category_uid')->with('subcategories')->get()->toArray();

        $userCategories = auth()->user()->categories->pluck('uid')->toArray();

        return view("profile.categories.index", [
            "resources" => [
                'resources/js/profile/categories.js'
            ],
            "user_categories" => $userCategories,
            "categories" => $categoriesAnidated,
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
            $userUid = auth()->user()->uid;
            $categoriesBd = CategoriesModel::whereIn('uid', $categories)->get()->pluck('uid');

            UserCategoriesModel::where('user_uid', $userUid)->whereIn('category_uid', $categoriesBd)->delete();
            $categoriesToSync = [];

            foreach ($categoriesBd as $categoryUid) {
                $categoriesToSync[] = [
                    'uid' => generate_uuid(),
                    'user_uid' => $userUid,
                    'category_uid' => $categoryUid
                ];
            }

            auth()->user()->categories()->sync($categoriesToSync);
        });
    }
}

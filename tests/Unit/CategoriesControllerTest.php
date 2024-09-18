<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CategoriesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoriesControllerTest extends TestCase
{
    
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    /**
     * @test
     * Prueba que la vista de categorías del perfil se carga correctamente con las categorías y las categorías del usuario
     */
    public function testIndexLoadsCategoriesPageWithCorrectData()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear algunas categorías anidadas (padres e hijos)

        CategoriesModel::factory()->count(3)->create(['parent_category_uid' => null]);

        $parentCategories= CategoriesModel::where('parent_category_uid',null)->get();

        $parentCategory = CategoriesModel::first();     


        CategoriesModel::factory()->count(3)->create(['parent_category_uid' => $parentCategory->uid]);

        $subCategories = CategoriesModel::where('parent_category_uid',"!=",null)->get();

    

        // Asociar algunas categorías al usuario
        $user->categories()->attach($parentCategory->uid,[
            'uid' => generate_uuid(),
        ]);

        // Hacer una solicitud GET a la ruta de categorías del perfil
        $response = $this->get(route('categories'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('profile.categories.index');

        // Verificar que los datos de las categorías y las categorías del usuario se pasan a la vista
        $response->assertViewHas('categories', function ($categories) use ($parentCategories, $subCategories) {
            // Verificar que las categorías anidadas se pasen correctamente
            return count($categories) === 3 && isset($categories[0]['subcategories']);
        });

        $response->assertViewHas('user_categories', function ($userCategories) use ($parentCategory) {
            // Verificar que las categorías del usuario se pasen correctamente         
            return $userCategories[0] === $parentCategory->uid;
        });

        // Verificar que el recurso JavaScript y la página actual se pasan correctamente
        $response->assertViewHas('resources', ['resources/js/profile/categories.js']);
        $response->assertViewHas('currentPage', 'categories');
    }

    /**
     * @test
     * Prueba que las categorías se guardan correctamente y se sincronizan para el usuario autenticado
     */
    public function testSaveCategoriesSyncsCorrectly()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear algunas categorías en la base de datos
        CategoriesModel::factory()->count(3)->create();

        $categories =CategoriesModel::get();


        // Datos simulados enviados desde el formulario de categorías
        $requestData = [
            'categories' => $categories->pluck('uid')->toArray(),
        ];

        // Hacer la solicitud POST a la ruta de guardar categorías
        $response = $this->post(route('save-categories'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que el mensaje correcto se devuelve en el JSON
        $response->assertJson(['message' => 'Categorias guardadas correctamente']);

        // Verificar que las categorías del usuario se han sincronizado correctamente
        foreach ($categories as $category) {
            $this->assertDatabaseHas('user_categories', [
                'user_uid' => $user->uid,
                'category_uid' => $category->uid,
            ]);
        }
    }

}

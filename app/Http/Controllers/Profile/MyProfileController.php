<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MyProfileController extends BaseController
{

    public function index()
    {
        $user = Auth::user();
        return view(
            'profile.my_profile.index',
            [
                "page_name" => "Mi perfil",
                "page_title" => "Mi perfil",
                "resources" => [
                    "resources/js/my_profile.js"
                ],
                "user" => $user,
                "currentPage" => "myProfile"
            ]
        );
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $this->updateUserDetails($request, $user);

        // Guardar la imagen en el backend
        if ($request->file('photo_path')) {
            $photo_path = $this->updateImageBackend($request->file('photo_path'));

            if (!$photo_path) {
                return response()->json(['message' => 'Ha ocurrido un error al subir la imagen'], 500);
            }

            $user->photo_path = $photo_path;
        }

        $user->save();

        return response()->json(['message' => 'Tu perfil se ha actualizado correctamente'], 200);
    }

    private function updateUserDetails($request, $user)
    {
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->nif = $request->input('nif');
        $user->curriculum = $request->input('curriculum');
        $user->department = $request->input('department');
        $user->curriculum = $request->input('curriculum');
    }

    // Enviar la imagen al webhook del backend y nos devuelve la ruta asignada
    public function updateImageBackend($image)
    {
        $header = ['API-KEY: ' . env('API_KEY_BACKEND_WEBHOOKS')];
        $url = env('BACKEND_URL') . '/webhook/update_user_image';

        try {
            $response = sendFileToBackend($image, $url, $header, true);
            return $response['photo_path'];
        } catch (\Exception $e) {
            return false;
        }
    }
}

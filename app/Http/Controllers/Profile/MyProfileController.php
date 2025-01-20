<?php

namespace App\Http\Controllers\Profile;

use App\Models\DepartmentsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MyProfileController extends BaseController
{

    public function index()
    {
        $user = Auth::user();
        $departments = DepartmentsModel::all();

        return view(
            'profile.my_profile.index',
            [
                "page_name" => "Mi perfil",
                "page_title" => "Mi perfil",
                "resources" => [
                    "resources/js/my_profile.js"
                ],
                "user" => $user,
                "departments" => $departments,
                "currentPage" => "myProfile"
            ]
        );
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->nif = $request->nif;
        $user->department_uid = $request->department_uid != "" ? $request->department_uid : null;
        $user->curriculum = $request->curriculum;

        // Guardar la imagen en el backend
        if ($request->file('photo_path')) {
            $photoPath = $this->updateImageBackend($request->file('photo_path'));

            if (!$photoPath) {
                return response()->json(['message' => 'Ha ocurrido un error al subir la imagen'], 500);
            }

            $user->photo_path = $photoPath;
        }

        $user->save();

        return response()->json(['message' => 'Tu perfil se ha actualizado correctamente'], 200);
    }

    public function deleteImage() {
        $user = Auth::user();
        $user->photo_path = null;
        $user->save();

        return response()->json(['message' => 'Imagen eliminada correctamente'], 200);
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

<?php

namespace App\Http\Controllers;

use App\Models\EmailsSuggestionsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuggestionsController extends Controller
{
    public function index() {
        $resources=[
            "resources/js/suggestions.js"
        ];
        return view("suggestions")->with('resources', $resources);
    }

    public function sendSuggestion(Request $request) {

        $messages = [
            'name.required' => 'El nombre es obligatorio',
            'message.required' => 'Este campo es obligatorio',
            'email.required' => 'El email es obligatorio',
        ];

        $rules = [
            'name' => 'required',
            'message' => 'required',
            'email' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['message' => 'Algunos campos son incorrectos', 'errors' => $validator->errors()], 422);
        }

        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $message = e($request->input('message'));

        // Insertamos los registros
        EmailsSuggestionsModel::create([
            'uid' => generate_uuid(),
            'email' => $email,
            'name' => $name,
            'message' => $message,
            'sent' => 0
        ]);

        return response()->json(['message' => 'Sugerencia enviada correctamente'], 200);
    }
}

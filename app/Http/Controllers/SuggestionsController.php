<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\SuggestionSubmissionEmailsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuggestionsController extends Controller
{
    public function index() {
        $resources=[
            "resources/js/suggestions.js"
        ];

        if(!app('existsEmailSuggestions')) {
            return redirect("/");
        }

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

        // Sacamos los emails definidos para el envío
        $suggestionSubmissionsEmails = SuggestionSubmissionEmailsModel::all();

        $parameters = [
            'name' => $name,
            'email' => $email,
            'userMessage' => $message,
        ];

        foreach($suggestionSubmissionsEmails as $email) {
            dispatch(new SendEmailJob($email->email, 'Nueva sugerencia', $parameters, 'emails.suggestion'));
        }

        return response()->json(['message' => 'Sugerencia enviada correctamente'], 200);
    }
}

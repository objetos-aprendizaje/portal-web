<?php

namespace App\Http\Controllers;

use App\Mail\SendDoubt;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalResourcesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class DoubtsController extends Controller
{
    public function index($learning_object_type, $uid)
    {

        if (!in_array($learning_object_type, ['course', 'educational_program', 'educational_resource'])) {
            abort(404);
        }

        return view("doubts", [
            "resources" => [
                "resources/js/doubts.js"
            ],
            "learning_object_type" => $learning_object_type,
            "uid" => $uid
        ]);
    }

    private function validateDoubt($request)
    {
        $messages = [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser un email válido',
            'message.required' => 'Este campo es obligatorio',
        ];

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'learning_object_type' => 'required|in:course,educational_program,educational_resource',
            'uid' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $errorsValidator = $validator->errors();

        return $errorsValidator;
    }

    public function sendDoubt(Request $request)
    {
        $errorsValidator = $this->validateDoubt($request);

        if ($errorsValidator->any()) {
            return response()->json(['message' => 'Algunos campos son incorrectos', 'errors' => $errorsValidator], 422);
        }

        $learning_object_type = $request->input('learning_object_type');
        $uid = $request->input('uid');

        $name = trim($request->input('name'));
        $email = $request->input('email');
        $message = e($request->input('message'));

        if ($learning_object_type == 'course') {
            $this->handleCourse($uid, $name, $email, $message);
        } else if ($learning_object_type == 'educational_program') {
            $this->handleEducationalProgram($uid, $name, $email, $message);
        } else if ($learning_object_type == 'educational_resource') {
            $this->handleEducationalResource($uid, $name, $email, $message);
        }

        return response()->json(['message' => 'Mensaje enviado correctamente'], 200);
    }

    private function handleCourse($uid, $name, $email, $message)
    {
        $learning_object = CoursesModel::where('uid', $uid)->with([
            'educational_program_type',
            'educational_program_type.redirection_queries' => function ($query) {
                $query->where('type', 'email');
            },
            'contact_emails'
        ])->first();

        if (!$learning_object) {
            return response()->json(['message' => 'El curso no existe'], 404);
        }

        $emails_to_send = $this->extractEmails($learning_object);
        $this->sendEmails($emails_to_send, $name, $email, $message);
    }

    private function handleEducationalProgram($uid, $name, $email, $message)
    {
        $educational_program = EducationalProgramsModel::where('uid', $uid)->with([
            'educational_program_type',
            'educational_program_type.redirection_queries' => function ($query) {
                $query->where('type', 'email');
            }
        ])->first();

        if (!$educational_program) {
            return response()->json(['message' => 'El programa educativo no existe'], 404);
        }

        $emails_to_send = $this->extractEmails($educational_program);
        $this->sendEmails($emails_to_send, $name, $email, $message);
    }

    private function handleEducationalResource($uid, $name, $email, $message)
    {
        $educational_resource = EducationalResourcesModel::where('uid', $uid)->with('educationalResourceType', 'educationalResourceType.redirection_queries_educational_program_types')->first();

        if (!$educational_resource) {
            return response()->json(['message' => 'El recurso educativo no existe'], 404);
        }

        $emails_to_send = $educational_resource->educationalResourceType->redirection_queries_educational_program_types->pluck('email')->toArray();

        $this->sendEmails($emails_to_send, $name, $email, $message);
    }

    private function extractEmails($learning_object)
    {
        if (!empty($learning_object->contact_emails->toArray())) {
            return $learning_object->contact_emails->pluck('email')->toArray();
        } else if (!empty($learning_object->educational_program_type->redirection_queries->toArray())) {
            return $learning_object->educational_program_type->redirection_queries->pluck('contact')->toArray();
        }

        return [];
    }

    private function sendEmails($emails, $name, $email, $message)
    {
        foreach ($emails as $destination_email) {
            Mail::to($destination_email)->send(new SendDoubt($name, $email, $message));
        }
    }
}

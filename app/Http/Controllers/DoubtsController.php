<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalResourcesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoubtsController extends Controller
{
    public function index($learningObjectType, $uid)
    {

        if (!in_array($learningObjectType, ['course', 'educational_program', 'educational_resource'])) {
            abort(404);
        }

        return view("doubts", [
            "resources" => [
                "resources/js/doubts.js"
            ],
            "learning_object_type" => $learningObjectType,
            "uid" => $uid,
            "page_title" => "Dudas"
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

        return $validator->errors();
        
    }

    public function sendDoubt(Request $request)
    {
        $errorsValidator = $this->validateDoubt($request);

        if ($errorsValidator->any()) {
            return response()->json(['message' => 'Algunos campos son incorrectos', 'errors' => $errorsValidator], 422);
        }

        $learningObjectType = $request->input('learning_object_type');
        $uid = $request->input('uid');

        $name = trim($request->input('name'));
        $email = $request->input('email');
        $message = e($request->input('message'));

        $parameters = [
            'name' => $name,
            'email' => $email,
            'userMessage' => $message
        ];

        if ($learningObjectType == 'course') {
            $this->handleCourse($uid, $parameters);
        } elseif ($learningObjectType == 'educational_program') {
            $this->handleEducationalProgram($uid, $parameters);
        } elseif ($learningObjectType == 'educational_resource') {
            $this->handleEducationalResource($uid, $parameters);
        }

        return response()->json(['message' => 'Mensaje enviado correctamente'], 200);
    }

    private function handleCourse($uid, $parameters)
    {
        $learningObject = CoursesModel::where('uid', $uid)->with([
            'course_type',
            'course_type.redirection_queries' => function ($query) {
                $query->where('type', 'email');
            },
            'contact_emails'
        ])->first();

        if (!$learningObject) {
            return response()->json(['message' => 'El curso no existe'], 404);
        }

        $emailsToSend = [];
        if ($learningObject->contact_emails->count()) {
            $emailsToSend = $learningObject->contact_emails->pluck('email')->toArray();
        } elseif ($learningObject->course_type->redirection_queries->count()) {
            $emailsToSend = $learningObject->course_type->redirection_queries->pluck('contact')->toArray();
        }

        $parameters['learningObjectName'] = $learningObject->title;
        $this->sendEmails($emailsToSend, $parameters);
    }

    private function handleEducationalProgram($uid, $parameters)
    {
        $educationalProgram = EducationalProgramsModel::where('uid', $uid)->with([
            'educational_program_type',
            'educational_program_type.redirection_queries' => function ($query) {
                $query->where('type', 'email');
            }
        ])->first();

        if (!$educationalProgram) {
            return response()->json(['message' => 'El programa formativo no existe'], 404);
        }

        $emailsToSend = [];
        if ($educationalProgram->contact_emails->count()) {
            $emailsToSend = $educationalProgram->contact_emails->pluck('email')->toArray();
        } elseif ($educationalProgram->educational_program_type->redirection_queries->count()) {
            $emailsToSend = $educationalProgram->educational_program_type->redirection_queries->pluck('contact')->toArray();
        }

        $parameters['learningObjectName'] = $educationalProgram->name;
        $this->sendEmails($emailsToSend, $parameters);
    }

    private function handleEducationalResource($uid, $parameters)
    {
        $educationalResource = EducationalResourcesModel::where('uid', $uid)
            ->with([
                'contactEmails',
                'educationalResourceType'
            ])
            ->first();

        if (!$educationalResource) {
            return response()->json(['message' => 'El recurso educativo no existe'], 404);
        }

        $emailsToSend = $educationalResource->contactEmails->pluck('email')->toArray();

        $parameters['learningObjectName'] = $educationalResource->title;
        $this->sendEmails($emailsToSend, $parameters);
    }

    private function sendEmails($emails, $parameters)
    {
        foreach ($emails as $destinationEmail) {
            dispatch(new SendEmailJob($destinationEmail, 'Has recibido una nueva consulta', $parameters, 'emails.doubt_learning_result'));
        }
    }
}

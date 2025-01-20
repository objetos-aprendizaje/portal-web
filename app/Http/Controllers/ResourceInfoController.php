<?php

namespace App\Http\Controllers;

use App\Models\EducationalResourceAccessModel;
use App\Models\EducationalResourcesAssessmentsModel;
use App\Models\EducationalResourcesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceInfoController extends BaseController
{

    public function index($uid)
    {
        $educationalResource = $this->getResourceDatabase($uid);

        $showDoubtsButton = $educationalResource->contactEmails->count() ? true : false;
        if (!$educationalResource) {
            abort(404);
        }

        // Grabar acceso
        $this->storeResourceAccess($uid);

        return view("resource-info", [
            'showDoubtsButton' => $showDoubtsButton,
            'educational_resource' => $educationalResource,
            "resources" => [
                'resources/js/educational_resource_info.js'
            ],
            'page_title' => $educationalResource->title,
        ]);
    }

    public function getResource($resourceUid)
    {
        $educationalResource = $this->getResourceDatabase($resourceUid);

        if (!$educationalResource) {
            abort(404);
        }

        return response()->json($educationalResource);
    }

    private function getResourceDatabase($resourceUid)
    {
        return EducationalResourcesModel::with('status', 'contactEmails', 'educationalResourceType', 'categories', 'learningResults')->select('educational_resources.*', 'califications_avg.average_calification')->with('licenseType')->whereHas('status', function ($query) {
            $query->where('code', 'PUBLISHED');
        })
            ->leftJoinSub(
                EducationalResourcesAssessmentsModel::select('educational_resources_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                    ->groupBy('educational_resources_uid'),
                'califications_avg',
                'califications_avg.educational_resources_uid',
                '=',
                'educational_resources.uid'
            )
            ->where('uid', $resourceUid)
            ->first();
    }

    public function calificate(Request $request)
    {
        // Validamos que calification sea un número entre 1 y 5
        $request->validate([
            'calification' => 'required|integer|between:1,5',
            'educational_resource_uid' => 'required|exists:educational_resources,uid'
        ]);

        $educationalResourceUid = $request->input('educational_resource_uid');

        $calificationValue = $request->input('calification');

        $calification = EducationalResourcesAssessmentsModel::where('user_uid', auth()->user()->uid)
            ->where('educational_resources_uid', $educationalResourceUid)
            ->first();

        if ($calification) {
            $calification->calification = $calificationValue;
            $calification->save();
        } else {
            $calification = new EducationalResourcesAssessmentsModel();
            $calification->uid = generate_uuid();
            $calification->user_uid = auth()->user()->uid;
            $calification->educational_resources_uid = $educationalResourceUid;
            $calification->calification = $calificationValue;
            $calification->save();
        }

        return response()->json(['message' => 'Se ha registrado correctamente la calificación'], 200);
    }

    private function storeResourceAccess($resourceUid)
    {
        $educationalResourceAccess = new EducationalResourceAccessModel();
        $educationalResourceAccess->uid = generate_uuid();
        if (auth()->user()) {
            $educationalResourceAccess->user_uid = auth()->user()->uid;
        }
        $educationalResourceAccess->educational_resource_uid = $resourceUid;
        $educationalResourceAccess->date = now();
        $educationalResourceAccess->save();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EducationalResourceAccessModel;
use App\Models\EducationalResourcesAssessmentsModel;
use App\Models\EducationalResourcesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class ResourceInfoController extends BaseController
{

    public function index($uid)
    {
        $educational_resource = $this->getResourceDatabase($uid);

        if (!$educational_resource) abort(404);

        return view("resource-info", [
            'educational_resource' => $educational_resource,
            "resources" => [
                'resources/js/educational_resource_info.js'
            ]
        ]);
    }

    public function getResource($resource_uid)
    {
        $educational_resource = $this->getResourceDatabase($resource_uid);

        if (!$educational_resource) abort(404);

        return response()->json($educational_resource);
    }

    private function getResourceDatabase($resource_uid)
    {
        $educational_resource = EducationalResourcesModel::with('status')->select('educational_resources.*', 'califications_avg.average_calification')->with('licenseType')->whereHas('status', function ($query) {
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
            ->where('uid', $resource_uid)
            ->first();

        return $educational_resource;
    }

    public function calificate(Request $request)
    {
        // Validamos que calification sea un número entre 1 y 5
        $request->validate([
            'calification' => 'required|integer|between:1,5',
            'educational_resource_uid' => 'required|exists:educational_resources,uid'
        ]);

        $educational_resource_uid = $request->input('educational_resource_uid');
        $calification = $request->input('calification');

        $calificationValue = $request->input('calification');

        $calification = EducationalResourcesAssessmentsModel::where('user_uid', auth()->user()->uid)
            ->where('educational_resources_uid', $educational_resource_uid)
            ->first();

        if ($calification) {
            $calification->calification = $calificationValue;
            $calification->save();
        } else {
            $calification = new EducationalResourcesAssessmentsModel();
            $calification->uid = generate_uuid();
            $calification->user_uid = auth()->user()->uid;
            $calification->educational_resources_uid = $educational_resource_uid;
            $calification->calification = $calificationValue;
            $calification->save();
        }

        return response()->json(['message' => 'Se ha registrado correctamente la calificación'], 200);
    }

    public function saveAccessResource(Request $request) {
        $resourceUid = $request->input('educational_resource_uid');

        EducationalResourceAccessModel::insert([
            'uid' => generate_uuid(),
            'user_uid' => auth()->user()->uid,
            'educational_resource_uid' => $resourceUid,
            'date' => now(),
        ]);

        return response()->json(['message' => 'Se ha registrado correctamente el acceso al recurso'], 200);
    }
}

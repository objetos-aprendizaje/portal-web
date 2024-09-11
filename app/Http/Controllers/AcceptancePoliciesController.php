<?php

namespace App\Http\Controllers;

use App\Models\FooterPagesModel;
use App\Models\UserPoliciesAcceptedModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcceptancePoliciesController extends BaseController
{
    public function index()
    {
        $policiesMustAccept = session('policiesMustAccept');

        return view('accept_policies', [
            'policiesMustAccept' => $policiesMustAccept,
            'resources' =>
            [
                "resources/js/accept_policies.js",
            ],
        ]);
    }

    public function acceptPolicies(Request $request)
    {
        $acceptedPolicies = $request->input('acceptedPolicies');

        DB::transaction(function () use ($acceptedPolicies) {
            $userUid = Auth::user()->uid;

            foreach ($acceptedPolicies as $acceptedPolicy) {
                $acceptedPolicyDb = UserPoliciesAcceptedModel::where('user_uid', $userUid)->where('footer_page_uid', $acceptedPolicy)->first();
                $footerPage = FooterPagesModel::where('uid', $acceptedPolicy)->first();

                if (!$acceptedPolicyDb) {
                    $acceptedPolicyDb = new UserPoliciesAcceptedModel();
                    $acceptedPolicyDb->uid = generate_uuid();
                    $acceptedPolicyDb->footer_page_uid = $footerPage->uid;
                    $acceptedPolicyDb->user_uid = $userUid;
                }

                $acceptedPolicyDb->version = $footerPage->version;
                $acceptedPolicyDb->accepted_at = now();

                $acceptedPolicyDb->save();
            }
        });

        return response()->json([
            'message' => 'Preferencias guardadas correctamente'
        ]);
    }
}

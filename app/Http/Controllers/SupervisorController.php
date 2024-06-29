<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupervisorCreateProposalApprovalStatusRequest;
use App\Models\Proposal;
use App\Models\Supervisor;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
    public function getAll(): JsonResponse 
    {
        $supervisors = Supervisor::paginate();
        return response()->json($supervisors, 201, ["Content-Type" => "application/json"]);
    }


    public function getSupervisorProposal() 
    {
        $user = Auth::user();
        $supervisor = Supervisor::where('user_id', $user->id)->first();
        
        $proposals = DB::table('supevisors_proposals')
        ->join('proposals', 'supevisors_proposals.proposal_id', '=', 'proposals.id')
        ->join('supervisors', 'supevisors_proposals.supervisor_id', '=', 'supervisors.id')
        ->join('students', 'proposals.student_id', '=', 'students.id')
        ->select('proposals.title', 'proposals.id', 'proposals.upload_date', "students.name", "students.nrp", "proposals.supervisors_approval_status")
        ->where('supervisors.id', $supervisor->id)
        ->get();

        return $proposals;

    }

    public function getSupervisorProposalByProposalId($proposal_id) 
    {

        if(!Proposal::where('id', $proposal_id)->first()) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "not_found" => ["proposal data not found"]
                    ]
                ]
            ], 401));
        }


        $user = Auth::user();
        $supervisor = Supervisor::where('user_id', $user->id)->first();
        
        $proposals = DB::table('supevisors_proposals')
        ->join('proposals', 'supevisors_proposals.proposal_id', '=', 'proposals.id')
        ->join('supervisors', 'supevisors_proposals.supervisor_id', '=', 'supervisors.id')
        ->select('proposals.title', 'proposals.id', 'proposals.upload_date', 'supevisors_proposals.supervisor_approval_status')
        ->where('supervisors.id', $supervisor->id)
        ->where('proposals.id', $proposal_id)
        ->first();

        return $proposals;
    }

    public function createProposalApprovalStatus($proposal_id, SupervisorCreateProposalApprovalStatusRequest $request)
    {
        $data = $request->validated();
        
        $user = Auth::user();
        $supervisor = Supervisor::where('user_id', $user->id)->first();


        if (($data["supervisor_approval_status"] === "rejected" || $data["supervisor_approval_status"] === "revision") && $data["suggestion"] === null) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "if_rejected_gave_suggestion" => ["Please provide suggestions on revision or rejected proposals"]
                    ]
                ]
            ], 401));
        }


        DB::table('supevisors_proposals')
        ->where('supervisor_id', $supervisor->id)
        ->where('proposal_id', $proposal_id)
        ->update(['supervisor_approval_status' => $data["supervisor_approval_status"], 'suggestion' => $data["suggestion"] ]);

        $revision_status = DB::table('supevisors_proposals')
        ->select('supevisors_proposals.id')
        ->where('proposal_id', $proposal_id)
        ->where('supervisor_approval_status', "revision")
        ->count();//query take amount supervisor proposal
        
        if($revision_status !== 0) {
            $proposal = Proposal::find($proposal_id);
            $proposal->supervisors_approval_status = "revision";
            $proposal->save();
        }


        $amount_supervisors_proposals = DB::table('supevisors_proposals')
        ->select('supevisors_proposals.id')
        ->where('proposal_id', $proposal_id)
        ->count();//query take amount supervisor proposal

        $amount_proposals_approved_supervisors = DB::table('supevisors_proposals')
        ->select('supevisors_proposals.id')
        ->where('proposal_id', $proposal_id)
        ->where('supervisor_approval_status', "approved")
        ->count();//query take amount approved proposal

        if ($amount_supervisors_proposals === $amount_proposals_approved_supervisors) {
            $proposal = Proposal::find($proposal_id);
            $proposal->supervisors_approval_status = "approved";
            $proposal->save();
        }

    }
    
}
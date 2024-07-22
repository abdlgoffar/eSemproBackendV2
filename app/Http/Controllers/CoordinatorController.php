<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordinatorCreateProposalAssessmentStatusRequest;
use App\Models\Coordinator;
use App\Models\Proposal;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoordinatorController extends Controller
{
    public function getAll(): JsonResponse 
    {
        $coordinators = Coordinator::paginate();
        return response()->json($coordinators, 201, ["Content-Type" => "application/json"]);
    }

    public function getInvitations() 
    {
        $user = Auth::user();
        $coordinator = Coordinator::where('user_id', $user->id)->first();

        $invitations = DB::table('coordinators')
        ->join('invitations', 'invitations.coordinator_id', '=', 'coordinators.id')
        ->select('coordinators.name as coordinator_name', 'invitations.implementation_hour as invitation_hour', 'invitations.implementation_date as invitation_date',)
        ->where('coordinators.id',   $coordinator->id)
        ->distinct()
        ->get();
    
        return $invitations;
    }

    public function getCoordinatorProposal() 
    {
        $user = Auth::user();
        $coordinator = Coordinator::where('user_id', $user->id)->first();
        
        $proposals = DB::table('proposals')
        ->join('invitations', 'invitations.id', '=', 'proposals.invitation_id')
        ->join('coordinators', 'coordinators.id', '=', 'invitations.coordinator_id')
         ->join('students', 'proposals.student_id', '=', 'students.id')
        ->select('proposals.title', 'proposals.id', 'proposals.upload_date', "students.name", "students.nrp", )
        ->where('coordinators.id',   $coordinator->id)
        ->whereIn('proposals.examiners_approval_status', ['approved', 'revision', 'rejected', 'pending'])
        ->paginate(1);

        return $proposals;
    }
    

    public function getCoordinatorProposalByProposalId($proposal_id) 
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
        $coordinator = Coordinator::where('user_id', $user->id)->first();
        
        $proposal = DB::table('proposals')
        ->join('invitations', 'invitations.id', '=', 'proposals.invitation_id')
        ->join('coordinators', 'coordinators.id', '=', 'invitations.coordinator_id')
        ->join('students', 'proposals.student_id', '=', 'students.id')
        ->select('proposals.title', 'proposals.id', 'proposals.upload_date', 'proposals.coordinator_approval_status', "students.name", "students.nrp", "students.phone")
        ->where('coordinators.id',   $coordinator->id)
        ->whereIn('proposals.examiners_approval_status', ['approved', 'revision', 'rejected', 'pending'])
        ->where('proposals.id', $proposal_id)
        ->first();

        $assessmentProposal = DB::table('examiners')
        ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
        ->select('examiners.name', 'examiners_proposals.*')
        ->where('examiners_proposals.proposal_id',   $proposal_id)
        ->distinct()
        ->get();



        return response()->json(["proposal" => $proposal, "assessment_proposal" => $assessmentProposal], 201);
    }

    public function createProposalAssessmentStatus($proposal_id, CoordinatorCreateProposalAssessmentStatusRequest $request)
    {
        $data = $request->validated();
        
        DB::table('proposals')
        ->where('id', $proposal_id)
        ->update(['coordinator_approval_status' => $data["coordinator_assessment_status"]]);

    }
}
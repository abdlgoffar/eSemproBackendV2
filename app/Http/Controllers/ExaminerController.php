<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExaminerCreateProposalAssessmentStatusRequest;
use App\Models\Examiner;
use App\Models\Proposal;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExaminerController extends Controller
{
    public function getAll(): JsonResponse 
    {
        $examiners = Examiner::paginate();
        return response()->json($examiners, 201, ["Content-Type" => "application/json"]);
    }


    public function getInvitations() 
    {
        $user = Auth::user();
        $examiner = Examiner::where('user_id', $user->id)->first();

        $invitations = DB::table('examiners')
        ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
        ->join('proposals', 'proposals.id', '=', 'examiners_proposals.proposal_id')
        ->join('invitations', 'invitations.id', '=', 'proposals.invitation_id')
        ->select('examiners.name as examiner_name', 'invitations.implementation_hour as invitation_hour', 'invitations.implementation_date as invitation_date',)
        ->where('examiners.id',   $examiner->id)
        ->distinct()
        ->get();
    
        return $invitations;
    }

    public function getProposals() 
    {
        $user = Auth::user();
        $examiner = Examiner::where('user_id', $user->id)->first();

        $proposals = DB::table('examiners')
        ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
        ->join('proposals', 'proposals.id', '=', 'examiners_proposals.proposal_id')
        ->join('invitations', 'invitations.id', '=', 'proposals.invitation_id')
        ->join('students', 'proposals.student_id', '=', 'students.id')
        ->select('proposals.id','proposals.title', 'proposals.upload_date',  "students.name", "students.nrp", "proposals.examiners_approval_status")
        ->where('examiners.id',   $examiner->id)
        ->distinct()
        ->paginate(1);
    
        return $proposals;
    }

    public function getExaminerProposalByProposalId($proposal_id)
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
        $examiner = Examiner::where('user_id', $user->id)->first();

        $proposal = DB::table('examiners')
        ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
        ->join('proposals', 'proposals.id', '=', 'examiners_proposals.proposal_id')
        ->join('invitations', 'invitations.id', '=', 'proposals.invitation_id')
        ->join('students', 'proposals.student_id', '=', 'students.id')
        ->select('proposals.id','proposals.title', 'proposals.upload_date', "examiners_proposals.examiner_assessment_status", "students.name", "students.nrp", "students.phone")
        ->where('examiners.id',   $examiner->id)
        ->where('proposals.id',   $proposal_id)
        ->distinct()
        ->first();
    
        $examiners = DB::table('examiners')
        ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
        ->select('examiners.name', 'examiners_proposals.*')
        ->where('examiners_proposals.proposal_id',   $proposal_id)
        ->distinct()
        ->get();

        return response()->json(["proposal" => $proposal, "examiners" => $examiners], 201);
    }

    public function createProposalAssessmentStatus($proposal_id, ExaminerCreateProposalAssessmentStatusRequest $request)
    {
        $data = $request->validated();
        
        $user = Auth::user();
        $examiner = Examiner::where('user_id', $user->id)->first();

        
        if (($data["examiner_assessment_status"] === "rejected" || $data["examiner_assessment_status"] === "revision") && $data["suggestion"] === null) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "if_rejected_or_revision_gave_suggestion" => ["Please provide suggestions on rejected or revision proposals"]
                    ]
                ]
            ], 401));
        }

        DB::table('examiners_proposals')
        ->where('examiner_id', $examiner->id)
        ->where('proposal_id', $proposal_id)
        ->update(['examiner_assessment_status' => $data["examiner_assessment_status"], 'suggestion' => $data["suggestion"]]);

        $amount_examiners_proposals = DB::table('examiners_proposals')
        ->select('examiners_proposals.id')
        ->where('proposal_id', $proposal_id)
        ->count();//query take amount examiner proposal

        $amount_proposals_approved_examiners = DB::table('examiners_proposals')
        ->select('examiners_proposals.id')
        ->where('proposal_id', $proposal_id)
        ->whereIn('examiner_assessment_status', ['accepted'])
        ->count();//query take amount approved 

        $revision = DB::table('examiners_proposals')
        ->select('examiners_proposals.id')
        ->where('proposal_id', $proposal_id)
        ->where("examiner_assessment_status", "revision")
        ->count();

        $rejected = DB::table('examiners_proposals')
        ->select('examiners_proposals.id')
        ->where('proposal_id', $proposal_id)
        ->where("examiner_assessment_status", "rejected")
        ->count();

        if( $revision !== 0) {
            $n1 = Proposal::find($proposal_id);
            $n1->examiners_approval_status = "revision";
            $n1->save();
        }

        if( $rejected !== 0) {
            $n2 = Proposal::find($proposal_id);
            $n2->examiners_approval_status = "rejected";
            $n2->save();
        }

        
        if ($amount_examiners_proposals === $amount_proposals_approved_examiners) {
            $n3 = Proposal::find($proposal_id);
            $n3->examiners_approval_status = "approved";
            $n3->save();
        }

        
    }



}
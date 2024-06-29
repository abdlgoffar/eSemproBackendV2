<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeadStudyProgramCreateProposalApprovalStatusRoomAndExaminerRequest;
use App\Models\HeadStudyProgram;
use App\Models\Proposal;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HeadStudyProgramController extends Controller
{
    public function getAll(): JsonResponse 
    {
        $headStudyPrograms = HeadStudyProgram::paginate();
        return response()->json($headStudyPrograms, 201, ["Content-Type" => "application/json"]);
    }


  
    public function getHeadStudyProgramProposal() 
    {
        $user = Auth::user();
        $headStudyProgram = HeadStudyProgram::where('user_id', $user->id)->first();
        
        $proposals = DB::table('proposals')
        ->join('head_study_programs', 'proposals.head_study_program_id', '=', 'head_study_programs.id')
        ->join('students', 'proposals.student_id', '=', 'students.id')
        ->select('proposals.title', 'proposals.id', 'proposals.upload_date', "students.name", "students.nrp", "proposals.head_study_program_approval_status")
        ->where('head_study_programs.id', $headStudyProgram->id)
        ->where('proposals.supervisors_approval_status', "approved")
        ->get();

        return $proposals;
    }

    
    public function getHeadStudyProgramProposalByProposalId($proposal_id) 
    {
        $user = Auth::user();
        $headStudyProgram = HeadStudyProgram::where('user_id', $user->id)->first();
        
        $proposals = DB::table('proposals')
        ->join('head_study_programs', 'proposals.head_study_program_id', '=', 'head_study_programs.id')
        ->select('proposals.title', 'proposals.id', 'proposals.upload_date', 'proposals.head_study_program_approval_status')
        ->where('head_study_programs.id', $headStudyProgram->id)
        ->where('proposals.id', $proposal_id)
        ->where('proposals.supervisors_approval_status', "approved")
        ->first();

        return $proposals;
    }


    public function createProposalApprovalStatusRoomAndExaminer(HeadStudyProgramCreateProposalApprovalStatusRoomAndExaminerRequest $request, $proposal_id) 
    {
        $data = $request->validated();
        
        
        $proposal = Proposal::where('id', $proposal_id)->first();

        if (!$proposal) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "proposal_data_not_found" => ["proposal data not found"]
                    ]
                ]
            ], 404));
        }
        $proposal->room_id = $data["room"];
        $proposal->head_study_program_approval_status = "approved";
        $proposal->save();

        foreach ($data["examiners"] as $i) {
            DB::table('examiners_proposals')->insert([
                'examiner_id' => $i,
                'proposal_id' => $proposal_id
            ]);
        }

        return response()->json(["proposal" => $proposal], 200);

    }
}
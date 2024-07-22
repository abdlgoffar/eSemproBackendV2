<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeadStudyProgramCreateProposalApprovalStatusRoomAndExaminerRequest;
use App\Http\Requests\HeadStudyProgramUpdateProposalExaminersRequest;
use App\Models\Examiner;
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


  
    // public function getHeadStudyProgramProposal() 
    // {
    //     $user = Auth::user();
    //     $headStudyProgram = HeadStudyProgram::where('user_id', $user->id)->first();
        
    //     $proposals = DB::table('proposals')
    //     ->join('head_study_programs', 'proposals.head_study_program_id', '=', 'head_study_programs.id')
    //     ->join('students', 'proposals.student_id', '=', 'students.id')
    //     ->join('examiners_proposals', 'examiners_proposals.proposal_id', '=', 'proposals.id')
    //     ->join('examiners', 'examiners_proposals.examiner_id', '=', 'examiners.id')
    //     ->select('proposals.title', 'proposals.id', 'proposals.upload_date', "students.name", "students.nrp", "proposals.head_study_program_approval_status", "examiners.name as examiner")
    //     ->where('head_study_programs.id', $headStudyProgram->id)
    //     ->where('proposals.supervisors_approval_status', "approved")
    //     ->get();

    //     return $proposals;
    // }

    public function getHeadStudyProgramProposal() 
    {
        $user = Auth::user();
        $headStudyProgram = HeadStudyProgram::where('user_id', $user->id)->first();
        
        $proposals = DB::table('proposals')
            ->join('head_study_programs', 'proposals.head_study_program_id', '=', 'head_study_programs.id')
            ->join('students', 'proposals.student_id', '=', 'students.id')
            ->join('supevisors_proposals', 'supevisors_proposals.proposal_id', '=', 'proposals.id')
            ->join('supervisors', 'supevisors_proposals.supervisor_id', '=', 'supervisors.id')
            ->select(
                'proposals.title', 
                'proposals.id', 
                'proposals.coordinator_approval_status', 
                'students.name as student_name', 
                'students.nrp', 
                'proposals.head_study_program_approval_status', 
                DB::raw('GROUP_CONCAT(supervisors.name SEPARATOR ", ") as supervisors')
            )
            ->where('head_study_programs.id', $headStudyProgram->id)
            ->where('proposals.supervisors_approval_status', 'approved')
            ->groupBy('proposals.id', 'proposals.title', 'proposals.coordinator_approval_status', 'students.name', 'students.nrp', 'proposals.head_study_program_approval_status')
            ->paginate(1);

        return $proposals;
    }

    public function getHeadStudyProgramProposalHaveExaminers() 
    {
        $user = Auth::user();
        $headStudyProgram = HeadStudyProgram::where('user_id', $user->id)->first();
        
        $proposals = DB::table('proposals')
            ->join('head_study_programs', 'proposals.head_study_program_id', '=', 'head_study_programs.id')
            ->join('students', 'proposals.student_id', '=', 'students.id')
            ->join('examiners_proposals', 'examiners_proposals.proposal_id', '=', 'proposals.id')
            ->select(
                'proposals.title', 
                'proposals.id', 
                'proposals.upload_date', 
                'students.name as student_name', 
                'students.nrp', 
                'proposals.head_study_program_approval_status', 
            )
            ->where('head_study_programs.id', $headStudyProgram->id)
            ->where('proposals.supervisors_approval_status', 'approved')
            ->where('proposals.examiners_approval_status', "pending")
            ->groupBy('proposals.id', 'proposals.title', 'proposals.upload_date', 'students.name', 'students.nrp', 'proposals.head_study_program_approval_status')
            ->paginate(1);

            return $proposals;
    }

    public function getAllExaminers($proposal_id) {
        $examiners = DB::table('examiners')
        ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
        ->select('examiners.name', 'examiners_proposals.*')
        ->where('examiners_proposals.proposal_id',   $proposal_id)
        ->distinct()
        ->get();

        return $examiners;
    }


    // public function getHeadStudyProgramProposal() 
    // {
    //     $user = Auth::user();
    //     $headStudyProgram = HeadStudyProgram::where('user_id', $user->id)->first();
        
    //     $proposals = DB::table('proposals')
    //         ->join('head_study_programs', 'proposals.head_study_program_id', '=', 'head_study_programs.id')
    //         ->join('students', 'proposals.student_id', '=', 'students.id')
    //         ->join('examiners_proposals', 'examiners_proposals.proposal_id', '=', 'proposals.id')
    //         ->join('examiners', 'examiners_proposals.examiner_id', '=', 'examiners.id')
    //         ->select(
    //             'proposals.title', 
    //             'proposals.id', 
    //             'proposals.upload_date', 
    //             'students.name as student_name', 
    //             'students.nrp', 
    //             'proposals.head_study_program_approval_status', 
    //             'examiners.name as examiner_name'
    //         )
    //         ->where('head_study_programs.id', $headStudyProgram->id)
    //         ->where('proposals.supervisors_approval_status', 'approved')
    //         ->get();

    //     // Reformat the proposals to have examiners in separate columns
    //     $result = [];
    //     foreach ($proposals as $proposal) {
    //         if (!isset($result[$proposal->id])) {
    //             $result[$proposal->id] = [
    //                 'title' => $proposal->title,
    //                 'id' => $proposal->id,
    //                 'upload_date' => $proposal->upload_date,
    //                 'student_name' => $proposal->student_name,
    //                 'nrp' => $proposal->nrp,
    //                 'head_study_program_approval_status' => $proposal->head_study_program_approval_status,
    //                 'examiners' => []
    //             ];
    //         }
    //         $result[$proposal->id]['examiners'][] = $proposal->examiner_name;
    //     }

    //     // Convert examiners array to separate columns
    //     $finalResult = [];
    //     foreach ($result as $proposal) {
    //         $examiners = $proposal['examiners'];
    //         unset($proposal['examiners']);
    //         foreach ($examiners as $index => $examiner) {
    //             $proposal['examiner_' . ($index + 1)] = $examiner;
    //         }
    //         $finalResult[] = $proposal;
    //     }

    //     return $finalResult;
    // }


    
    public function getHeadStudyProgramProposalByProposalId($proposal_id) 
    {
        $user = Auth::user();
        $headStudyProgram = HeadStudyProgram::where('user_id', $user->id)->first();
        
        $proposal = DB::table('proposals')
        ->join('head_study_programs', 'proposals.head_study_program_id', '=', 'head_study_programs.id')
        ->join('students', 'proposals.student_id', '=', 'students.id')
        ->select('proposals.title', 'proposals.id', 'proposals.upload_date', 'proposals.head_study_program_approval_status',  "students.name", "students.nrp", "students.phone")
        ->where('head_study_programs.id', $headStudyProgram->id)
        ->where('proposals.id', $proposal_id)
        ->where('proposals.supervisors_approval_status', "approved")
        ->first();

        $supervisors = DB::table('supervisors')
            ->join('supevisors_proposals', 'supevisors_proposals.supervisor_id', '=', 'supervisors.id')
            ->select('supervisors.name', 'supevisors_proposals.*')
            ->where('supevisors_proposals.proposal_id',   $proposal_id)
            ->get();

            
    

        return response()->json(["proposal" => $proposal,"supervisors" => $supervisors], 201);
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
        foreach ($data["examiners"] as $i) {
            $examiner = Examiner::find($i);
           if (DB::table('supevisors_proposals')
           ->join('supervisors', 'supevisors_proposals.supervisor_id', '=', 'supervisors.id')
           ->where('proposal_id', $proposal_id)
           ->where("supervisors.name", $examiner->name)
           ->count() !== 0) {
            throw new HttpResponseException(response([
                                "errors" => [
                                    "messages" => [
                                        "supervisor_cannotbe_examiner" => ["{$examiner->name} cannot be an examiner because of the supervisor"]
                                    ]
                                ]
                            ], 404));
           }
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


    public function updateProposalExaminers(HeadStudyProgramUpdateProposalExaminersRequest $request) {
        $data = $request->validated();

        $updated = DB::table('examiners_proposals')
        ->where('proposal_id', $data["proposal_id"])
        ->where('examiner_id', $data["old_examiner"])
        ->update(['examiner_id' => $data["new_examiner"]]);
    }
}
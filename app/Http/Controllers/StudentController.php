<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRevisionRequest;
use App\Http\Requests\StudentSupervisorCreateRequest;
use App\Models\Proposal;
use App\Models\ProposalPdf;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{

    public function createStudentSupervisor(StudentSupervisorCreateRequest $request, $proposal_id): JsonResponse
    {
        $user = Auth::user();
       
        $data = $request->validated();

        foreach ($data["supervisors"] as $i) {
            DB::table('supevisors_proposals')->insert([
                'supervisor_id' => $i,
                'proposal_id' => $proposal_id
            ]);
        }



        return response()->json("Ok", 201);
    }


    public function getInvitations()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $invitations = DB::table('students')
            ->join('proposals', 'proposals.student_id', '=', 'students.id')
            ->join('invitations', 'invitations.id', '=', 'proposals.invitation_id')
            ->select('students.name as student_name', 'invitations.implementation_hour as invitation_hour', 'invitations.implementation_date as invitation_date',)
            ->where('students.id',   $student->id)
            ->distinct()
            ->get();

        return $invitations;
    }

    public function getProposals()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $proposal = DB::table('proposals')
            ->join('students', 'students.id', '=', 'proposals.student_id')
            ->select('proposals.id', 'proposals.title', 'proposals.supervisors_approval_status', 'proposals.examiners_approval_status',  'proposals.coordinator_approval_status')
            ->where('students.id',   $student->id)
            ->distinct()
            ->get();

        return $proposal;
    }


    public function getProposalByProposalId($proposal_id)
    {
        if (!Proposal::where('id', $proposal_id)->first()) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "not_found" => ["proposal data not found"]
                    ]
                ]
            ], 401));
        }

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $proposal = DB::table('proposals')
            ->join('students', 'students.id', '=', 'proposals.student_id')
            ->select('proposals.id', 'proposals.title', 'proposals.supervisors_approval_status', 'proposals.examiners_approval_status', 'proposals.coordinator_approval_status')
            ->where('students.id',   $student->id)
            ->where('proposals.id',   $proposal_id)
            ->first();

        $examiners = DB::table('examiners')
            ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
            ->select('examiners.name', 'examiners_proposals.*')
            ->where('examiners_proposals.proposal_id',   $proposal_id)
            ->distinct()
            ->get();

        $supervisors = DB::table('supervisors')
            ->join('supevisors_proposals', 'supevisors_proposals.supervisor_id', '=', 'supervisors.id')
            ->select('supervisors.name', 'supevisors_proposals.*')
            ->where('supevisors_proposals.proposal_id',   $proposal_id)
            ->get();

        $coordinator = DB::table('proposals')
            ->join('students', 'students.id', '=', 'proposals.student_id')
            ->join("invitations", "proposals.invitation_id", "=", "invitations.id")
            ->join("coordinators", "invitations.coordinator_id", "=", "coordinators.id")
            ->select('coordinators.name', "proposals.coordinator_approval_status")
            ->where('students.id',   $student->id)
            ->where('proposals.id',   $proposal_id)
            ->get();


        return response()->json(["proposal" => $proposal, "examiners" => $examiners, "supervisors" => $supervisors, "student" => $student, "coordinator" => $coordinator], 201);
    }


    public function revision($proposal_id, StudentRevisionRequest $request) 
    {
        $request->validated();

        $amountProposalFile = ProposalPdf::where("proposal_id", $proposal_id)->count();

        if ($amountProposalFile == 1) {
            $file = $request->file('proposal_file');
            $name = time() . $file->getClientOriginalName();
            $file->storeAs('public/PDF/Proposals', $name);

            $newProposalPdf = new ProposalPdf();
            $newProposalPdf->saved_name = $name;
            $newProposalPdf->original_name = $file->getClientOriginalName();
            $newProposalPdf->path = 'public/PDF/Proposals';
            $newProposalPdf->proposal_id = $proposal_id;
            $newProposalPdf->save();
        } else {
            $oldProposalPdf = ProposalPdf::where("proposal_id", $proposal_id)->orderBy('created_at', 'asc')->first();
            //create proposal new
            $file = $request->file('proposal_file');
            $name = time() . $file->getClientOriginalName();
            $file->storeAs('public/PDF/Proposals', $name);
             
            $proposalPdf = new ProposalPdf();
            $proposalPdf->saved_name = $name;
            $proposalPdf->original_name = $file->getClientOriginalName();
            $proposalPdf->path = 'public/PDF/Proposals';
            $proposalPdf->proposal_id = $proposal_id;
            $proposalPdf->save();
    
            Storage::delete($oldProposalPdf->path."/".$oldProposalPdf->saved_name);//delete proposal pdf file from server
            $oldProposalPdf->forceDelete(); //hard delete proposal pdf from database
             
        }
        
         return response()->json("OK")->setStatusCode(201);        
    }
}
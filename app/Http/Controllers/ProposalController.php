<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProposalCreateRequest;
use App\Models\Proposal;
use App\Models\ProposalPdf;
use App\Models\Student;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProposalController extends Controller
{
    

    public function create(ProposalCreateRequest $request)
    {
        
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $data = $request->validated();

        //create proposal 
        $proposal = new Proposal();
        $proposal->title = $data["title"];
        $proposal->period = $data["period"];
        $proposal->upload_date = $data["upload_date"];
        $proposal->student_id = $student->id; 
        $proposal->head_study_program_id = $student->headStudyProgram->id; 
        $proposal->save();


        

        //create and store proposal pdf
        $file = $request->file('proposal_file');
        $name = time() . $file->getClientOriginalName();
        $file->storeAs('public/PDF/Proposals', $name);
        
        $proposalPdf = new ProposalPdf();
        $proposalPdf->saved_name = $name;
        $proposalPdf->original_name = $file->getClientOriginalName();
        $proposalPdf->path = 'public/PDF/Proposals';
        $proposalPdf->proposal_id = $proposal->id;
        $proposalPdf->save();

    

        if (!$proposal || !$proposalPdf) {
            if($proposal) Proposal::where("id", $proposal->id)->delete();//delete proposal
            Storage::delete($proposalPdf->path."/".$proposalPdf->saved_name);//delete proposal pdf file from server
            if($proposalPdf) ProposalPdf::where("id", $proposalPdf->id)->delete();//delete proposal pdf

            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "create_proposal_data_failed" => ["create data failed"]
                    ]
                ]
            ], 404));
        }

        return response()->json(["proposal" => $proposal, "proposalPdf" => $proposalPdf])->setStatusCode(201);
    }

    public function rollback(int $proposal_id) 
    {

        //remove proposal pdf
        $proposalPdf = ProposalPdf::where("proposal_id", $proposal_id)->first();
        Storage::delete($proposalPdf->path."/".$proposalPdf->saved_name);//delete proposal pdf file from server
        $proposalPdf->delete();
                
        //remove proposal
        Proposal::find($proposal_id)->delete();

    }

    public function getProposalPdf($proposal_id): StreamedResponse
    {

        $proposal = Proposal::where("id", $proposal_id)->first();

        if (!$proposal) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "proposal_data_not_found" => ["proposal data not found"]
                    ]
                ]
            ], 404));
        }
        
        
        $proposalPdf = $proposal->proposalPdf;
        return Storage::download($proposalPdf->path . "/{$proposalPdf->saved_name}", $proposalPdf->original_name, ['Content-Type: application/pdf']);
    }

}
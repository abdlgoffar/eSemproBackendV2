<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicAdministrationCreateInvitationRequest;
use App\Http\Requests\AcademicAdministrationCreateProposalsInvitationsRequest;
use App\Models\Invitation;
use App\Models\InvitationPdf;
use App\Models\Proposal;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AcademicAdministrationController extends Controller
{
    
    public function getStudents($room_id) {
        $students = DB::table('examiners_proposals')
        ->join('proposals', 'proposals.id', '=', 'examiners_proposals.proposal_id')
        ->join('examiners', 'examiners.id', '=', 'examiners_proposals.examiner_id')
        ->join('students', 'students.id', '=', 'proposals.student_id')
        ->join('rooms', 'rooms.id', '=', 'proposals.room_id')
        ->select('students.name as student_name', 'proposals.title as proposal_title', 'proposals.id as proposal_id', 'proposals.invitation_id as proposal_invitation')
        ->where('proposals.head_study_program_approval_status', "approved")
        ->where('rooms.id', $room_id)
        ->distinct()
        ->get();
    
        return $students;
    }



    public function createInvitation(AcademicAdministrationCreateInvitationRequest $request)
    {
        $data = $request->validated();

        //create invitation
        $invitation = new Invitation();
        $invitation->implementation_hour = $data["hour"];
        $invitation->implementation_date = $data["date"];
        $invitation->coordinator_id = $data["coordinator"];
        $invitation->save();


        //create and store invittaion pdf
        $file = $request->file('invitation_file');
        $name = time() . $file->getClientOriginalName();
        $file->storeAs('public/PDF/Invitations', $name);

        $invitationPdf = new InvitationPdf();
        $invitationPdf->saved_name = $name;
        $invitationPdf->original_name = $file->getClientOriginalName();
        $invitationPdf->path = 'public/PDF/Invitations';
        $invitationPdf->invitation_id = $invitation->id;
        $invitationPdf->save();


        if (!$invitation || !$invitationPdf) {
            if($invitation) Invitation::where("id", $invitation->id)->delete();//delete invitation
            Storage::delete($invitationPdf->path."/".$invitationPdf->saved_name);//delete invittaion pdf file from server
            if($invitationPdf) InvitationPdf::where("id", $invitationPdf->id)->delete();//delete invittaion pdf

            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "create_invitation_data_failed" => ["create data failed"]
                    ]
                ]
            ], 404));
        }


        return response()->json(["invitation" => $invitation, "invitationPdf" => $invitationPdf])->setStatusCode(201);


    }

    public function rollback(int $invitation_id) 
    {

        //remove invitation pdf
        $invitationPdf = InvitationPdf::where("invitation_id", $invitation_id)->first();
        Storage::delete($invitationPdf->path."/".$invitationPdf->saved_name);//delete invitation pdf file from server
        $invitationPdf->delete();
                
        //remove invitation
        Invitation::find($invitation_id)->delete();

    }


    public function createProposalsInvitations($invitation_id, AcademicAdministrationCreateProposalsInvitationsRequest $request)
    {
        $data = $request->validated();
        

        foreach ($data["students_proposals"] as $i) {
            $c = DB::table('coordinators')
                        ->join('invitations', '.invitations.coordinator_id', '=', 'coordinators.id')
                        ->join('proposals', '.proposals.invitation_id', '=', 'invitations.id')
                        ->join('rooms', 'rooms.id', '=', 'proposals.room_id')
                        ->select('rooms.name', "coordinators.name as coord")
                        ->where('coordinators.id', $data["coordinator"])
                        ->where('invitations.implementation_date', $data["date"])
                        ->where('invitations.implementation_hour', $data["hour"])
                        ->first();
                
            $r = DB::table('proposals')
                        ->join('rooms', 'rooms.id', '=', 'proposals.room_id')
                        ->select('rooms.name')
                        ->where('proposals.id', $i)
                        ->first();

                        if ($c && $r && $c->name && $c->name != $r->name) {
                            $proposal = Proposal::where('id', $i)->first();
                            throw new HttpResponseException(response([
                                "errors" => [
                                    "messages" => [
                                        "coord_have_schedule" => ["The Coord {$c->coord} assigned to test the proposal {$proposal->student->name} already has a schedule At Same Time and other Class"]
                                    ]
                                ]
                            ], 404));
                        }

        }

        


        //dapatkan setiap dosen penguji proposal
        foreach ($data["students_proposals"] as $i) {
            $examiners = DB::table('proposals')
                        ->join('examiners_proposals', 'examiners_proposals.proposal_id', '=', 'proposals.id')
                        ->join('examiners', 'examiners_proposals.examiner_id', '=', 'examiners.id')
                        ->select('examiners.name')
                        ->where('proposals.id', $i)
                        ->get();

            foreach($examiners as $e) {
               
                //ketahui apakah setiap dosen penguji sudah memiliki jadwal di waktu yg sama dan dapatkan nama
                $room = DB::table('examiners')
                        ->join('examiners_proposals', 'examiners_proposals.examiner_id', '=', 'examiners.id')
                        ->join('proposals', 'examiners_proposals.proposal_id', '=', 'proposals.id')
                        ->join('invitations', 'invitations.id', '=', 'proposals.invitation_id')
                        ->join('rooms', 'rooms.id', '=', 'proposals.room_id')
                        ->select('rooms.name')
                        ->where('examiners.name', $e->name)
                        ->where('invitations.implementation_date', $data["date"])
                        ->where('invitations.implementation_hour', $data["hour"])
                        ->first();
                
                // dapatkan kelas dari proposal yang baru
                $room2 = DB::table('proposals')
                        ->join('rooms', 'rooms.id', '=', 'proposals.room_id')
                        ->select('rooms.name')
                        ->where('proposals.id', $i)
                        ->first();

                // check apakah dosen penguji memiliki jadwal bentrok.
                if ($room && $room2 && $room->name && $room->name != $room2->name) {
                    $proposal = Proposal::where('id', $i)->first();
                    throw new HttpResponseException(response([
                        "errors" => [
                            "messages" => [
                                "examiner_have_schedule" => ["The examiner {$e->name} assigned to test the proposal {$proposal->student->name} already has a schedule At Same Time and other Class"]
                            ]
                        ]
                    ], 404));
                }
            }
        }

        
        foreach ($data["students_proposals"] as $i) {
            $proposal = Proposal::where('id', $i)->first();
            $proposal->invitation_id = $invitation_id;
            $proposal->save();
        }


        return response()->json("Oke")->setStatusCode(201);


    
    }
        







        
    

   

    
    
}
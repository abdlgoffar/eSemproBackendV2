<?php


use Illuminate\Support\Facades\Route;





Route::post("/users/login", [\App\Http\Controllers\UserController::class, "login"]);
// Route::post("/users/register", [\App\Http\Controllers\UserController::class, "register"]);

Route::middleware(\App\Http\Middleware\AuthenticationMiddleware::class)->group(function () {
    Route::delete("/users/logout", [\App\Http\Controllers\UserController::class, "logout"]);
    Route::get("/users/get/current", [\App\Http\Controllers\UserController::class, "get"]);
    Route::post("/users/create", [\App\Http\Controllers\UserController::class, "createUser"]);
    Route::post("/users/create", [\App\Http\Controllers\UserController::class, "createUser"]);
    Route::get("/users/get/username/current", [\App\Http\Controllers\UserController::class, "getUserUsername"]);
    Route::post("/users/students/create", [\App\Http\Controllers\UserController::class, "createStudentUser"]);
    Route::post("/users/supervisors/create", [\App\Http\Controllers\UserController::class, "createSupervisorUser"]);
    Route::post("/users/examiners/create", [\App\Http\Controllers\UserController::class, "createExaminerUser"]);
    Route::post("/users/head-study-programs/create", [\App\Http\Controllers\UserController::class, "createHeadStudyProgramUser"]);
    Route::post("/users/coordinators/create", [\App\Http\Controllers\UserController::class, "createCoordinatorUser"]);
    Route::get("/users/get/{role}", [\App\Http\Controllers\UserController::class, "getUsersByRole"]);

    
    Route::get("/head-study-programs/get/all", [\App\Http\Controllers\HeadStudyProgramController::class, "getAll"]);
    Route::get("/head-study-programs/get/all/examiners/{proposal_id}", [\App\Http\Controllers\HeadStudyProgramController::class, "getAllExaminers"]);
    Route::get("/head-study-programs/get/proposals", [\App\Http\Controllers\HeadStudyProgramController::class, "getHeadStudyProgramProposal"]);
    Route::get("/head-study-programs/get/proposals/examiners", [\App\Http\Controllers\HeadStudyProgramController::class, "getHeadStudyProgramProposalHaveExaminers"]);
    Route::get("/head-study-programs/get/proposals/{proposal_id}", [\App\Http\Controllers\HeadStudyProgramController::class, "getHeadStudyProgramProposalByProposalId"]);
    Route::post("/head-study-programs/create/proposals/approval/{proposal_id}", [\App\Http\Controllers\HeadStudyProgramController::class, "createProposalApprovalStatusRoomAndExaminer"]);
    Route::post("/head-study-programs/update/proposals/examiner", [\App\Http\Controllers\HeadStudyProgramController::class, "updateProposalExaminers"]);

    Route::get("/supervisors/get/all", [\App\Http\Controllers\SupervisorController::class, "getAll"]);
    Route::get("/supervisors/get/proposals", [\App\Http\Controllers\SupervisorController::class, "getSupervisorProposal"]);
    Route::get("/supervisors/get/proposals/{proposal_id}", [\App\Http\Controllers\SupervisorController::class, "getSupervisorProposalByProposalId"]);
    Route::post("/supervisors/create/proposals/approval/{proposal_id}", [\App\Http\Controllers\SupervisorController::class, "createProposalApprovalStatus"]);


    Route::post("/proposals/create", [\App\Http\Controllers\ProposalController::class, "create"]);
    Route::delete("/proposals/create/rollback/{proposal_id}", [\App\Http\Controllers\ProposalController::class, "rollback"]);
    Route::get("/proposals/get/pdf/{proposal_id}", [\App\Http\Controllers\ProposalController::class, "getProposalPdf"]);
    Route::get("/proposals/get/new/pdf/{proposal_id}", [\App\Http\Controllers\ProposalController::class, "getNewProposalPdf"]);
    Route::get("/proposals/get/old/pdf/{proposal_id}", [\App\Http\Controllers\ProposalController::class, "getOldProposalPdf"]);

    


    Route::post("/create/students/supervisors/{proposal_id}", [\App\Http\Controllers\StudentController::class, "createStudentSupervisor"]);

    
    Route::get("/examiners/get/all", [\App\Http\Controllers\ExaminerController::class, "getAll"]);
    Route::post("/examiners/create/proposals/assessment/{proposal_id}", [\App\Http\Controllers\ExaminerController::class, "createProposalAssessmentStatus"]);
    Route::get("/examiners/get/invitation", [\App\Http\Controllers\ExaminerController::class, "getInvitations"]);
    Route::get("/examiners/get/proposals", [\App\Http\Controllers\ExaminerController::class, "getProposals"]);
    Route::get("/examiners/get/proposals/{proposal_id}", [\App\Http\Controllers\ExaminerController::class, "getExaminerProposalByProposalId"]);
    
    Route::get("/rooms/get/all", [\App\Http\Controllers\RoomController::class, "getAll"]);

    Route::get("/students/get/invitation", [\App\Http\Controllers\StudentController::class, "getInvitations"]);
    Route::post("/students/revision/{proposal_id}", [\App\Http\Controllers\StudentController::class, "revision"]);
    Route::get("/students/get/proposals", [\App\Http\Controllers\StudentController::class, "getProposals"]);
    Route::get("/students/get/proposals/{proposal_id}", [\App\Http\Controllers\StudentController::class, "getProposalByProposalId"]);
   


    
    Route::post("/academic-administrations/create/invitation", [\App\Http\Controllers\AcademicAdministrationController::class, "createInvitation"]);
    Route::delete("/academic-administrations/create/invitation/rollback/{invitation_id}", [\App\Http\Controllers\AcademicAdministrationController::class, "rollback"]);
    Route::post("/academic-administrations/create/proposal/invitation/{invitation_id}", [\App\Http\Controllers\AcademicAdministrationController::class, "createProposalsInvitations"]);
    Route::get("/academic-administrations/get/students/{room_id}", [\App\Http\Controllers\AcademicAdministrationController::class, "getStudents"]);
   
    Route::get("/coordinators/get/all", [\App\Http\Controllers\CoordinatorController::class, "getAll"]);
    Route::get("/coordinators/get/invitation", [\App\Http\Controllers\CoordinatorController::class, "getInvitations"]);
    Route::get("/coordinators/get/proposals", [\App\Http\Controllers\CoordinatorController::class, "getCoordinatorProposal"]);
    Route::get("/coordinators/get/proposals/{proposal_id}", [\App\Http\Controllers\CoordinatorController::class, "getCoordinatorProposalByProposalId"]);
    Route::post("/coordinators/create/proposals/assessment/{proposal_id}", [\App\Http\Controllers\CoordinatorController::class, "createProposalAssessmentStatus"]);
    

});
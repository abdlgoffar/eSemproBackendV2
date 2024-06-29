<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCoordinatorCreateRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserExaminerCreateRequest;
use App\Http\Requests\UserHeadStudyProgramCreateRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserStudentCreateRequest;
use App\Http\Requests\UserSupervisorCreateRequest;
use App\Http\Resources\UserResponse;
use App\Models\Coordinator;
use App\Models\Examiner;
use App\Models\HeadStudyProgram;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;



class UserController extends Controller
{

    private function checkUsernameIsAvailable($data) 
    {
       
        if (User::where("username", $data)->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "username_is_available" =>  ["username already registered"]
                    ]
                ]
            ], 404));
        }
    }

    public function login(UserLoginRequest $request): UserResponse
    {
        $data = $request->validated();

        $user = User::where('username', $data["username"] )->where('role', $data["role"] )->first();
        
        // check username available and password is valid
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                "errors" => [
                    "messages" => [
                        "account_not_found" => ["Account Not Found"]
                    ]
                ]
            ], 401));
        }

        $user->token = Str::uuid()->toString();
        $user->token_expired = time() + 86400; //token will expired in 24 hour or 1 days
        $user->save();

        return new UserResponse($user);
    }
    
    public function get(): UserResponse
    {
        $user = Auth::user();
        
        return new UserResponse($user);
    }

    public function getUserUsername()
    {
        $auth = Auth::user();

        $user = User::with('student')->with('examiner')->with('supervisor')->with('headStudyProgram')->with('coordinator')->where("token", $auth->token)->first();
        return $user;
    }

    public function createUser(UserCreateRequest $request) 
    {
        $data = $request->validated();

        foreach ($data["roles"] as $i) {
            if (DB::table('users')->where('username', $data["username"] )->where('role', $i)->count() != 0) {
                throw new HttpResponseException(response([
                    "errors" => [
                        "messages" => [
                            "username_has_created" =>  ["username with" . $data["username"] .  "have role" . $i]
                        ]
                    ]
                ], 404));
            }
        }

        if (in_array("supervisors", $data["roles"]) || in_array("examiners", $data["roles"]) || in_array("head-study-programs", $data["roles"]) || in_array("coordinators", $data["roles"])) {
            if (in_array("students", $data["roles"])) {
                throw new HttpResponseException(response([
                    "errors" => [
                        "messages" => [
                            "not_valid_role" => ["teachers cannot have the role of students and students can have the role of teachers"]
                        ]
                    ]
                ], 404));
            }
        }

        if (in_array("students", $data["roles"])) {
            if (in_array("supervisors", $data["roles"]) || in_array("examiners", $data["roles"]) || in_array("head-study-programs", $data["roles"]) || in_array("coordinators", $data["roles"])) {
                throw new HttpResponseException(response([
                    "errors" => [
                        "messages" => [
                            "not_valid_role" => ["teachers cannot have the role of students and students can have the role of teachers"]
                        ]
                    ]
                ], 404));
            }
        }
      
        foreach ($data["roles"] as $i) {
            if ($i === "students" && (empty($data["nrp"]) || empty($data["head_study_program_id"]))) {
                throw new HttpResponseException(response([
                    "errors" => [
                        "messages" => [
                            "not_valid_role" => ["user with role student must have nrp and head study program"]
                        ]
                    ]
                ], 404));
            }
        }

        foreach ($data["roles"] as $i) {
            if (($i === "supervisors" || $i === "examiners" || $i === "head-study-programs" || $i === "coordinators" ) && (isset($data["nrp"]) || isset($data["head_study_program_id"]))) {
                throw new HttpResponseException(response([
                    "errors" => [
                        "messages" => [
                            "not_valid_role" => ["user with role teacher must not have nrp and head study program"]
                        ]
                    ]
                ], 404));
            }
        }


        foreach ($data["roles"] as $i) {
            $user = new User();
            $user->username = $data["username"];
            $user->password = $data["password"];
            $user->role = $i;
            $user->save();

            if ($user->role = "students") {
                $student = new Student();
                $student->name = $data["name"];
                $student->address = $data["address"];
                $student->phone = $data["phone"];
                $student->nrp = $data["nrp"];
                $student->user_id = $user->id;
                $student->head_study_program_id = $data["head_study_program_id"];
                $student->save();
            }
            if($user->role = "supervisors") {
                $supervisor = new Supervisor();
                $supervisor->name = $data["name"];
                $supervisor->address = $data["address"];
                $supervisor->phone = $data["phone"];
                $supervisor->user_id = $user->id;
                $supervisor->save();
            }
            if($user->role = "examiners") {
                $examiner = new Examiner();
                $examiner->name = $data["name"];
                $examiner->address = $data["address"];
                $examiner->phone = $data["phone"];
                $examiner->user_id = $user->id;
                $examiner->save();
            }
            if($user->role = "coordinators") {
                $coordinator = new Coordinator();
                $coordinator->name = $data["name"];
                $coordinator->address = $data["address"];
                $coordinator->phone = $data["phone"];
                $coordinator->user_id = $user->id;
                $coordinator->save();
            }
            if($user->role = "head-study-programs") {
                $headStudyProgram = new HeadStudyProgram();
                $headStudyProgram->name = $data["name"];
                $headStudyProgram->address = $data["address"];
                $headStudyProgram->phone = $data["phone"];
                $headStudyProgram->user_id = $user->id;
                $headStudyProgram->save();
            }
        }

        return response()->json("OK")->setStatusCode(201);
    }
    
    // public function register(UserRegisterRequest $request): JsonResponse 
    // {
 
        
    //     $data = $request->validated();

    //     $this->checkUsernameIsAvailable($data["username"]);

    //     $user = new User($data);
    //     $user->password = Hash::make($data["password"]);
    //     $user->save();

    //     return (new UserResponse($user))->response()->setStatusCode(201); 
    // }



    // public function createStudentUser(UserStudentCreateRequest $request): JsonResponse 
    // {
    //     $data = $request->validated();

    //     $this->checkUsernameIsAvailable($data["username"]);

    //     if (!HeadStudyProgram::where("id", $data["head_study_program_id"])->first()) {
    //         throw new HttpResponseException(response([
    //             "errors" => [
    //                 "messages" => [
    //                     "head_study_program_data_not_found" => ["head study program data not found"]
    //                 ]
    //             ]
    //         ], 404));
    //     }

    //     $user = new User();
    //     $user->username = $data["username"];
    //     $user->password = Hash::make($data["password"]);
    //     $user->role = "students";
    //     $user->save();
        
    //     $student = new Student();
    //     $student->name = $data["name"];
    //     $student->address = $data["address"];
    //     $student->phone = $data["phone"];
    //     $student->nrp = $data["nrp"];
    //     $student->user_id = $user->id;
    //     $student->head_study_program_id = $data["head_study_program_id"];
    //     $student->save();


    //     if (!$user || !$student) {

    //         if($user) User::where("id", $user->id)->delete();
    //         if($student) Student::where("id", $student->id)->delete();

    //         throw new HttpResponseException(response([
    //             "errors" => [
    //                 "messages" => [
    //                     "create_user_data_failed" => ["create data failed"]
    //                 ]
    //             ]
    //         ], 404));
    //     }

        
    //     return (new UserResponse($user))->response()->setStatusCode(201); 
        
    // }
    // public function createSupervisorUser(UserSupervisorCreateRequest $request): JsonResponse
    // {
    //     $data = $request->validated();


    //     $this->checkUsernameIsAvailable($data["username"]);

    //     $user = new User();
    //     $user->username = $data["username"];
    //     $user->password = Hash::make($data["password"]);
    //     $user->role = "supervisors";
    //     $user->save();

    //     $supervisor = new Supervisor();
    //     $supervisor->name = $data["name"];
    //     $supervisor->address = $data["address"];
    //     $supervisor->phone = $data["phone"];
    //     $supervisor->user_id = $user->id;
    //     $supervisor->save();


    //     if (!$user || !$supervisor) {

    //         if($user) User::where("id", $user->id)->delete();
    //         if($supervisor) Supervisor::where("id", $supervisor->id)->delete();

    //         throw new HttpResponseException(response([
    //             "errors" => [
    //                 "messages" => [
    //                     "create_user_data_failed" => ["create data failed"]
    //                 ]
    //             ]
    //         ], 404));
    //     }        
    //     return (new UserResponse($user))->response()->setStatusCode(201); 
    // }
    // public function createHeadStudyProgramUser(UserHeadStudyProgramCreateRequest $request): JsonResponse
    // {
    //     $data = $request->validated();

    //     $this->checkUsernameIsAvailable($data["username"]);


    //     $user = new User();
    //     $user->username = $data["username"];
    //     $user->password = Hash::make($data["password"]);
    //     $user->role = "head-study-programs";
    //     $user->save();

    //     $headStudyProgram = new HeadStudyProgram();
    //     $headStudyProgram->name = $data["name"];
    //     $headStudyProgram->address = $data["address"];
    //     $headStudyProgram->phone = $data["phone"];
    //     $headStudyProgram->user_id = $user->id;
    //     $headStudyProgram->save();

    //     if (!$user || !$headStudyProgram) {

    //         if($user) User::where("id", $user->id)->delete();
    //         if($headStudyProgram) HeadStudyProgram::where("id", $headStudyProgram->id)->delete();

    //         throw new HttpResponseException(response([
    //             "errors" => [
    //                 "messages" => [
    //                     "create_user_data_failed" => ["create data failed"]
    //                 ]
    //             ]
    //         ], 404));
    //     }        
    //     return (new UserResponse($user))->response()->setStatusCode(201); 

    // }

    // public function createExaminerUser(UserExaminerCreateRequest $request): JsonResponse
    // {
    //     $data = $request->validated();

    //     $this->checkUsernameIsAvailable($data["username"]);

    //     $user = new User();
    //     $user->username = $data["username"];
    //     $user->password = Hash::make($data["password"]);
    //     $user->role = "examiners";
    //     $user->save();

    //     $examiner = new Examiner();
    //     $examiner->name = $data["name"];
    //     $examiner->address = $data["address"];
    //     $examiner->phone = $data["phone"];
    //     $examiner->user_id = $user->id;
    //     $examiner->save();

    //     if (!$user || !$examiner) {

    //         if($user) User::where("id", $user->id)->delete();
    //         if($examiner) Examiner::where("id", $examiner->id)->delete();

    //         throw new HttpResponseException(response([
    //             "errors" => [
    //                 "messages" => [
    //                     "create_user_data_failed" => ["create data failed"]
    //                 ]
    //             ]
    //         ], 404));
    //     }        
    //     return (new UserResponse($user))->response()->setStatusCode(201); 

    // }

    // public function createCoordinatorUser(UserCoordinatorCreateRequest $request): JsonResponse
    // {
    //     $data = $request->validated();

    //     $this->checkUsernameIsAvailable($data["username"]);

    //     $user = new User();
    //     $user->username = $data["username"];
    //     $user->password = Hash::make($data["password"]);
    //     $user->role = "coordinators";
    //     $user->save();

    //     $coordinator = new Coordinator();
    //     $coordinator->name = $data["name"];
    //     $coordinator->address = $data["address"];
    //     $coordinator->phone = $data["phone"];
    //     $coordinator->user_id = $user->id;
    //     $coordinator->save();

      
    //     if (!$user || !$coordinator) {

    //         if($user) User::where("id", $user->id)->delete();
    //         if($coordinator) Coordinator::where("id", $coordinator->id)->delete();

    //         throw new HttpResponseException(response([
    //             "errors" => [
    //                 "messages" => [
    //                     "create_user_data_failed" => ["create data failed"]
    //                 ]
    //             ]
    //         ], 404));
    //     }        
    //     return (new UserResponse($user))->response()->setStatusCode(201); 

    // }
    // public function getUsersByRole($role) 
    // {
    //     $users = User::with('student')->with('examiner')->with('supervisor')->with('headStudyProgram')->with('coordinator')->where('role', $role)->get();
    //     return $users;
    // }
}
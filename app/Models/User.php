<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class User extends Model implements Authenticatable
{
    use SoftDeletes;
    protected $table = "users";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        "username",
        "password",
        "role"
    ];



 


    //override value Auth from Authenticatable
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function getAuthIdentifier()
    {
        return $this->username;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->token;
    }

    public function setRememberToken($value)
    {
        $this->token = $value;
    }

    public function getRememberTokenName()
    {
        return 'token';
    }

    //relationship setting

    
    //a user just be a student
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, "user_id", "id");
    }
    //a user just be a examiner
    public function examiner(): HasOne
    {
        return $this->hasOne(Examiner::class, "user_id", "id");
    }
    //a user just only be a supervisor
    public function supervisor(): HasOne
    {
        return $this->hasOne(Supervisor::class, "user_id", "id");
    }
    //a user just be a head study program
    public function headStudyProgram(): HasOne
    {
        return $this->hasOne(HeadStudyProgram::class, "user_id", "id");
    }
    //a user just be a coordinator
    public function coordinator(): HasOne
    {
        return $this->hasOne(Coordinator::class, "user_id", "id");
    }
    //a user just be a academic administration
    public function academicAdministration(): HasOne
    {
        return $this->hasOne(AcademicAdministration::class, "user_id", "id");
    }



}
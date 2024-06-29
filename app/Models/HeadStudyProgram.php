<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeadStudyProgram extends Model
{
    use SoftDeletes;
    protected $table = "head_study_programs";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        "name",
        "address",
        "phone"
    ];




    //relationship setting
    
    //a head study program just one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

     //one head study program can have many student
    public function students(): HasMany {
        return $this->hasMany(Student::class, "head_study_program_id", "id");
    }
}
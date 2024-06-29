<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supervisor extends Model
{
    use SoftDeletes;
    protected $table = "supervisors";
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

    //a supervisor just one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, "students_supervisors")->withTimestamps();
    }
}
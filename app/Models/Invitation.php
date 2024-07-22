<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use SoftDeletes;
    protected $table = "invitations";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
   
        "implementation_hour",
        "implementation_date",
   
    ];





    public function students(): HasMany {
        return $this->hasMany(Student::class, "invitation_id", "id");
    }



    
    
    public function coordinator(): BelongsTo {
        return $this->belongsTo(Coordinator::class, "coordinator_id", "id");
    }
}
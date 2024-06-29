<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use SoftDeletes;
    protected $table = "proposals";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        "title",
        "upload_date",
        "semester"
    ];



        
    public function proposalPdf(): HasOne
    {
        return $this->hasOne(ProposalPdf::class, "proposal_id", "id");
    }

    // one to one

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, "student_id", "id");
    }


    public function revisions(): HasMany {
        return $this->hasMany(Revision::class, "proposal_id", "id");
    }

    
    
 
}
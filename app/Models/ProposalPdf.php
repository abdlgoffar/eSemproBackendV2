<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposalPdf extends Model
{
    use SoftDeletes;
    protected $table = "proposal_pdfs";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;
  

    protected $fillable = [
        "saved_name",
        "original_name",
        "path"
    ];

     
    //relationship setting

    //a pdf file just to one proposal
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, "proposal_id", "id");
    }
}
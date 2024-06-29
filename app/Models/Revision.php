<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revision extends Model
{
   

    use SoftDeletes;
    protected $table = "revisions";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;





    public function proposal(): BelongsTo {
        return $this->belongsTo(Proposal::class, "proposal_id", "id");
    }
}
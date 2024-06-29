<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvitationPdf extends Model
{
    use SoftDeletes;
    protected $table = "invitation_pdfs";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;
  

    protected $fillable = [
        "saved_name",
        "original_name",
        "path"
    ];
}
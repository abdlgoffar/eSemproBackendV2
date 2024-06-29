<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    
    public function getAll(): JsonResponse 
    {
        $rooms = Room::paginate();
        return response()->json($rooms, 201, ["Content-Type" => "application/json"]);
    }
}
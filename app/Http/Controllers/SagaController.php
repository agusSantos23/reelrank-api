<?php

namespace App\Http\Controllers;

use App\Models\Saga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SagaController extends Controller
{
    public function index(): JsonResponse {
        $sagas = Saga::with('avatars')->get(); 

        return response()->json($sagas);
    }
}

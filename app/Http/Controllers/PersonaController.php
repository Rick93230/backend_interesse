<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $pagina = $request->input('page', 1);

        $result = DB::selectOne("CALL sp_process_personas_pagination(?)", [$pagina]);

        $payload = json_decode($result->resultado, true);

        return response()->json($payload);
    }
}

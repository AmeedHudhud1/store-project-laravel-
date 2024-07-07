<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PredictController extends Controller
{
    public function predict(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'unit' => 'required|string',
            'count' => 'required|integer',
        ]);

        $start_date = $request->input('start_date');
        $unit = $request->input('unit');
        $count = $request->input('count');

        $pythonPath = 'python';

        $scriptPath = base_path('/prediction/main.py');

        $command = escapeshellcmd("$pythonPath $scriptPath $start_date $unit $count");

        $output = shell_exec($command);

        $data = json_decode($output, true);

        // Return the decoded JSON data
        return response()->json($data);
    }
}

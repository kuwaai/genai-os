<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;

class KernelController extends Controller
{
    public function fetchData()
    {
        $apiUrl = SystemSetting::where('key', 'agent_location')->first()->value . '/v1.0/worker/read';
        $response = Http::get($apiUrl);
        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data);
        } else {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }

    public function updateField(Request $request)
    {
        $apiUrl = SystemSetting::where('key', 'agent_location')->first()->value . '/v1.0/worker/update';
        $data = [
            'access_code' => $request->input('access_code'),
            'url' => $request->input('url'),
            $request->input('field') => $request->input('value'),
        ];

        $response = Http::post($apiUrl, $data);

        return $response->successful() ? response()->json(['status' => 'success']) : response()->json(['error' => 'Failed to update data'], 500);
    }

    public function updateData(Request $request)
    {
        $apiUrl = SystemSetting::where('key', 'agent_location')->first()->value . '/v1.0/worker/update';
        $response = Http::post($apiUrl, $request->all());

        return $response->successful() ? response()->json(['status' => 'success']) : response()->json(['error' => 'Failed to update data'], 500);
    }

    public function deleteData(Request $request)
    {
        $apiUrl = SystemSetting::where('key', 'agent_location')->first()->value . '/v1.0/worker/delete';
        $response = Http::post($apiUrl, $request->all());

        return $response->successful() ? response()->json(['status' => 'success']) : response()->json(['error' => 'Failed to delete data'], 500);
    }

    public function shutdown(Request $request)
    {
        $apiUrl = SystemSetting::where('key', 'agent_location')->first()->value . '/v1.0/worker/shutdown';
        $response = Http::post($apiUrl, ['url' => $request->url]);

        return $response->successful() ? response()->json(['status' => 'success']) : response()->json(['error' => 'Failed to shutdown address'], 500);
    }

    // New method to create data
    public function createData(Request $request)
    {
        $apiUrl = SystemSetting::where('key', 'agent_location')->first()->value . '/v1.0/worker/create'; // Ensure this endpoint supports creating
        $data = [
            'access_code' => $request->input('access_code'),
            'url' => $request->input('url'),
            'status' => $request->input('status'),
            'history_id' => $request->input('history_id'),
            'user_id' => $request->input('user_id'),
        ];

        $response = Http::post($apiUrl, $data);

        return $response->successful() ? response()->json(['status' => 'success']) : response()->json(['error' => 'Failed to create data'], 500);
    }
}

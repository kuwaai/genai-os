<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Models\SystemSetting;
use App\Models\User;
use DB;

class CloudController extends Controller
{
    function getFileCategory($extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return 'image';
            case 'pdf':
                return 'pdf';
            case 'doc':
            case 'docx':
                return 'word';
            case 'xls':
            case 'xlsx':
                return 'excel';
            case 'ppt':
            case 'pptx':
                return 'powerpoint';
            case 'zip':
            case 'rar':
                return 'archive';
            case '/':
                return 'folder';
            default:
                return 'file';
        }
    }

    public function home(Request $request)
    {
        return view('cloud');
    }

    public function api_read_cloud(Request $request, $user_id = null, $paths = null)
    {
        $result = DB::table('personal_access_tokens')
            ->join('users', 'tokenable_id', '=', 'users.id')
            ->select('tokenable_id', 'users.id', 'users.name')
            ->where('token', str_replace('Bearer ', '', $request->header('Authorization')))
            ->first();
        if ($result) {
            $user = $result;
            if (User::find($user->id)->hasPerm('tab_Cloud')) {
                $authUserId = auth()->id();
                if (!$request->user()->hasPerm('tab_Manage')) {
                    if ($paths !== null || $user_id === null || intval($user_id) !== $authUserId) {
                        $paths = null;
                        $user_id = $authUserId;
                    }
                }
                $base_dir = 'homes';
                $user_dir = $base_dir . '/' . $authUserId;
                $query_path = '/';
                if ($user_id) {
                    $query_path .= $user_id . '/';
                }
                if ($paths) {
                    $query_path .= $paths . '/';
                }
                $files = Storage::disk('public')->Files($base_dir . $query_path);
                $directories = array_map(fn($dir) => rtrim($dir, '/') . '/', Storage::disk('public')->directories($base_dir . $query_path));
                $items = array_merge($directories, $files);

                $explorer = [];
                foreach ($items as $item) {
                    $isDirectory = str_ends_with($item, '/');
                    $extension = $isDirectory ? '/' : pathinfo($item, PATHINFO_EXTENSION);
                    $explorer[] = [
                        'name' => basename($item),
                        'is_directory' => $isDirectory,
                        'icon' => $this->getFileCategory($extension),
                    ];
                }

                $user_dir_usedSpace = collect(Storage::disk('public')->allFiles($user_dir))->sum(fn($file) => Storage::disk('public')->size($file));
                return response()->json(['status' => 'success', 'result' => compact('query_path', 'explorer', 'user_dir_usedSpace')], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                $errorResponse = [
                    'status' => 'error',
                    'message' => 'You have no permission to use this Kuwa API',
                ];

                return response()->json($errorResponse, 401, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            $errorResponse = [
                'status' => 'error',
                'message' => 'Authentication failed',
            ];

            return response()->json($errorResponse, 401, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function upload(Request $request)
    {
        return view('cloud');
    }
    public function api_delete_cloud(Request $request, $user_id = null, $folder = null)
    {
        $result = DB::table('personal_access_tokens')
            ->join('users', 'tokenable_id', '=', 'users.id')
            ->select('tokenable_id', 'users.id', 'users.name')
            ->where('token', str_replace('Bearer ', '', $request->header('Authorization')))
            ->first();

        if ($result) {
            $user = $result;

            if (User::find($user->id)->hasPerm('tab_Cloud')) {
                $authUserId = auth()->id();

                if (!$request->user()->hasPerm('tab_Manage') && intval($user_id) !== $authUserId) {
                    return response()->json(
                        [
                            'status' => 'error',
                            'message' => 'Permission not enough to delete this item.',
                        ],
                        403,
                        [],
                        JSON_UNESCAPED_UNICODE,
                    );
                }
                $pathToDelete = 'homes/' . $user_id . '/' . ($folder ?? '');
                if (Storage::disk('public')->exists($pathToDelete)) {
                    $isDirectory = !empty(Storage::disk('public')->directories(dirname($pathToDelete)));

                    if ($isDirectory) {
                        Storage::disk('public')->deleteDirectory($pathToDelete);
                    } else {
                        Storage::disk('public')->delete($pathToDelete);
                    }

                    return response()->json(
                        [
                            'status' => 'success',
                            'message' => 'File or folder deleted successfully.',
                        ],
                        200,
                        [],
                        JSON_UNESCAPED_UNICODE,
                    );
                } else {
                    return response()->json(
                        [
                            'status' => 'error',
                            'message' => 'File or folder not found.',
                        ],
                        404,
                        [],
                        JSON_UNESCAPED_UNICODE,
                    );
                }
            } else {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'You have no permission to use this Kuwa API',
                    ],
                    401,
                    [],
                    JSON_UNESCAPED_UNICODE,
                );
            }
        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Authentication failed',
                ],
                401,
                [],
                JSON_UNESCAPED_UNICODE,
            );
        }
    }

    public function rename(Request $request)
    {
        return view('cloud');
    }
}

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
    function resolvePath($path)
    {
        return array_values(array_filter(explode('/', trim($path, '/'))));
    }
    function pathToString($pathArray)
    {
        return '/' . implode('/', $pathArray);
    }
    public function api_read_cloud(Request $request, $paths = null)
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
                $path = $this->resolvePath($paths);
                $user_dir = $this->resolvePath('/homes/' . $authUserId);
                if (!$request->user()->hasPerm('tab_Manage')) {
                    if (($path[0] ?? null) != 'homes' || ($path[1] ?? null) != $authUserId) {
                        $path = $user_dir;
                    }
                }

                $query_path = $this->pathToString($path) . '/';
                $files = Storage::disk('public')->Files('root' . $query_path);
                $directories = array_map(fn($dir) => rtrim($dir, '/') . '/', Storage::disk('public')->directories('root' . $query_path));
                $items = array_merge($directories, $files);

                $explorer = [];
                foreach ($items as $item) {
                    $path = Storage::disk('public')->path($item);
                    $resolvedPath = readlink($path);

                    $isSymbolicLink = $resolvedPath && $resolvedPath !== $path;

                    $isDirectory = str_ends_with($item, '/') || ($isSymbolicLink && is_dir($resolvedPath)) || is_dir($path);

                    $extension = $isDirectory ? '/' : pathinfo($item, PATHINFO_EXTENSION);

                    $explorer[] = [
                        'name' => basename($item),
                        'is_directory' => $isDirectory,
                        'icon' => $this->getFileCategory($extension),
                    ];
                }

                return response()->json(['status' => 'success', 'result' => compact('query_path', 'explorer')], 200, [], JSON_UNESCAPED_UNICODE);
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
    public function api_delete_cloud(Request $request, $paths = null)
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
                $path = $this->resolvePath($paths);
                $user_dir = $this->resolvePath('/homes/' . $authUserId);
                if (!$request->user()->hasPerm('tab_Manage') && ((($path[0] ?? null) != 'homes') || (($path[1] ?? null) != $authUserId))) {
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
                $pathToDelete = '/root' . $this->pathToString($path) . '/';
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

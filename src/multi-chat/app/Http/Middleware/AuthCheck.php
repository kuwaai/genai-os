<?php

namespace App\Http\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Jobs\CheckUpdate;
use Closure;

class AuthCheck
{
    /**
     * Redirect user if they're required to change password
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->tokens()->where('name', 'API_Token')->count() != 1) {
            $request->user()->tokens()->where('name', 'API_Token')->delete();
            $request->user()->createToken('API_Token', ['access_api']);
        }
        $user_dir = 'root/homes' . '/' . auth()->id();
        if (!Storage::disk('public')->exists($user_dir)) {
            Storage::disk('public')->makeDirectory($user_dir);
        }

        if ($request->user()) {
            if ($request->user()->require_change_password) {
                return redirect()->route('change_password');
            }

            if ($request->user()->hasPerm('tab_Manage')) {
                $jobs = Redis::lrange('queues:default', 0, -1);
                $runningJobs = array_map(fn($job) => json_decode($job)->displayName, $jobs);
            
                foreach ($jobs as $job) {
                    $jobData = json_decode($job);
                    if ($jobData->displayName === 'App\Jobs\CheckUpdate') {
                        Redis::lrem('queues:default', 0, $job);
                    }
                }
                if (!in_array('App\Jobs\CheckUpdate', $runningJobs)) {
                    Redis::throttle('check_update')->block(0)->allow(1)->every(300)->then(
                        fn() => CheckUpdate::dispatch(),
                        fn() => null
                    );
                }
            }
        }

        return $next($request);
    }
}

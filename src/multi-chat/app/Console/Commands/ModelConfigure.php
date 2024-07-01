<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LLMs;
use App\Models\Bots;
use App\Models\Permissions;
use App\Models\GroupPermissions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use DB;

class ModelConfigure extends Command
{
    protected $signature = 'model:config {access_code} {name} {--image=} {--do_not_create_bot} {--force}';
    protected $description = 'Quickly configure a model for admins';
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $accessCode = $this->argument('access_code');
        $name = $this->argument('name');
        $force = !!$this->option('do_not_create_bot');

        try {
            if (!$force && LLMs::where('access_code', '=', $accessCode)->exists()) {
                $this->error('The access code already exists! Aborted.');
            } elseif (!$force && LLMs::where('name', '=', $name)->exists()) {
                $this->error('The name already exists! Aborted.');
            } else {
                DB::beginTransaction(); // Start a database transaction
                $path = null;
                if ($this->option('image')) {
                    $imagePath = $this->option('image');
                    $fileContents = file_get_contents($imagePath);
                    $imageName = Str::random(40) . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);
                    $path = 'public/images/' . $imageName;
                    Storage::put($path, $fileContents);
                }
                
                $model = LLMs::where('access_code', '=', $accessCode);
                if (!$model->exists()){
                    $model = new LLMs();
                }
                $model->fill(['name' => $name, 'access_code' => $accessCode, 'image' => $path]);
                $model->save();
                $perm = new Permissions();
                $perm->fill(['name' => 'model_' . $model->id]);
                $perm->save();
                $currentTimestamp = now();

                $targetPermID = Permissions::where('name', '=', 'tab_Manage')->first()->id;

                $groups = GroupPermissions::pluck('group_id')->toArray();

                foreach ($groups as $group) {
                    GroupPermissions::where('group_id', $group)
                        ->where('perm_id', '=', $perm->id)
                        ->delete();
                    if (GroupPermissions::where('group_id', $group)->where('perm_id', '=', $targetPermID)->exists()) {
                        GroupPermissions::insert([
                            'group_id' => $group,
                            'perm_id' => $perm->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp,
                        ]);
                    }
                }

                if (!$this->option('do_not_create_bot')) {
                    $bot = new Bots();
                    $bot->fill(['name' => $model->name, 'type' => 'prompt', 'visibility' => 0, 'model_id' => $model->id]);
                    $bot->save();
                }
                DB::commit();
                $this->info('Model ' . $name . ' with access_code ' . $accessCode . ' configured successfully!');
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e; // Re-throw the exception to halt the migration
        }
    }
}

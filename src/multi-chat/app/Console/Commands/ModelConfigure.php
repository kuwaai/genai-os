<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LLMs;
use App\Models\Permissions;
use App\Models\GroupPermissions;
use DB;

class ModelConfigure extends Command
{
    protected $signature = 'model:config {access_code} {name}';
    protected $description = 'Quickly configure a model for everyone';
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $accessCode = $this->argument('access_code');
        $name = $this->argument('name');

        try {
            if (LLMs::where('access_code', '=', $accessCode)->exists()) {
                $this->error('The access code already exists! Aborted.');
            } elseif (LLMs::where('name', '=', $name)->exists()) {
                $this->error('The name already exists! Aborted.');
            } else {
                DB::beginTransaction(); // Start a database transaction
                $model = new LLMs();
                $model->fill(['name' => $name, 'access_code' => $accessCode]);
                $model->save();
                $perm = new Permissions();
                $perm->fill(['name' => 'model_' . $model->id, 'describe' => 'Permission for model id ' . $model->id]);
                $perm->save();
                $currentTimestamp = now();

                $groups = GroupPermissions::pluck('group_id')->toArray();

                foreach ($groups as $group) {
                    GroupPermissions::where('group_id', $group)
                        ->where('perm_id', '=', $perm->id)
                        ->delete();
                    GroupPermissions::insert([
                        'group_id' => $group,
                        'perm_id' => $perm->id,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ]);
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

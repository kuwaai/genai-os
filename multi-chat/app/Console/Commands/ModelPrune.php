<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LLMs;
use App\Models\Permissions;
use Illuminate\Support\Facades\Storage;
use DB;

class ModelPrune extends Command
{
    protected $signature = 'model:prune {--force : Automatically confirm deletion}';
    protected $description = 'Quickly cleanup all models';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $models = LLMs::get(); // Get all models

        if ($models->isEmpty()) {
            $this->info('No models found for deletion.');
            return;
        }

        $this->info('The following models will be deleted:');
        foreach ($models as $model) {
            $this->info('- ID: ' . $model->id . ', name: ' . $model->name . ', access_code: ' . $model->access_code . ', description: ' . $model->description);
        }
        if ($this->option('force')) {
            $this->info('Automatically confirming deletion.');
        } elseif (!$this->confirm('Do you wish to continue?')) {
            $this->info('Operation cancelled. No models have been deleted.');
            return;
        }
        try {
            DB::beginTransaction(); // Start a database transaction

            foreach ($models as $model) {
                Storage::delete($model->image);
                Permissions::where('name', '=', 'model_' . $model->id)->delete();
                $model->delete();
            }

            DB::commit();
            $this->info('All models have been deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            $this->error('An error occurred while deleting models. Transaction rolled back.');
            $this->error($e->getMessage());
        }
    }
}

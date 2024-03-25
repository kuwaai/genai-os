<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Logs;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $logData = [
            'message' => 'New user has been created',
            'user_id' => $user->id,
        ];

        // Remove unwanted attributes
        $attributesToIgnore = ['updated_at', 'openai_token', 'password', 'id', 'created_at'];
        $attributes = array_diff_key($user->getAttributes(), array_flip($attributesToIgnore));

        if (!empty($attributes)) {
            $logData['new_attributes'] = $attributes;
        }

        $log = new Logs();
        $log->fill([
            'action' => 'create_user',
            'description' => json_encode($logData),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
        $log->save();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $originalValues = $user->getOriginal();
        $changes = $user->getChanges();

        // Remove 'updated_at' column from changes if present
        unset($changes['updated_at']);

        $modifiedValues = [];
        $passwordChanged = false;
        $openaiTokenChanged = false;

        foreach ($changes as $attribute => $change) {
            if ($attribute === 'password') {
                $passwordChanged = true;
                continue; // Skip logging the actual password change
            } elseif ($attribute === 'openai_token') {
                $openaiTokenChanged = true;
                continue; // Skip logging the actual openai_token change
            }

            // Check if the attribute exists in original values before accessing it
            $beforeValue = isset($originalValues[$attribute]) ? $originalValues[$attribute] : null;

            $modifiedValues[$attribute] = [
                'before' => $beforeValue,
                'after' => $change,
            ];
        }

        $logData = [];

        if ($passwordChanged) {
            $logData['message'] = 'Password has been changed';
        } elseif ($openaiTokenChanged) {
            $logData['message'] = 'OpenAI token has been changed';
        } elseif (!empty($modifiedValues)) {
            $logData['modified_values'] = $modifiedValues;
        }

        // Log the user_id along with the changes
        $logData['user_id'] = $user->id;

        if (!empty($logData)) {
            $log = new Logs();
            $log->fill([
                'action' => 'update_user',
                'description' => json_encode($logData),
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
            ]);
            $log->save();
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $logData = [
            'message' => 'User has been deleted',
            'user_id' => $user->id,
        ];

        $log = new Logs();
        $log->fill([
            'action' => 'delete_user',
            'description' => json_encode($logData),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
        $log->save();
    }
    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $logData = [
            'message' => 'User has been force deleted',
            'user_id' => $user->id,
        ];

        $log = new Logs();
        $log->fill([
            'action' => 'force_delete_user',
            'description' => json_encode($logData),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
        $log->save();
    }
}

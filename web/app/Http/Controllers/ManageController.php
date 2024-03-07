<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\LLMUpdateRequest;
use App\Http\Requests\LLMCreateRequest;
use App\Models\Groups;
use App\Models\User;
use App\Models\GroupPermissions;
use App\Models\LLMs;
use App\Models\Logs;
use App\Models\Permissions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ManageController extends Controller
{
    public function group_create(Request $request): RedirectResponse
    {
        if ($request->input('name')) {
            $group = new Groups();
            $group->fill(['name' => $request->input('name'), 'describe' => $request->input('describe'), 'invite_token' => $request->input('invite_code')]);
            $group->save();
            if ($request->input('permissions')) {
                $currentTimestamp = now();
                $perm_records = [];

                foreach ($request->input('permissions') as $perm_id) {
                    $perm_records[] = [
                        'group_id' => $group->id,
                        'perm_id' => $perm_id,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ];
                }
                GroupPermissions::insert($perm_records);
            }
            $log = new Logs();
            $log->fill([
                'action' => 'create_group',
                'description' => "Created group {$group->id} with name {$group->name}" . ($group->describe ? " and described {$group->describe}" : '') . ($group->invite_token ? " and invite_token {$group->invite_token}" : '') . "\npermIDs: " . implode(' ', $request->input('permissions') ?? []),
                'user_id' => $request->user()->id,
                'ip_address' => $request->ip(),
            ]);
            $log->save();
            return Redirect::route('manage.home')
                ->with('last_tab', 'groups')
                ->with('last_group', $group->id)
                ->with('last_action', 'create')
                ->with('status', 'success');
        }
    }

    public function group_update(Request $request): RedirectResponse
    {
        $id = $request->input('id');
        if ($id) {
            $group = Groups::find($id);
            $name = $request->input('name');
            $describe = $request->input('describe');
            $group->fill(['name' => $name, 'describe' => $describe, 'invite_token' => $request->input('invite_code')]);
            $group->save();
            $permissions = $request->input('permissions');
            GroupPermissions::where('group_id', '=', $group->id)->delete();
            if ($permissions) {
                $currentTimestamp = now();
                $perm_records = [];

                foreach ($permissions as $perm_id) {
                    $perm_records[] = [
                        'group_id' => $group->id,
                        'perm_id' => $perm_id,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ];
                }
                GroupPermissions::insert($perm_records);
            }
            $log = new Logs();
            $log->fill([
                'action' => 'update_group',
                'description' => "Updated group {$group->id} by name {$group->name}" . ($group->describe ? " and described {$group->describe}" : '') . ($group->invite_token ? " and invite_token {$group->invite_token}" : '') . "\npermIDs: " . implode(' ', $request->input('permissions') ?? []),
                'user_id' => $request->user()->id,
                'ip_address' => $request->ip(),
            ]);
            $log->save();
            return Redirect::route('manage.home')->with('last_tab', 'groups')->with('last_group', $id)->with('last_action', 'update')->with('status', 'success');
        }
        return Redirect::route('manage.home')->with('last_tab', 'groups')->with('last_group', $id);
    }

    public function group_delete(Request $request): RedirectResponse
    {
        $id = $request->input('id');
        if ($id) {
            $group = Groups::find($id);
            User::where('group_id', '=', $id)->update(['group_id' => null]);
            $group->delete();
            $log = new Logs();
            $log->fill([
                'action' => 'delete_group',
                'description' => 'Deleted group ' . $group->id,
                'user_id' => $request->user()->id,
                'ip_address' => $request->ip(),
            ]);
            $log->save();
            return Redirect::route('manage.home')->with('last_tab', 'groups')->with('last_group', null)->with('last_action', 'delete')->with('status', 'success');
        }
        return Redirect::route('manage.home')->with('last_tab', 'groups')->with('last_group', null);
    }

    public function user_update(Request $request): RedirectResponse
    {
        $user = User::find($request->input('id'));
        if ($request->input('group')) {
            $group_id = Groups::where('name', '=', $request->input('group'))->first()->id;
        } else {
            $group_id = null;
        }
        $user->fill(['name' => $request->input('name'), 'email' => $request->input('email'), 'group_id' => $group_id, 'detail' => $request->input('detail')]);
        if ($request->input('password')) {
            $user->fill(['password' => Hash::make($request->input('password'))]);
        }
        $user->save();
        return Redirect::route('manage.home')
            ->with('last_tab', 'users')
            ->with('last_tool', 'group_selector')
            ->with('list_group', $group_id == null ? -1 : $group_id)
            ->with('edit_user', $request->input('id'));
    }

    public function tab(Request $request): RedirectResponse
    {
        return Redirect::route('manage.home')->with('last_tab', $request->input('last_tab'))->with('last_tool', $request->input('last_tool'))->with('list_group', $request->input('list_group'));
    }

    public function user_create(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'group' => 'nullable|string',
            'detail' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return Redirect::route('manage.home')->with('last_tab', 'users');
        }
        $user = new User();
        if ($request->input('group')) {
            $group_id = Groups::where('name', '=', $request->input('group'))->first()->id;
        } else {
            $group_id = null;
        }
        $user->fill(['name' => $request->input('name'), 'email' => $request->input('email'), 'group_id' => $group_id, 'detail' => $request->input('detail')]);
        if ($request->input('password')) {
            $user->fill(['password' => Hash::make($request->input('password'))]);
        }
        $user->save();
        $user->markEmailAsVerified();
        return Redirect::route('manage.home')
            ->with('last_tab', 'users')
            ->with('last_tool', 'group_selector')
            ->with('list_group', $group_id == null ? -1 : $group_id)
            ->with('edit_user', $user->id);
    }

    public function search_user(Request $request): RedirectResponse
    {
        return Redirect::route('manage.home')->with('last_tab', 'users')->with('last_tool', 'fuzzy_selector')->with('fuzzy_search', $request->input('search'));
    }

    public function user_delete(Request $request): RedirectResponse
    {
        $id = $request->input('id');
        if ($id) {
            $user = User::find($id);
            $group_id = $user->group_id;
            $user->delete();
            return Redirect::route('manage.home')
                ->with('last_tab', 'users')
                ->with('last_tool', 'group_selector')
                ->with('list_group', $group_id == null ? -1 : $group_id);
        }
        return Redirect::route('manage.home')->with('last_tab', 'users')->with('last_tool', 'group_selector');
    }

    public function llm_update(LLMUpdateRequest $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->input('id'));
        $validated = $request->validated();
        if ($file = $request->file('image')) {
            Storage::delete($model->image);
            $validated['image'] = $file->store('public/images');
        }
        if (is_null($validated['order'])) {
            unset($validated['order']);
        }
        if (is_null($validated['version'])) {
            unset($validated['version']);
        }
        $validated['config'] = [];
        if (isset($validated['system_prompt'])) {
            $validated['config']['startup_prompt'] = [['role' => 'system', 'message' => $validated['system_prompt']]];
            unset($validated['system_prompt']);
        }
        if (isset($validated['react_btn'])) {
            $validated['config']['react_btn'] = $validated['react_btn'];
            unset($validated['react_btn']);
        }
        $validated['config'] = json_encode($validated['config']);
        $model->fill($validated);
        $model->save();
        return Redirect::route('manage.home')->with('last_tab', 'llms')->with('last_llm_id', $request->input('id'));
    }

    public function llm_delete(Request $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->input('id'));
        Storage::delete($model->image);
        $model->delete();
        Permissions::where('name', '=', 'model_' . $request->input('id'))->delete();
        return Redirect::route('manage.home')->with('last_tab', 'llms')->with('last_llm_id', $request->input('id'));
    }

    public function llm_create(LLMCreateRequest $request): RedirectResponse
    {
        $model = new LLMs();
        $validated = $request->validated();
        if ($file = $request->file('image')) {
            $validated['image'] = $file->store('public/images');
        }
        if (is_null($validated['order'])) {
            unset($validated['order']);
        }
        if (is_null($validated['version'])) {
            unset($validated['version']);
        }
        $model->fill($validated);
        $model->save();
        $perm = new Permissions();
        $perm->fill(['name' => 'model_' . $model->id]);
        $perm->save();
        return Redirect::route('manage.home')
            ->with('last_tab', 'llms')
            ->with('last_llm_id', $model->id);
    }

    public function llm_toggle(Request $request): RedirectResponse
    {
        $model = LLMs::findOrFail($request->route('llm_id'));
        $model->enabled = !$model->enabled;
        $model->save();
        return Redirect::route('manage.home')->with('last_tab', 'llms')->with('last_llm_id', $request->route('llm_id'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\Groups;
use App\Models\User;
use App\Models\GroupPermissions;
use Illuminate\Support\Facades\Hash;

class ManageController extends Controller
{
    public function group_create(Request $request): RedirectResponse
    {
        if ($request->input('name')) {
            $group = new Groups();
            $group->fill(['name' => $request->input('name'), 'describe' => $request->input('describe'), 'invite_token'=>$request->input('invite_code')]);
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
            $group->fill(['name' => $name, 'describe' => $describe, 'invite_token'=>$request->input('invite_code')]);
            $group->save();
            $permissions = $request->input('permissions');
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
                GroupPermissions::where('group_id', '=', $group->id)->delete();
                GroupPermissions::insert($perm_records);
            }
        }
        return Redirect::route('manage.home')
            ->with('last_tab', 'groups')
            ->with('last_group', $id)
            ->with('last_action', 'update')
            ->with('status', 'success');
    }

    public function group_delete(Request $request): RedirectResponse
    {
        $id = $request->input('id');
        if ($id) {
            $group = Groups::find($id);
            $group->delete();
        }
        return Redirect::route('manage.home')
            ->with('last_tab', 'groups')
            ->with('last_group', null)
            ->with('last_action', 'delete')
            ->with('status', 'success');
    }

    public function user_update(Request $request): RedirectResponse
    {
        $user = User::find($request->input('id'));
        if ($request->input('group')) {
            $group_id = Groups::where('name', '=', $request->input('group'))->first()->id;
        } else {
            $group_id = null;
        }
        $user->fill(['name' => $request->input('name'), 'email' => $request->input('email'), 'group_id' => $group_id]);
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
        return Redirect::route('manage.home')
            ->with('last_tab', $request->input('last_tab'))
            ->with('last_tool', $request->input('last_tool'))
            ->with('list_group', $request->input('list_group'));
    }

    public function user_create(Request $request): RedirectResponse
    {
        $user = new User();
        if ($request->input('group')) {
            $group_id = Groups::where('name', '=', $request->input('group'))->first()->id;
        } else {
            $group_id = null;
        }
        $user->fill(['name' => $request->input('name'), 'email' => $request->input('email'), 'group_id' => $group_id]);
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
        return Redirect::route('manage.home')
            ->with('last_tab', 'users')
            ->with('last_tool', 'fuzzy_selector')
            ->with('fuzzy_search', $request->input("search"));
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
        return Redirect::route('manage.home')
            ->with('last_tab', 'users')
            ->with('last_tool', 'group_selector');
    }
}

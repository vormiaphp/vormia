<?php

namespace Vormia\Vormia\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vormia\Vormia\Models\Role;
use Vormia\Vormia\Support\Helpers;
use Vormia\Vormia\Traits\Model\ApiResponseTrait;

class UserRoleController extends Controller
{
    use ApiResponseTrait;

    public function assignRoles(Request $request, $userId)
    {
        try {
            $userClass = Helpers::userModel();
            $user = $userClass::find($userId);

            if (! $user) {
                return $this->notFound('User not found');
            }

            $validator = Validator::make($request->all(), [
                'role_ids' => 'required|array',
                'role_ids.*' => ['required'],
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            $roles = Role::whereIn('id', $request->role_ids)
                ->orWhereIn('slug', $request->role_ids)
                ->pluck('id')
                ->toArray();

            if (count($roles) !== count(array_unique($request->role_ids))) {
                return $this->notFound('One or more roles not found');
            }

            $user->roles()->sync($roles);

            return $this->success($user->load('roles'), 'Roles assigned to user successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to assign roles: ' . $e->getMessage(), 500);
        }
    }

    public function getUserRoles($userId)
    {
        try {
            $userClass = Helpers::userModel();
            $user = $userClass::with('roles')->find($userId);

            if (! $user) {
                return $this->notFound('User not found');
            }

            return $this->success($user->roles, 'User roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve user roles: ' . $e->getMessage(), 500);
        }
    }

    public function getUsersByRole($roleIdOrSlug)
    {
        try {
            $role = Role::where('id', $roleIdOrSlug)
                ->orWhere('slug', $roleIdOrSlug)
                ->first();

            if (! $role) {
                return $this->notFound('Role not found');
            }

            $users = $role->users()->get();

            return $this->success([
                'role' => $role,
                'users' => $users,
            ], 'Users by role retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve users by role: ' . $e->getMessage(), 500);
        }
    }

    public function removeRoles(Request $request, $userId)
    {
        try {
            $userClass = Helpers::userModel();
            $user = $userClass::find($userId);

            if (! $user) {
                return $this->notFound('User not found');
            }

            $validator = Validator::make($request->all(), [
                'role_ids' => 'required|array',
                'role_ids.*' => ['required'],
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            $roles = Role::whereIn('id', $request->role_ids)
                ->orWhereIn('slug', $request->role_ids)
                ->pluck('id')
                ->toArray();

            $user->roles()->detach($roles);

            return $this->success($user->load('roles'), 'Roles removed from user successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to remove roles: ' . $e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Vrm;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vrm\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\Vrm\Model\ApiResponseTrait;

class UserRoleController extends Controller
{
    use ApiResponseTrait;
    /**
     * Assign roles to a user.
     */
    public function assignRoles(Request $request, $userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return $this->notFound('User not found');
            }

            $validator = Validator::make($request->all(), [
                'role_ids' => 'required|array',
                'role_ids.*' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!is_numeric($value) && !is_string($value)) {
                            $fail('The ' . $attribute . ' must be an array of role IDs or slugs.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            // Find role IDs from both IDs and slugs
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

    /**
     * Get user roles.
     */
    public function getUserRoles($userId)
    {
        try {
            $user = User::with('roles')->find($userId);
            if (!$user) {
                return $this->notFound('User not found');
            }
            return $this->success($user->roles, 'User roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve user roles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole($roleIdOrSlug)
    {
        try {
            $role = Role::where('id', $roleIdOrSlug)
                ->orWhere('slug', $roleIdOrSlug)
                ->first();
            if (!$role) {
                return $this->notFound('Role not found');
            }
            $users = $role->users()->get();
            return $this->success([
                'role' => $role,
                'users' => $users
            ], 'Users by role retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve users by role: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove roles from a user.
     */
    public function removeRoles(Request $request, $userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return $this->notFound('User not found');
            }

            $validator = Validator::make($request->all(), [
                'role_ids' => 'required|array',
                'role_ids.*' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!is_numeric($value) && !is_string($value)) {
                            $fail('The ' . $attribute . ' must be an array of role IDs or slugs.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            // Find role IDs from both IDs and slugs
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

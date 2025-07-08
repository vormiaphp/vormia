<?php

namespace App\Http\Controllers\Vrm;

use App\Http\Controllers\Controller;
use App\Traits\Vrm\Model\ApiResponseTrait;
use App\Models\Vrm\Permission;
use App\Models\Vrm\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the permissions.
     */
    public function index()
    {
        try {
            $permissions = Permission::all();
            return $this->success($permissions, 'Permissions retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve permissions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:' . config('vormia.table_prefix') . 'permissions,name',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            $permission = Permission::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return $this->success($permission, 'Permission created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create permission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified permission.
     */
    public function show($id)
    {
        try {
            $permission = Permission::find($id);
            if (!$permission) {
                return $this->notFound('Permission not found');
            }
            return $this->success($permission->load('roles'), 'Permission retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve permission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $permission = Permission::find($id);
            if (!$permission) {
                return $this->notFound('Permission not found');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255|unique:' . config('vormia.table_prefix') . 'permissions,name,' . $permission->id,
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            $permission->update($request->only(['name', 'description', 'is_active']));

            return $this->success($permission, 'Permission updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update permission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::find($id);
            if (!$permission) {
                return $this->notFound('Permission not found');
            }

            // Detach from roles before deleting
            $permission->roles()->detach();
            $permission->delete();

            return $this->success(null, 'Permission deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete permission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Assign permissions to a role.
     */
    public function assignToRole(Request $request, $roleIdOrSlug)
    {
        try {
            $role = Role::where('id', $roleIdOrSlug)
                ->orWhere('slug', $roleIdOrSlug)
                ->first();

            if (!$role) {
                return $this->notFound('Role not found');
            }

            $validator = Validator::make($request->all(), [
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'exists:' . config('vormia.table_prefix') . 'permissions,id',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            $role->permissions()->sync($request->permission_ids);

            return $this->success($role->load('permissions'), 'Permissions assigned to role successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to assign permissions to role: ' . $e->getMessage(), 500);
        }
    }
}

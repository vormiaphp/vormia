<?php

namespace Vormia\Vormia\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Vormia\Vormia\Models\Permission;
use Vormia\Vormia\Models\Role;
use Vormia\Vormia\Traits\Model\ApiResponseTrait;

class PermissionController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $permissions = Permission::all();

            return $this->success($permissions, 'Permissions retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve permissions: ' . $e->getMessage(), 500);
        }
    }

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
                'name' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            return $this->success($permission, 'Permission created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create permission: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $permission = Permission::find($id);
            if (! $permission) {
                return $this->notFound('Permission not found');
            }

            return $this->success($permission->load('roles'), 'Permission retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve permission: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $permission = Permission::find($id);
            if (! $permission) {
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

            if (isset($request->name)) {
                $request->merge(['name' => Str::slug($request->name)]);
            }

            $permission->update($request->only(['name', 'description', 'is_active']));

            return $this->success($permission, 'Permission updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update permission: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::find($id);
            if (! $permission) {
                return $this->notFound('Permission not found');
            }

            $permission->roles()->detach();
            $permission->delete();

            return $this->success(null, 'Permission deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete permission: ' . $e->getMessage(), 500);
        }
    }

    public function assignToRole(Request $request, $roleIdOrSlug)
    {
        try {
            $role = Role::where('id', $roleIdOrSlug)
                ->orWhere('slug', $roleIdOrSlug)
                ->first();

            if (! $role) {
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

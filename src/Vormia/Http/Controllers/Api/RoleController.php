<?php

namespace Vormia\Vormia\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Vormia\Vormia\Models\Role;
use Vormia\Vormia\Traits\Model\ApiResponseTrait;

class RoleController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $roles = Role::all();

            return $this->success($roles, 'Roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve roles: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:' . config('vormia.table_prefix') . 'roles,name',
                'description' => 'nullable|string',
                'module' => 'nullable|string',
                'authority' => 'nullable|string|in:main,comp,part',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            $role = Role::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'module' => $request->module ?? 'dashboard,users,permissions,upload,update,download',
                'authority' => $request->authority ?? 'main',
            ]);

            return $this->success($role, 'Role created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create role: ' . $e->getMessage(), 500);
        }
    }

    public function show($idOrSlug)
    {
        try {
            $role = Role::where('id', $idOrSlug)
                ->orWhere('slug', $idOrSlug)
                ->with('permissions')
                ->first();

            if (! $role) {
                return $this->notFound('Role not found');
            }

            return $this->success($role, 'Role retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve role: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $idOrSlug)
    {
        try {
            $role = Role::where('id', $idOrSlug)
                ->orWhere('slug', $idOrSlug)
                ->first();

            if (! $role) {
                return $this->notFound('Role not found');
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique(config('vormia.table_prefix') . 'roles', 'name')->ignore($role->id),
                ],
                'description' => 'nullable|string',
                'module' => 'nullable|string',
                'authority' => 'nullable|string|in:main,comp,part',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed');
            }

            $updateData = $request->only(['name', 'description', 'module', 'authority', 'is_active']);

            if (isset($updateData['name'])) {
                $updateData['slug'] = Str::slug($updateData['name']);
            }

            $role->update($updateData);

            return $this->success($role, 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update role: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($idOrSlug)
    {
        try {
            $role = Role::where('id', $idOrSlug)
                ->orWhere('slug', $idOrSlug)
                ->withCount('users')
                ->first();

            if (! $role) {
                return $this->notFound('Role not found');
            }

            if ($role->users_count > 0) {
                return $this->error('Cannot delete role that is assigned to users', 422, [
                    'users_count' => $role->users_count,
                ]);
            }

            $role->delete();

            return $this->success(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete role: ' . $e->getMessage(), 500);
        }
    }
}

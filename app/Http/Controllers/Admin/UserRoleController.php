<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;
use App\Services\MediaUploadService;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $query = User::query()->with('roles', 'permissions');

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'q' => $q ?? '',
        ]);
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request, AuditService $audit, MediaUploadService $mediaUploader)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if ($request->hasFile('avatar')) {
            $media = $mediaUploader->uploadPublicImage($request->file('avatar'), auth()->id(), 'avatars');
            $user->authorProfile()->create(['avatar_path' => $media->path]);
        }

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        if (!empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        $audit->log('user.created', User::class, $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $data['roles'] ?? [],
            'permissions' => $data['permissions'] ?? []
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load('roles', 'permissions', 'authorProfile');
        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, User $user, AuditService $audit, MediaUploadService $mediaUploader)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if ($request->hasFile('avatar')) {
            $profile = $user->authorProfile;
            if ($profile && $profile->avatar_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->avatar_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->avatar_path);
            }
            $media = $mediaUploader->uploadPublicImage($request->file('avatar'), auth()->id(), 'avatars');
            $user->authorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                ['avatar_path' => $media->path]
            );
        }

        $user->syncRoles($data['roles'] ?? []);
        $user->syncPermissions($data['permissions'] ?? []);

        $audit->log('user.updated', User::class, $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $data['roles'] ?? [],
            'permissions' => $data['permissions'] ?? []
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(User $user, AuditService $audit)
    {
        if (auth()->id() === $user->id) {
            return back()->with('status', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userId = $user->id;
        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        $audit->log('user.deleted', User::class, $userId, [
            'name' => $userName,
            'email' => $userEmail
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User berhasil dihapus.');
    }

    public function syncRoles(Request $request, User $user, AuditService $audit)
    {
        $data = $request->validate(['role_names' => ['array'], 'role_names.*' => ['string']]);
        $user->syncRoles($data['role_names'] ?? []);
        $audit->log('user.roles.synced', User::class, $user->id, ['roles' => $data['role_names'] ?? []]);
        return back()->with('status', 'Role user diperbarui.');
    }

    public function syncPermissions(Request $request, User $user, AuditService $audit)
    {
        $data = $request->validate(['permission_names' => ['array'], 'permission_names.*' => ['string']]);
        $user->syncPermissions($data['permission_names'] ?? []);
        $audit->log('user.permissions.synced', User::class, $user->id, ['permissions' => $data['permission_names'] ?? []]);
        return back()->with('status', 'Permission user diperbarui.');
    }
}

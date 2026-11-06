<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use App\Models\User;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        abort_if(Gate::denies('user_index'), 403);

        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'desc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        $users = User::query()->with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $users->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%");
            });
        }

        // Filtro avanzado por tipo de campo
        if ($request->filled('filterType') && $request->filled('secondFilter') && $request->secondFilter != '') {
            $filterType = $request->filterType;
            $secondFilter = $request->secondFilter;

            if ($filterType === 'rol') {
                $users->whereHas('roles', function($q) use ($secondFilter) {
                    $q->where('id', $secondFilter);
                });
            } elseif ($filterType === 'status') {
                $users->where('status', $secondFilter);
            }
        }

        // Búsqueda general en múltiples campos
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $users->where(function($query) use ($busqueda) {
                $query->where('name', 'LIKE', '%' . $busqueda . '%')
                      ->orWhere('lastname', 'LIKE', '%' . $busqueda . '%')
                      ->orWhere('cedula', 'LIKE', '%' . $busqueda . '%')
                      ->orWhere('email', 'LIKE', '%' . $busqueda . '%')
                      ->orWhere('username', 'LIKE', '%' . $busqueda . '%')
                      ->orWhere('departamento', 'LIKE', '%' . $busqueda . '%')
                      ->orWhereHas('roles', function($q) use ($busqueda) {
                          $q->where('name', 'LIKE', '%' . $busqueda . '%');
                      });
            });
        }

        // Filtros individuales por campo
        if ($request->filled('nombre')) {
            $users->where(function($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->nombre . '%')
                      ->orWhere('lastname', 'LIKE', '%' . $request->nombre . '%');
            });
        }

        if ($request->filled('cedula')) {
            $users->where('cedula', 'LIKE', '%' . $request->cedula . '%');
        }

        if ($request->filled('correo')) {
            $users->where('email', 'LIKE', '%' . $request->correo . '%');
        }

        if ($request->filled('username')) {
            $users->where('username', 'LIKE', '%' . $request->username . '%');
        }

        if ($request->filled('departamento')) {
            $users->where('departamento', 'LIKE', '%' . $request->departamento . '%');
        }

        // Búsqueda simple (mantiene compatibilidad hacia atrás)
        if ($request->filled('search') && !$request->filled('busqueda') && !$request->filled('filterType')) {
            $search = $request->search;
            $users->where(function($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('lastname', 'LIKE', '%' . $search . '%')
                      ->orWhere('cedula', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%')
                      ->orWhere('username', 'LIKE', '%' . $search . '%')
                      ->orWhere('departamento', 'LIKE', '%' . $search . '%')
                      ->orWhereHas('roles', function($q) use ($search) {
                          $q->where('name', 'LIKE', '%' . $search . '%');
                      });
            });
        }

        $users = $users->orderBy($sort, $direction)->paginate(10);

        // Obtener roles para el filtro
        $roles = Role::all()->pluck('name', 'id');

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'users' => $users,
                    'roles' => Role::orderBy('name')->get(['id', 'name']),
                    'current_user_id' => auth()->id(),
                    'permissions' => [
                        'can_create' => auth()->user()?->can('user_create') ?? false,
                        'can_edit' => auth()->user()?->can('user_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('user_destroy') ?? false,
                        'can_export' => true,
                    ]
                ],
                'message' => 'Usuarios obtenidos exitosamente'
            ], 200);
        }

        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    public function create()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        abort_if(Gate::denies('user_create'), 403);
        $roles = Role::orderBy('name')->pluck('name', 'id');

        // Lista de departamentos para el formulario
        $departamentos = [
            'GTIC' => 'GTIC',
            'OGH' => 'OGH',
            'OAC' => 'OAC',
            'Consultoria Juridica' => 'Consultoria Juridica',
            'GEDP' => 'GEDP',
            'OPP' => 'Operaciones',
            'OGA' => 'OGA',
            'Presidencia' => 'Presidencia',
            'Auditoría Interna' => 'Auditoría Interna',
        ];

        return view('users.create', compact('roles', 'departamentos'));
    }

    public function store(UserCreateRequest $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        // Crear usuario con todos los campos
        $user = User::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'cedula' => $request->cedula,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'departamento' => $request->departamento,
            'status' => $request->status ?? true,
            'is_active' => $request->status ?? true,
        ]);

        // Asignar roles - sincronizar por nombre, no por ID
        $roles = $request->input('roles');
        if (!empty($roles)) {
            // Convertir ID a nombre de rol (maneja integer o array)
            $roleNames = [];
            $roleIds = is_array($roles) ? $roles : [$roles];
            foreach ($roleIds as $roleId) {
                $role = Role::findById($roleId, 'web');
                if ($role) {
                    $roleNames[] = $role->name;
                }
            }
            $user->syncRoles($roleNames);
        } else {
            $user->syncRoles([]);
        }

        // Manejar foto de perfil si se subió
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $guessedExt = strtolower($file->guessExtension() ?? '');
            $safeExt = in_array($guessedExt, ['jpeg', 'jpg', 'png', 'webp'])
                ? ($guessedExt === 'jpeg' ? 'jpg' : $guessedExt)
                : 'jpg';
            $filename = time() . '_' . $user->id . '_' . Str::random(6) . '.' . $safeExt;
            $file->move(public_path('archivos/avatars'), $filename);
            $user->profile_photo = $filename;
            $user->save();
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }

    public function show(User $user, Request $request)
    {
        $user->load('roles');
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $user,
                'message' => 'Usuario obtenido exitosamente'
            ], 200);
        }
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        abort_if(Gate::denies('user_edit'), 403);
        $roles = Role::orderBy('name')->pluck('name', 'id');
        $user->load('roles');

        // Lista de departamentos para el formulario
        $departamentos = [
            'GTIC' => 'GTIC',
            'OGH' => 'OGH',
            'OAC' => 'OAC',
            'Consultoria Juridica' => 'Consultoria Juridica',
            'GEDP' => 'GEDP',
            'OPP' => 'Operaciones',
            'OGA' => 'OGA',
            'Presidencia' => 'Presidencia',
            'Auditoría Interna' => 'Auditoría Interna',
        ];

        return view('users.edit', compact('user', 'roles', 'departamentos'));
    }

    public function update(UserEditRequest $request, User $user)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        // Actualizar datos del usuario
        $data = [
            'name' => $request->name,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'cedula' => $request->cedula,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'departamento' => $request->departamento,
            'status' => $request->status ?? true,
            'is_active' => $request->status ?? true,
        ];

        // Actualizar contraseña solo si se proporciona
        $password = $request->input('password');
        if ($password) {
            $data['password'] = bcrypt($password);
        }

        $user->update($data);

        // Actualizar roles - convertir IDs a nombres de rol
        $roles = $request->input('roles');
        if (!empty($roles)) {
            // Convertir ID a nombre de rol (maneja integer o array)
            $roleNames = [];
            $roleIds = is_array($roles) ? $roles : [$roles];
            foreach ($roleIds as $roleId) {
                $role = Role::findById($roleId, 'web');
                if ($role) {
                    $roleNames[] = $role->name;
                }
            }
            $user->syncRoles($roleNames);
        } else {
            $user->syncRoles([]);
        }

        // Manejar foto de perfil si se subió
        if ($request->hasFile('profile_photo')) {
            // Eliminar foto anterior si existe
            if ($user->profile_photo && file_exists(public_path('archivos/avatars/' . $user->profile_photo))) {
                unlink(public_path('archivos/avatars/' . $user->profile_photo));
            }
            $file = $request->file('profile_photo');
            $guessedExt = strtolower($file->guessExtension() ?? '');
            $safeExt = in_array($guessedExt, ['jpeg', 'jpg', 'png', 'webp'])
                ? ($guessedExt === 'jpeg' ? 'jpg' : $guessedExt)
                : 'jpg';
            $filename = time() . '_' . $user->id . '_' . Str::random(6) . '.' . $safeExt;
            $file->move(public_path('archivos/avatars'), $filename);
            $user->profile_photo = $filename;
            $user->save();
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user, Request $request)
    {
        if (!auth()->check()) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Tu sesión ha expirado.'], 401);
            }
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        abort_if(Gate::denies('user_destroy'), 403);

        if (auth()->user()->id == $user->id) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta'], 422);
            }
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta');
        }

        try {
            // Eliminar foto de perfil si existe
            if ($user->profile_photo && file_exists(public_path('archivos/avatars/' . $user->profile_photo))) {
                unlink(public_path('archivos/avatars/' . $user->profile_photo));
            }

            // Eliminar relaciones con almacenamiento_impresoras (establecer user_id a null)
            \App\Models\AlmacenamientoImpresora::where('user_id', $user->id)->update(['user_id' => null]);

            // Eliminar el usuario
            $user->delete();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
            }

            return back()->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar el usuario porque tiene registros asociados'], 422);
            }
            return back()->with('error', 'No se puede eliminar el usuario porque tiene registros asociados');
        }
    }
    public function export(){
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}

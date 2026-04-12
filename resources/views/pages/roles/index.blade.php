<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="Role Management" subtitle="Create roles and sync permission bundles with laravel-permission." icon="o-users" />
        @include('security::partials.flash')
        @include('security::partials.nav')

        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <x-tallui-card title="Create Role" subtitle="Define a new role and assign initial permissions." icon="o-plus-circle">
                <form method="POST" action="{{ route('security.roles.store') }}" class="space-y-4">
                    @csrf
                    <label class="form-control">
                        <span class="label-text text-xs uppercase text-base-content/60">Role Name</span>
                        <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full" placeholder="security_analyst" />
                    </label>

                    <div class="space-y-2">
                        <div class="text-xs uppercase text-base-content/60">Permissions</div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($permissions as $permission)
                                <label class="flex items-center gap-2 rounded-xl border border-base-200 px-3 py-2 text-sm">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="checkbox checkbox-sm" @checked(in_array($permission->name, old('permissions', []), true)) />
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <x-tallui-button type="submit" class="btn-primary btn-sm">Create Role</x-tallui-button>
                </form>
            </x-tallui-card>

            <div class="space-y-4">
                @forelse ($roles as $role)
                    <x-tallui-card>
                        <form method="POST" action="{{ route('security.roles.update', $role->getKey()) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <input type="text" name="name" value="{{ $role->name }}" class="input input-bordered input-sm max-w-xs" />
                                        <x-tallui-badge type="ghost">{{ $role->users_count }} users</x-tallui-badge>
                                    </div>
                                    <div class="mt-2 text-sm text-base-content/60">Guard: {{ $role->guard_name }}</div>
                                </div>
                                <x-tallui-button type="submit" class="btn-primary btn-sm">Save Role</x-tallui-button>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-2 rounded-xl border border-base-200 px-3 py-2 text-sm">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->name }}"
                                            class="checkbox checkbox-sm"
                                            @checked($role->permissions->contains('name', $permission->name))
                                        />
                                        <span>{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </form>
                    </x-tallui-card>
                @empty
                    <x-tallui-empty-state title="No roles defined" description="Create the first application role to start assigning permissions." />
                @endforelse
            </div>
        </div>
    </div>
</x-layouts::app>

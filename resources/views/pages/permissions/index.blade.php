<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="Permission Management" subtitle="Manage permission keys and review which roles receive them." icon="o-key" />
        @include('security::partials.flash')
        @include('security::partials.nav')

        <div class="grid gap-6 xl:grid-cols-[0.7fr_1.3fr]">
            <x-tallui-card title="Create Permission" subtitle="Add a new permission key for application access control." icon="o-plus-circle">
                <form method="POST" action="{{ route('security.permissions.store') }}" class="space-y-4">
                    @csrf
                    <label class="form-control">
                        <span class="label-text text-xs uppercase text-base-content/60">Permission Name</span>
                        <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full" placeholder="security.roles.manage" />
                    </label>

                    <x-tallui-button type="submit" class="btn-primary btn-sm">Create Permission</x-tallui-button>
                </form>
            </x-tallui-card>

            <div class="space-y-4">
                @forelse ($permissions as $permission)
                    <x-tallui-card>
                        <form method="POST" action="{{ route('security.permissions.update', $permission->getKey()) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="grow">
                                    <label class="form-control">
                                        <span class="label-text text-xs uppercase text-base-content/60">Permission</span>
                                        <input type="text" name="name" value="{{ $permission->name }}" class="input input-bordered input-sm w-full max-w-md" />
                                    </label>
                                    <div class="mt-2 text-sm text-base-content/60">Guard: {{ $permission->guard_name }}</div>
                                </div>
                                <x-tallui-button type="submit" class="btn-primary btn-sm">Save Permission</x-tallui-button>
                            </div>

                            <div>
                                <div class="text-xs uppercase text-base-content/60">Assigned Roles</div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse ($permission->roles as $role)
                                        <x-tallui-badge type="ghost">{{ $role->name }}</x-tallui-badge>
                                    @empty
                                        <span class="text-sm text-base-content/60">No roles currently grant this permission.</span>
                                    @endforelse
                                </div>
                            </div>
                        </form>
                    </x-tallui-card>
                @empty
                    <x-tallui-empty-state title="No permissions defined" description="Create permission keys here, then assign them from the Roles page." />
                @endforelse
            </div>
        </div>

        @if ($roles->isNotEmpty())
            <x-tallui-card title="Current Roles" subtitle="Reference list of roles available for permission assignment." icon="o-identification">
                <div class="flex flex-wrap gap-2">
                    @foreach ($roles as $role)
                        <x-tallui-badge type="ghost">{{ $role->name }}</x-tallui-badge>
                    @endforeach
                </div>
            </x-tallui-card>
        @endif
    </div>
</x-layouts::app>

<div class="flex items-center gap-4 overflow-x-auto no-scrollbar pl-2">

    <!-- System Settings Group -->
    <!-- General -->
    <a href="{{ route('settings.general') }}"
        class="island-item group {{ request()->routeIs('settings.general') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('settings.general') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-desktop text-2xl group-hover:rotate-45 transition-transform duration-500 ease-in-out"></i>
        </div>
        <span class="island-label {{ request()->routeIs('settings.general') ? 'text-indigo-900' : '' }}">General</span>
    </a>

    <!-- Styles -->
    <a href="#" class="island-item group">
        <div class="icon-container text-slate-500 group-hover:text-indigo-600">
            <i class="fas fa-palette text-2xl group-hover:scale-110 transition-transform duration-200"></i>
        </div>
        <span class="island-label">Styles</span>
    </a>

    <div class="divider"></div>

    <!-- User Settings Group -->
    <!-- Access -->
    <a href="{{ route('settings.access') }}"
        class="island-item group {{ request()->routeIs('settings.access') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('settings.access') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-users-cog text-2xl group-hover:translate-x-1 transition-transform"></i>
        </div>
        <span class="island-label {{ request()->routeIs('settings.access') ? 'text-indigo-900' : '' }}">Access</span>
    </a>

    <!-- Add/Edit -->
    <a href="{{ route('settings.users') }}"
        class="island-item group {{ request()->routeIs('settings.users') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('settings.users') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-user-plus text-2xl group-hover:scale-110 transition-transform"></i>
        </div>
        <span class="island-label {{ request()->routeIs('settings.users') ? 'text-indigo-900' : '' }}">Add/Edit</span>
    </a>

    <div class="divider"></div>

    <!-- Schedule Group -->
    <!-- To Do -->
    <a href="{{ route('settings.todo') }}"
        class="island-item group {{ request()->routeIs('settings.todo') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('settings.todo') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-clipboard-list text-2xl group-hover:-translate-y-1 transition-transform"></i>
        </div>
        <span class="island-label {{ request()->routeIs('settings.todo') ? 'text-indigo-900' : '' }}">To Do</span>
    </a>

    <!-- Reminder -->
    <a href="{{ route('settings.reminder') }}"
        class="island-item group {{ request()->routeIs('settings.reminder') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('settings.reminder') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-clock text-2xl group-hover:shake-gentle"></i>
        </div>
        <span class="island-label {{ request()->routeIs('settings.reminder') ? 'text-indigo-900' : '' }}">Reminder</span>
    </a>

    <div class="divider"></div>

    <!-- Employees Group -->
    <a href="{{ route('settings.employees') }}"
        class="island-item group {{ request()->routeIs('settings.employees') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('settings.employees') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-user-tie text-2xl group-hover:bounce-gentle"></i>
        </div>
        <span class="island-label {{ request()->routeIs('settings.employees') ? 'text-indigo-900' : '' }}">Employees</span>
    </a>

    <div class="divider"></div>

    <!-- Save Data Group -->
    <a href="#" class="island-item group">
        <div class="icon-container text-slate-500 group-hover:text-indigo-600">
            <i class="fas fa-save text-2xl group-hover:scale-110"></i>
        </div>
        <span class="island-label">Backup</span>
    </a>

    <!-- Exit Group (Right aligned roughly by flex) -->
    <div class="ml-auto flex items-center gap-4">
        <div class="divider"></div>
        <a href="{{ route('dashboard') }}" class="island-item group">
            <div class="icon-container text-red-400 group-hover:text-red-600">
                <i class="fas fa-power-off text-2xl group-hover:pulse-red"></i>
            </div>
            <span class="island-label text-red-400 group-hover:text-red-700">Exit</span>
        </a>
    </div>

</div>
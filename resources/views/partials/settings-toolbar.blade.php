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

    <!-- Customers -->
    <a href="{{ route('customers.index') }}"
        class="island-item group {{ request()->routeIs('customers.*') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('customers.*') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-address-book text-2xl group-hover:-translate-y-1 transition-transform"></i>
        </div>
        <span class="island-label {{ request()->routeIs('customers.*') ? 'text-indigo-900' : '' }}">Customers</span>
    </a>



    <!-- User Settings Group -->
    <!-- Access -->
    <a href="{{ route('settings.access') }}"
        class="island-item group {{ request()->routeIs('settings.access') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('settings.access') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-users-cog text-2xl group-hover:translate-x-1 transition-transform"></i>
        </div>
        <span class="island-label {{ request()->routeIs('settings.access') ? 'text-indigo-900' : '' }}">Access</span>
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

    <!-- Staff Group -->
    <a href="{{ route('staff.create') }}"
        class="island-item group {{ request()->routeIs('staff.create') ? 'active-pill-target' : '' }}">
        <div class="icon-container {{ request()->routeIs('staff.create') ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-indigo-600">
            <i class="fas fa-users-cog text-2xl group-hover:bounce-gentle"></i>
        </div>
        <span class="island-label {{ request()->routeIs('staff.create') ? 'text-indigo-900' : '' }}">Staff</span>
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
  

</div>
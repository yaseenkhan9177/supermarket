@extends('layouts.admin')

@section('content')
<div class="h-[calc(100vh-6rem)] bg-gray-50 flex overflow-hidden font-sans text-sm relative" x-data="employeeManager()">

    <!-- 1. Left Panel: The Team Directory (35%) -->
    <div class="w-[35%] bg-white border-r border-gray-200 flex flex-col h-full z-10">

        <!-- Header: Search & Filter -->
        <div class="p-4 border-b border-gray-100 bg-white sticky top-0 z-20">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Team Directory</h2>
                <button @click="resetForm()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] uppercase font-bold px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-plus mr-1"></i> New
                </button>
            </div>

            <div class="relative group">
                <input type="text" x-model="search" placeholder="Search by Name, Email, or Phone..." class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition-all placeholder-gray-400 font-medium">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-indigo-400 transition-colors"></i>
                </div>
            </div>
        </div>

        <!-- The List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar bg-gray-50/50">
            <template x-for="emp in filteredEmployees" :key="emp.id">
                <div @click="selectEmployee(emp)"
                    :class="{'ring-2 ring-indigo-500 bg-white shadow-md': currentId === emp.id, 'bg-white border border-gray-200 hover:border-indigo-300 hover:shadow-sm': currentId !== emp.id}"
                    class="p-4 rounded-xl transition-all cursor-pointer relative overflow-hidden group">

                    <div class="flex items-start">
                        <!-- Avatar -->
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-50 to-indigo-100 border-2 border-white shadow-sm flex items-center justify-center text-indigo-700 font-bold text-sm tracking-wide" x-text="getInitials(emp.name)"></div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <h3 class="text-sm font-bold text-gray-900 truncate" x-text="emp.name"></h3>
                                <span class="w-2 h-2 rounded-full bg-green-500 mt-1.5"></span>
                            </div>
                            <p class="text-xs font-medium text-gray-500 truncate" x-text="emp.role || 'No Role'"></p>

                            <div class="mt-2 pt-2 border-t border-gray-100 flex justify-between text-[10px] text-gray-400 font-mono">
                                <span>Email: <span class="text-gray-600 font-bold" x-text="emp.email"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="filteredEmployees.length === 0" class="text-center p-8 text-gray-400 text-xs">
                No employees found.
            </div>
        </div>
    </div>

    <!-- 2. Right Panel: Employee Profile Editor (65%) -->
    <div class="flex-1 bg-gray-50 flex flex-col h-full relative overflow-hidden">

        <!-- Header -->
        <div class="px-8 py-6 bg-white border-b border-gray-200 flex justify-between items-end">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Human Resources</p>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-gray-900" x-text="currentId ? 'Edit Employee' : 'Create New Employee'"></h1>
                </div>
            </div>
        </div>

        <!-- Scrollable Form Content -->
        <div class="flex-1 overflow-y-auto p-8 pb-32 custom-scrollbar">
            <div class="max-w-3xl mx-auto space-y-8">

                <!-- Section A: Identity & Contact -->
                <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                    <h3 class="text-sm font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-id-card text-indigo-500 mr-2 bg-indigo-50 p-1.5 rounded-lg"></i> Identity & Contact
                    </h3>

                    <div class="space-y-6">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Full Name</label>
                            <input type="text" x-model="form.name" placeholder="e.g. Muhammad Ali" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 font-bold text-lg focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none">
                        </div>

                        <!-- Contact Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Phone / Mobile</label>
                                <input type="text" x-model="form.phone" placeholder="+92 300 1234567" class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 font-medium focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Email Address</label>
                                <div class="relative">
                                    <input type="email" x-model="form.email" placeholder="ali@example.com" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 font-medium focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section B: Role & Permissions -->
                <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                    <h3 class="text-sm font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-lock text-emerald-500 mr-2 bg-emerald-50 p-1.5 rounded-lg"></i> Access & Permissions
                    </h3>

                    <div class="space-y-6">
                        <!-- Role Dropdown -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Employee Role/Type</label>
                            <div class="relative">
                                <select x-model="form.role" @change="applyRoleTemplate()" class="block w-full pl-4 pr-10 py-3 bg-white border border-gray-200 rounded-xl text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500 shadow-sm appearance-none cursor-pointer">
                                    <option value="">Select Role...</option>
                                    <template x-for="role in roles" :key="role.id">
                                        <option :value="role.name" x-text="role.name"></option>
                                    </template>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-2">Selecting a role will apply standard permissions. You can customize them below.</p>
                        </div>

                        <!-- Permission Matrix -->
                        <div>
                            <div class="grid grid-cols-1 gap-6">
                                <template x-for="(perms, category) in permissionMatrix" :key="category">
                                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                        <h4 class="text-xs font-bold text-gray-700 uppercase mb-3 border-b border-gray-200 pb-2" x-text="category + ' Module'"></h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <template x-for="(label, key) in perms" :key="key">
                                                <label class="flex items-start space-x-2 cursor-pointer group">
                                                    <div class="relative flex items-start">
                                                        <input type="checkbox" :value="category.toLowerCase() + '.' + key" x-model="form.permissions"
                                                            class="peer h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-0.5">
                                                    </div>
                                                    <span class="text-xs text-gray-600 group-hover:text-indigo-700 transition-colors" x-text="label"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </section>

            </div>
        </div>

        <!-- 3. Sticky Footer Action Bar -->
        <div class="absolute bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 shadow-[0_-5px_15px_-5px_rgba(0,0,0,0.05)] z-30">
            <div class="max-w-3xl mx-auto flex justify-between items-center px-4">

                <!-- Delete -->
                <button x-show="currentId" @click="deleteEmployee" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-wide transition-colors">
                    Delete Employee
                </button>
                <div x-show="!currentId"></div>

                <!-- Primary: Save -->
                <button @click="saveEmployee"
                    :disabled="saving"
                    class="bg-gray-900 hover:bg-black text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center min-w-[160px] justify-center disabled:opacity-50 disabled:cursor-not-allowed">

                    <span x-show="!saving" class="flex items-center">
                        <span x-text="currentId ? 'Update Profile' : 'Save Profile'"></span>
                    </span>

                    <span x-show="saving" class="flex items-center">
                        <i class="fas fa-circle-notch fa-spin mr-2"></i> Saving...
                    </span>
                </button>
            </div>
        </div>

    </div>

</div>

<!-- Alpine JS Logic -->
<script>
    function employeeManager() {
        return {
            employees: [],
            roles: [],
            permissionMatrix: {},
            search: '',
            currentId: null,
            form: {
                name: '',
                email: '',
                phone: '',
                role: '',
                permissions: []
            },
            saving: false,

            init() {
                this.fetchEmployees();
                this.fetchMeta();
            },

            async fetchMeta() {
                try {
                    const response = await fetch('/settings/employees-meta');
                    const data = await response.json();
                    this.roles = data.roles;
                    this.permissionMatrix = data.matrix;
                } catch (error) {
                    console.error('Error fetching meta:', error);
                }
            },

            applyRoleTemplate() {
                const selectedRole = this.roles.find(r => r.name === this.form.role);
                if (selectedRole && selectedRole.default_permissions) {
                    // Determine if we need to merge or replace. For now, replacing is cleaner for a "template" feel.
                    // But let's check if the user has manually edited?
                    // Simple behavior: Apply template overrides current selection.
                    if (selectedRole.default_permissions.includes('*')) {
                        // If super admin, maybe select all? For now, we manually handle '*' in backend,
                        // but for UI, let's just leave it or select all keys.
                        // A safe bet is to just set it to ['*'] and handle display logic,
                        // OR select all available keys from matrix.
                        let allPerms = [];
                        for (const cat in this.permissionMatrix) {
                            for (const key in this.permissionMatrix[cat]) {
                                allPerms.push(cat.toLowerCase() + '.' + key);
                            }
                        }
                        this.form.permissions = allPerms;
                    } else {
                        this.form.permissions = [...selectedRole.default_permissions];
                    }
                }
            },
            get filteredEmployees() {
                if (this.search === '') return this.employees;
                return this.employees.filter(emp =>
                    emp.name.toLowerCase().includes(this.search.toLowerCase()) ||
                    (emp.email && emp.email.toLowerCase().includes(this.search.toLowerCase()))
                );
            },

            async fetchEmployees() {
                try {
                    const response = await fetch('/settings/employees-api');
                    this.employees = await response.json();
                } catch (error) {
                    console.error('Error fetching employees:', error);
                }
            },

            resetForm() {
                this.currentId = null;
                this.form = {
                    name: '',
                    email: '',
                    phone: '',
                    role: '',
                    permissions: []
                };
            },

            selectEmployee(emp) {
                this.currentId = emp.id;
                this.form = {
                    name: emp.name,
                    email: emp.email,
                    phone: emp.phone,
                    role: emp.role,
                    permissions: emp.permissions || []
                };
            },

            getInitials(name) {
                return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            },

            async saveEmployee() {
                this.saving = true;
                try {
                    const url = this.currentId ? `/settings/employees-api/${this.currentId}` : '/settings/employees-api';
                    const method = this.currentId ? 'PUT' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.form)
                    });

                    if (response.ok) {
                        await this.fetchEmployees();
                        if (!this.currentId) this.resetForm();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Employee profile has been saved successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        const data = await response.json();
                        alert('Error: ' + (data.message || 'Failed to save'));
                    }
                } catch (error) {
                    console.error('Error saving:', error);
                    alert('An error occurred.');
                } finally {
                    this.saving = false;
                }
            },

            async deleteEmployee() {
                if (!confirm('Are you sure you want to delete this employee?')) return;

                try {
                    const response = await fetch(`/settings/employees-api/${this.currentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        await this.fetchEmployees();
                        this.resetForm();
                    } else {
                        alert('Failed to delete');
                    }
                } catch (error) {
                    console.error('Error deleting:', error);
                }
            }
        }
    }
</script>
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
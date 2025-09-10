<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Job management
            'view jobs',
            'create jobs',
            'edit jobs',
            'delete jobs',
            
            // Application management
            'view applications',
            'create applications',
            'edit applications',
            'delete applications',
            
            // Timesheet management
            'view timesheets',
            'create timesheets',
            'edit timesheets',
            'delete timesheets',
            
            // Contract management
            'view contracts',
            'create contracts',
            'edit contracts',
            'delete contracts',
            
            // Payroll management
            'view payroll',
            'create payroll',
            'edit payroll',
            'delete payroll',
            
            // Dashboard access
            'view dashboard',
            
            // Reports
            'view reports',
            'export reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $employerRole = Role::create([
            'name' => 'employer',
            'guard_name' => 'web'
        ]);

        $candidateRole = Role::create([
            'name' => 'candidate',
            'guard_name' => 'web'
        ]);

        // Assign permissions to roles
        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // Employer permissions
        $employerRole->syncPermissions([
            'view dashboard',
            'view jobs',
            'create jobs',
            'edit jobs',
            'delete jobs',
            'view applications',
            'edit applications',
            'view timesheets',
            'create timesheets',
            'edit timesheets',
            'view contracts',
            'create contracts',
            'edit contracts',
            'view payroll',
            'create payroll',
            'edit payroll',
            'view reports',
            'export reports',
        ]);

        // Candidate permissions
        $candidateRole->syncPermissions([
            'view dashboard',
            'view jobs',
            'create applications',
            'view applications',
            'edit applications',
            'view timesheets',
            'create timesheets',
            'view contracts',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
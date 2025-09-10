<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\User;
use Modules\Jobs\Models\Job;
use Modules\Jobs\Models\JobCategory;
use Modules\Jobs\Models\JobApplication;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // Create a test tenant
        $tenant = Tenant::create([
            'name' => 'BaoProd Gabon',
            'domain' => 'baoprod-gabon.local',
            'subdomain' => 'baoprod-gabon',
            'country_code' => 'GA',
            'currency' => 'XOF',
            'language' => 'fr',
            'settings' => [
                'timezone' => 'Africa\/Libreville',
                'date_format' => 'd\/m\/Y',
                'time_format' => 'H:i',
            ],
            'modules' => ['core', 'contrats', 'timesheets', 'paie'],
            'is_active' => true,
            'trial_ends_at' => now()->addDays(30),
        ]);

        // Create admin user
        $admin = User::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Admin',
            'last_name' => 'BaoProd',
            'email' => 'admin@baoprod-gabon.com',
            'password' => Hash::make('password'),
            'phone' => '+241 01 23 45 67',
            'type' => 'admin',
            'is_active' => true,
        ]);
        $adminRole = Role::findByName('admin', 'web');
        $admin->assignRole($adminRole);

        // Create employer user
        $employer = User::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'employer@baoprod-gabon.com',
            'password' => Hash::make('password'),
            'phone' => '+241 01 23 45 68',
            'type' => 'employer',
            'profile_data' => [
                'company_name' => 'Entreprise Gabonaise SARL',
                'company_size' => '50-100',
                'industry' => 'Construction',
                'website' => 'https:\/\/entreprise-gabon.com',
                'description' => 'Entreprise spécialisée dans la construction et les travaux publics au Gabon.',
            ],
            'is_active' => true,
        ]);
        $employerRole = Role::findByName('employer', 'web');
        $employer->assignRole($employerRole);

        // Create candidate users
        $candidates_data = [
            [
                'first_name' => 'Marie',
                'last_name' => 'Mba',
                'email' => 'marie.mba@example.com',
                'phone' => '+241 01 23 45 69',
                'profile_data' => [
                    'skills' => ['PHP', 'Laravel', 'JavaScript', 'MySQL'],
                    'experience' => [
                        [
                            'company' => 'Tech Gabon',
                            'position' => 'Développeur Web',
                            'duration' => '2 ans',
                            'description' => 'Développement d\'applications web avec Laravel'
                        ]
                    ],
                    'education' => [
                        [
                            'degree' => 'Master en Informatique',
                            'school' => 'Université Omar Bongo',
                            'year' => '2022'
                        ]
                    ],
                    'languages' => ['Français', 'Anglais'],
                    'availability' => 'immediate',
                    'expected_salary' => 150000,
                ],
            ],
            [
                'first_name' => 'Pierre',
                'last_name' => 'Nguema',
                'email' => 'pierre.nguema@example.com',
                'phone' => '+241 01 23 45 70',
                'profile_data' => [
                    'skills' => ['Comptabilité', 'Gestion', 'Excel', 'Sage'],
                    'experience' => [
                        [
                            'company' => 'Cabinet Comptable Libreville',
                            'position' => 'Comptable',
                            'duration' => '3 ans',
                            'description' => 'Tenue de comptabilité pour PME'
                        ]
                    ],
                    'education' => [
                        [
                            'degree' => 'BTS Comptabilité',
                            'school' => 'Institut Supérieur de Gestion',
                            'year' => '2021'
                        ]
                    ],
                    'languages' => ['Français'],
                    'availability' => '2 semaines',
                    'expected_salary' => 120000,
                ],
            ],
        ];

        $candidateRole = Role::findByName('candidate', 'web');
        foreach ($candidates_data as $candidateData) {
            $candidate = User::create([
                'tenant_id' => $tenant->id,
                'first_name' => $candidateData['first_name'],
                'last_name' => $candidateData['last_name'],
                'email' => $candidateData['email'],
                'password' => Hash::make('password'),
                'phone' => $candidateData['phone'],
                'type' => 'candidate',
                'profile_data' => $candidateData['profile_data'],
                'is_active' => true,
            ]);
            $candidate->assignRole($candidateRole);
        }

        // Create sample job categories
        $categories = collect(['Informatique', 'Comptabilité', 'Administration', 'Construction', 'Vente'])->map(function ($name) use ($tenant) {
            return JobCategory::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        });

        // Create sample jobs
        $jobs = [
            [
                'title' => 'Développeur Web Laravel',
                'description' => 'Nous recherchons un développeur web expérimenté avec Laravel pour rejoindre notre équipe de développement.',
                'requirements' => 'Maîtrise de PHP, Laravel, MySQL, JavaScript. Expérience minimum 2 ans.',
                'location' => 'Libreville, Gabon',
                'latitude' => 0.4162,
                'longitude' => 9.4673,
                'type' => 'full_time',
                'salary_min' => 120000,
                'salary_max' => 180000,
                'salary_currency' => 'XOF',
                'salary_period' => 'monthly',
                'start_date' => now()->addDays(15),
                'positions_available' => 2,
                'benefits' => ['Assurance santé', 'Formation', 'Prime de transport'],
                'skills_required' => ['PHP', 'Laravel', 'JavaScript', 'MySQL'],
                'experience_required' => 2,
                'education_level' => 'Bac+3',
                'is_remote' => false,
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'expires_at' => now()->addDays(30),
            ],
            [
                'title' => 'Comptable',
                'description' => 'Poste de comptable pour une entreprise en pleine croissance. Gestion de la comptabilité générale.',
                'requirements' => 'Formation comptable, maîtrise des logiciels comptables, expérience 3 ans minimum.',
                'location' => 'Port-Gentil, Gabon',
                'latitude' => -0.7167,
                'longitude' => 8.7833,
                'type' => 'full_time',
                'salary_min' => 100000,
                'salary_max' => 140000,
                'salary_currency' => 'XOF',
                'salary_period' => 'monthly',
                'start_date' => now()->addDays(20),
                'positions_available' => 1,
                'benefits' => ['Assurance santé', 'Prime de performance'],
                'skills_required' => ['Comptabilité', 'Sage', 'Excel'],
                'experience_required' => 3,
                'education_level' => 'BTS',
                'is_remote' => false,
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'expires_at' => now()->addDays(25),
            ],
            [
                'title' => 'Assistant Administratif',
                'description' => 'Assistant administratif polyvalent pour soutenir les équipes opérationnelles.',
                'requirements' => 'Maîtrise de l\'outil informatique, bon niveau de français, sens de l\'organisation.',
                'location' => 'Franceville, Gabon',
                'latitude' => -1.6333,
                'longitude' => 13.5833,
                'type' => 'full_time',
                'salary_min' => 80000,
                'salary_max' => 100000,
                'salary_currency' => 'XOF',
                'salary_period' => 'monthly',
                'start_date' => now()->addDays(10),
                'positions_available' => 1,
                'benefits' => ['Formation', 'Prime de transport'],
                'skills_required' => ['Bureautique', 'Organisation', 'Communication'],
                'experience_required' => 1,
                'education_level' => 'Bac',
                'is_remote' => false,
                'is_featured' => false,
                'status' => 'draft',
            ],
        ];

        foreach ($jobs as $jobData) {
            Job::create(array_merge($jobData, [
                'tenant_id' => $tenant->id,
                'employer_id' => $employer->id,
                'job_category_id' => $categories->random()->id,
            ]));
        }

        // Create sample applications
        $publishedJobs = Job::where('status', 'published')->get();
        $candidateUsers = User::where('type', 'candidate')->get();

        if ($publishedJobs->count() > 0 && $candidateUsers->count() > 0) {
            // Marie applies for the developer job
            $developerJob = $publishedJobs->where('title', 'Développeur Web Laravel')->first();
            $marie = $candidateUsers->where('email', 'marie.mba@example.com')->first();
            
            if ($developerJob && $marie) {
                JobApplication::create([
                    'tenant_id' => $tenant->id,
                    'job_id' => $developerJob->id,
                    'candidate_id' => $marie->id,
                    'cover_letter' => 'Bonjour, je suis très intéressée par ce poste de développeur Laravel. Mon expérience avec Laravel et PHP correspond parfaitement à vos besoins.',
                    'documents' => ['cv_marie_mba.pdf', 'lettre_motivation.pdf'],
                    'expected_salary' => 150000,
                    'available_start_date' => now()->addDays(7),
                    'status' => 'pending',
                ]);
            }

            // Pierre applies for the accounting job
            $accountingJob = $publishedJobs->where('title', 'Comptable')->first();
            $pierre = $candidateUsers->where('email', 'pierre.nguema@example.com')->first();
            
            if ($accountingJob && $pierre) {
                JobApplication::create([
                    'tenant_id' => $tenant->id,
                    'job_id' => $accountingJob->id,
                    'candidate_id' => $pierre->id,
                    'cover_letter' => 'Je souhaite postuler pour le poste de comptable. Mon expérience de 3 ans en comptabilité générale me permettra de contribuer efficacement à votre équipe.',
                    'documents' => ['cv_pierre_nguema.pdf'],
                    'expected_salary' => 120000,
                    'available_start_date' => now()->addDays(14),
                    'status' => 'pending',
                ]);
            }
        }

        // Seed des contrats de test
        // $this->call(ContratSeeder::class);
        
        // Seed des timesheets de test
        // $this->call(TimesheetSeeder::class);
        
        // Seed des bulletins de paie de test
        // $this->call(PaieSeeder::class);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Test tenant: BaoProd Gabon');
        $this->command->info('Admin email: admin@baoprod-gabon.com');
        $this->command->info('Employer email: employer@baoprod-gabon.com');
        $this->command->info('Password for all users: password');
    }
}

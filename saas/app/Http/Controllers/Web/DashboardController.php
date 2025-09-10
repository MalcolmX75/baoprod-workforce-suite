<?php

namespace BaoProd\Workforce\Http\Controllers\Web;

use BaoProd\Workforce\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BaoProd\Workforce\Models\User;
use Modules\Jobs\Models\Job;
use Modules\Jobs\Models\JobApplication as Application;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Timesheet;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_jobs' => Job::count(),
            'total_applications' => Application::count(),
            'total_contracts' => Contrat::count(),
            'active_employees' => User::where('type', 'candidate')->where('is_active', true)->count(),
            'active_employers' => User::where('type', 'employer')->where('is_active', true)->count(),
            'pending_applications' => Application::where('status', 'pending')->count(),
            'active_contracts' => Contrat::where('statut', 'ACTIF')->count(),
        ];

        // Statistiques mensuelles
        $currentMonth = Carbon::now();
        $monthlyStats = [
            'new_users_this_month' => User::whereYear('created_at', $currentMonth->year)
                                          ->whereMonth('created_at', $currentMonth->month)
                                          ->count(),
            'new_jobs_this_month' => Job::whereYear('created_at', $currentMonth->year)
                                       ->whereMonth('created_at', $currentMonth->month)
                                       ->count(),
            'new_applications_this_month' => Application::whereYear('created_at', $currentMonth->year)
                                                      ->whereMonth('created_at', $currentMonth->month)
                                                      ->count(),
            'new_contracts_this_month' => Contrat::whereYear('created_at', $currentMonth->year)
                                                ->whereMonth('created_at', $currentMonth->month)
                                                ->count(),
        ];

        // Activités récentes
        $recentActivities = [
            'recent_applications' => Application::with(['candidate', 'job'])
                                             ->orderBy('created_at', 'desc')
                                             ->take(5)
                                             ->get(),
            'recent_jobs' => Job::with('employer')
                               ->orderBy('created_at', 'desc')
                               ->take(5)
                               ->get(),
            'recent_contracts' => Contrat::with(['employee', 'job', 'createdBy'])
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get(),
        ];

        // Données pour les graphiques
        $chartData = $this->getChartData();

        return view('dashboard.index', compact('stats', 'monthlyStats', 'recentActivities', 'chartData'));
    }

    public function analytics()
    {
        // Données analytiques avancées
        $analytics = [
            'user_growth' => $this->getUserGrowthData(),
            'job_categories' => $this->getJobCategoriesData(),
            'application_status' => $this->getApplicationStatusData(),
            'contract_status' => $this->getContractStatusData(),
            'monthly_revenue' => $this->getMonthlyRevenueData(),
            'top_employers' => $this->getTopEmployersData(),
            'geographic_data' => $this->getGeographicData(),
        ];

        return view('dashboard.analytics', compact('analytics'));
    }

    public function settings()
    {
        return view('dashboard.settings');
    }

    private function getChartData()
    {
        // Données des 12 derniers mois
        $months = [];
        $usersData = [];
        $jobsData = [];
        $applicationsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $usersData[] = User::whereYear('created_at', $date->year)
                              ->whereMonth('created_at', $date->month)
                              ->count();
                              
            $jobsData[] = Job::whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month)
                            ->count();
                            
            $applicationsData[] = Application::whereYear('created_at', $date->year)
                                            ->whereMonth('created_at', $date->month)
                                            ->count();
        }

        return [
            'labels' => $months,
            'users' => $usersData,
            'jobs' => $jobsData,
            'applications' => $applicationsData,
        ];
    }

    private function getUserGrowthData()
    {
        return User::selectRaw('date(created_at) as date, COUNT(*) as count')
                  ->where('created_at', '>=', Carbon::now()->subDays(30))
                  ->groupBy('date')
                  ->orderBy('date')
                  ->get();
    }

    private function getJobCategoriesData()
    {
        return DB::table('job_categories')
            ->join('jobs', 'job_categories.id', '=', 'jobs.job_category_id')
            ->select('job_categories.name as category', DB::raw('COUNT(jobs.id) as count'))
            ->groupBy('job_categories.name')
            ->get();
    }

    private function getApplicationStatusData()
    {
        return Application::selectRaw('status, COUNT(*) as count')
                         ->groupBy('status')
                         ->get();
    }

    private function getContractStatusData()
    {
        return Contrat::selectRaw('statut, COUNT(*) as count')
                     ->groupBy('statut')
                     ->get();
    }

    private function getMonthlyRevenueData()
    {
        // Simulate revenue data based on contracts
        // Using SQLite-compatible date functions
        return Contrat::selectRaw('strftime("%Y", created_at) as year, strftime("%m", created_at) as month, COUNT(*) * 50 as revenue')
                     ->where('statut', 'ACTIF')
                     ->groupBy('year', 'month')
                     ->orderBy('year')
                     ->orderBy('month')
                     ->get();
    }

    private function getTopEmployersData()
    {
        return User::where('type', 'employer')
                  ->withCount('postedJobs')
                  ->orderBy('posted_jobs_count', 'desc')
                  ->take(10)
                  ->get();
    }

    private function getGeographicData()
    {
        // TODO: Implement correct geographic data retrieval, e.g., from a 'location' column or a JSON field.
        // The 'city' column does not exist in the 'users' table.
        return collect();
    }
}

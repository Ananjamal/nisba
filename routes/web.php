<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

// Referral Redirection & Tracking
Route::get('/re/{unique_ref_id}', function ($unique_ref_id) {
    $referral = \App\Models\UserReferral::where('unique_ref_id', $unique_ref_id)->with('referralLink')->firstOrFail();

    \App\Models\Click::create([
        'user_id' => $referral->user_id,
        'referral_link_id' => $referral->referral_link_id,
        'ip_address' => request()->ip(),
        'user_agent' => request()->header('User-Agent'),
        'referer' => request()->header('referer'),
    ]);

    // Update stats
    $stats = $referral->user->stats()->firstOrCreate([], [
        'clicks_count' => 0,
        'active_clients_count' => 0,
        'total_contracts_value' => 0,
        'pending_commissions' => 0
    ]);
    $stats->increment('clicks_count');

    $url = $referral->referralLink->base_url;

    // Append referral ID if it's Daftra (example logic)
    if (str_contains($url, 'daftra.com')) {
        $connector = str_contains($url, '?') ? '&' : '?';
        $url .= $connector . 'nisba_ref=' . $referral->unique_ref_id;
    }

    return redirect()->away($url);
})->name('referral.redirect');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('affiliate/referral-links', 'affiliate.referral-links')->name('affiliate.referral-links');
    Route::view('affiliate/team', 'affiliate.team')->name('affiliate.team');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin|employee'])->prefix('admin')->group(function () {
    Volt::route('/', 'admin.dashboard')->name('admin.dashboard');
    Volt::route('/leads', 'admin.leads')->name('admin.leads');
    Volt::route('/leads/{lead}', 'admin.leads-show')->name('admin.leads.show');
    Volt::route('/affiliates', 'admin.affiliates')->name('admin.affiliates');
    Volt::route('/affiliates/{user}', 'admin.affiliates-show')->name('admin.affiliates.show');
    Route::get('/marketers/ranks', \App\Livewire\Admin\MarketerRanks::class)->name('admin.marketers.ranks');
    Volt::route('/payouts', 'admin.payouts')->name('admin.payouts');
    Volt::route('/settings', 'admin.settings')->name('admin.settings');

    // Roles & Permissions
    Route::middleware(['can:manage roles'])->group(function () {
        Volt::route('/roles', 'admin.roles.index')->name('admin.roles.index');
    });

    // Staff Management
    Route::middleware(['can:manage staff'])->group(function () {
        Volt::route('/staff', 'admin.staff.index')->name('admin.staff.index');
    });

    // Reports & Exports
    Route::get('/reports/export/excel', [\App\Http\Controllers\Admin\ReportController::class, 'exportLeadsExcel'])->name('admin.reports.export.excel');
    Route::get('/reports/export/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportLeadsPdf'])->name('admin.reports.export.pdf');
    Route::get('/reports/export/payouts/excel', [\App\Http\Controllers\Admin\ReportController::class, 'exportPayoutsExcel'])->name('admin.reports.payouts.excel');
    Route::get('/reports/export/payouts/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPayoutsPdf'])->name('admin.reports.payouts.pdf');
});

require __DIR__ . '/auth.php';

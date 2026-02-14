<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\LeadsExport;
use App\Models\Lead;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function exportLeadsExcel(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sector', 'affiliate', 'date_from', 'date_to']);
        return Excel::download(new LeadsExport($filters), 'leads-' . date('Y-m-d') . '.xlsx');
    }

    public function exportLeadsPdf(Request $request)
    {
        $query = Lead::query()->with('users');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('client_phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('affiliate')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->affiliate);
            });
        }

        $leads = $query->latest()->get();

        $pdf = Pdf::loadView('admin.reports.leads-pdf', compact('leads'));
        return $pdf->download('leads-' . date('Y-m-d') . '.pdf');
    }

    public function exportPayoutsExcel(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to']);
        return Excel::download(new \App\Exports\PayoutsExport($filters), 'payouts-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPayoutsPdf(Request $request)
    {
        $query = \App\Models\WithdrawalRequest::query()->with(['user', 'lead']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payouts = $query->latest()->get();

        $pdf = Pdf::loadView('admin.reports.payouts-pdf', compact('payouts'));
        return $pdf->download('payouts-' . date('Y-m-d') . '.pdf');
    }

    public function exportAffiliatesExcel(Request $request)
    {
        $filters = $request->only(['search', 'sector']);
        return Excel::download(new \App\Exports\AffiliatesExport($filters), 'affiliates-' . date('Y-m-d') . '.xlsx');
    }

    public function exportAffiliatesPdf(Request $request)
    {
        $query = \App\Models\User::where('role', 'affiliate')->withCount('leads');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        $users = $query->latest()->get();

        $pdf = Pdf::loadView('admin.reports.affiliates-pdf', compact('users'));
        return $pdf->download('affiliates-' . date('Y-m-d') . '.pdf');
    }
}

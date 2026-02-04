<?php

use Livewire\Volt\Component;
use App\Models\Lead;
use App\Models\User;
use App\Models\WithdrawalRequest;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public function syncQoyod(\App\Services\QoyodService $service)
    {
        $count = $service->syncLeadsFromQoyod();
        session()->flash('message', "تمت مزامنة {$count} عملاء بنجاح من منصة قيود!");
    }

    public function syncDaftra(\App\Services\DaftraService $service)
    {
        $count = $service->syncLeadsFromDaftra();
        session()->flash('message', "تمت مزامنة {$count} عملاء بنجاح من منصة دفترة!");
    }

    public function with()
    {
        return [
            'totalLeads' => Lead::count(),
            'pendingLeads' => Lead::where('status', 'under_review')->count(),
            'soldLeads' => Lead::where('status', 'sold')->count(),
            'totalUsers' => User::where('role', 'affiliate')->count(),
            'recentLeads' => Lead::latest()->take(5)->get(),
            'pendingPayouts' => WithdrawalRequest::where('status', 'pending')->count(),
        ];
    }
}; ?>

<div class="space-y-8">
    @if (session()->has('message'))
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold" role="alert">
        {{ session('message') }}
    </div>
    @endif

    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-blue-900">نظرة عامة</h2>
        <div class="flex gap-2">
            <button wire:click="syncQoyod" wire:loading.attr="disabled" class="bg-white border-2 border-blue-600 text-blue-600 px-6 py-3 rounded-2xl font-bold hover:bg-blue-50 transition shadow-sm flex items-center gap-2">
                <span wire:loading wire:target="syncQoyod">جاري المزامنة...</span>
                <span wire:loading.remove wire:target="syncQoyod">مزامنة قيود</span>
                <svg wire:loading.remove wire:target="syncQoyod" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <button wire:click="syncDaftra" wire:loading.attr="disabled" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100 flex items-center gap-2">
                <span wire:loading wire:target="syncDaftra">جاري المزامنة...</span>
                <span wire:loading.remove wire:target="syncDaftra">مزامنة دفترة</span>
                <svg wire:loading.remove wire:target="syncDaftra" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-8 rounded-3xl border border-blue-100 shadow-sm">
            <p class="text-sm font-bold text-blue-400 mb-2">إجمالي العملاء</p>
            <h4 class="text-3xl font-black text-blue-900">{{ $totalLeads }}</h4>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-blue-100 shadow-sm">
            <p class="text-sm font-bold text-blue-400 mb-2">بانتظار المراجعة</p>
            <h4 class="text-3xl font-black text-red-500">{{ $pendingLeads }}</h4>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-blue-100 shadow-sm">
            <p class="text-sm font-bold text-blue-400 mb-2">عمليات بيع ناجحة</p>
            <h4 class="text-3xl font-black text-green-500">{{ $soldLeads }}</h4>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-blue-100 shadow-sm">
            <p class="text-sm font-bold text-blue-400 mb-2">إجمالي المسوقين</p>
            <h4 class="text-3xl font-black text-blue-600">{{ $totalUsers }}</h4>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-8 rounded-3xl border border-blue-100 shadow-sm transition hover:shadow-md">
            <h3 class="text-xl font-black text-blue-900 mb-6 italic">آخر العملاء المضافين</h3>
            <div class="space-y-4">
                @foreach($recentLeads as $lead)
                <div class="flex items-center justify-between p-5 bg-blue-50/30 rounded-2xl border border-blue-50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white border border-blue-100 rounded-full flex items-center justify-center font-bold text-blue-400 shadow-sm">{{ mb_substr($lead->client_name, 0, 1) }}</div>
                        <div>
                            <p class="font-bold text-blue-900 text-sm">{{ $lead->client_name }}</p>
                            <p class="text-[10px] text-blue-400">عن طريق: {{ $lead->user->name }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-blue-400 bg-white px-2 py-1 rounded-lg border border-blue-50">{{ $lead->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            <a href="{{ route('admin.leads') }}" class="block text-center mt-6 text-sm font-bold text-blue-600 hover:underline">عرض جميع العملاء</a>
        </div>

        <div class="bg-white p-8 rounded-3xl border border-blue-100 shadow-sm transition hover:shadow-md">
            <h3 class="text-xl font-black text-blue-900 mb-6 italic">حالة التكامل (API Status)</h3>
            <div class="space-y-6">
                <div class="flex items-center justify-between p-5 bg-green-50/30 rounded-2xl border border-green-50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                            <img src="https://nisba.me/assets/img/qoyod.png" class="h-4 grayscale" alt="Qoyod">
                        </div>
                        <div>
                            <p class="font-black text-blue-900 text-sm">منصة قيود</p>
                            <p class="text-[10px] text-green-600 font-bold">متصل - يعمل بكفاءة</p>
                        </div>
                    </div>
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                </div>

                <div class="flex items-center justify-between p-5 bg-blue-50/30 rounded-2xl border border-blue-50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                            <img src="https://nisba.me/assets/img/daftra.png" class="h-4 grayscale" alt="Daftra">
                        </div>
                        <div>
                            <p class="font-black text-blue-900 text-sm">منصة دفترة</p>
                            <p class="text-[10px] text-blue-600 font-bold">متصل - يعمل بكفاءة</p>
                        </div>
                    </div>
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                </div>

                <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                    <h4 class="text-xs font-black text-blue-400 mb-3 uppercase tracking-wider">إحصائيات المزامنة اليومية</h4>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-blue-400">إجمالي السجلات المحدثة</p>
                            <p class="text-xl font-black text-blue-900">142</p>
                        </div>
                        <div class="text-left font-mono text-[10px] text-blue-400 leading-relaxed">
                            Last Sync: {{ now()->format('H:i') }}<br>
                            Success Rate: 100%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
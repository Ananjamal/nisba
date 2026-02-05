<x-app-layout>
    <div class="space-y-6">
        <!-- Stats Row -->
        <div class="animate-slide-up" style="animation-duration: 0.5s; animation-fill-mode: both;">
            <livewire:affiliate.stats />
        </div>

        <!-- Leads Table -->
        <div class="animate-slide-up" style="animation-duration: 0.5s; animation-delay: 0.1s; animation-fill-mode: both;">
            <livewire:affiliate.leads />
        </div>

        <!-- Referral Links -->
        <div class="animate-slide-up" style="animation-duration: 0.5s; animation-delay: 0.2s; animation-fill-mode: both;">
            <livewire:affiliate.referral-links />
        </div>

        <!-- Wallet & Transactions -->
        <div class="animate-slide-up" style="animation-duration: 0.5s; animation-delay: 0.3s; animation-fill-mode: both;">
            <livewire:affiliate.wallet />
        </div>
    </div>
</x-app-layout>
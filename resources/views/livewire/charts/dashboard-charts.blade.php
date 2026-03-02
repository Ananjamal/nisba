<div>
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">الرسوم البيانية والتقارير</h2>

        <!-- Filters Section -->
        <div class="space-y-4 mb-6">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="setDatePreset('today')" class="px-4 py-2 text-xs font-bold rounded-lg border bg-white text-gray-700 border-gray-200 hover:border-blue-300" style="{{ $datePreset === 'today' && !$useCustomDates ? 'background-color:#2563eb;border-color:#2563eb;color:#fff;' : '' }}">اليوم</button>
                <button type="button" wire:click="setDatePreset('last_7_days')" class="px-4 py-2 text-xs font-bold rounded-lg border bg-white text-gray-700 border-gray-200 hover:border-blue-300" style="{{ $datePreset === 'last_7_days' && !$useCustomDates ? 'background-color:#2563eb;border-color:#2563eb;color:#fff;' : '' }}">آخر 7 أيام</button>
                <button type="button" wire:click="setDatePreset('last_30_days')" class="px-4 py-2 text-xs font-bold rounded-lg border bg-white text-gray-700 border-gray-200 hover:border-blue-300" style="{{ $datePreset === 'last_30_days' && !$useCustomDates ? 'background-color:#2563eb;border-color:#2563eb;color:#fff;' : '' }}">آخر 30 يوم</button>
                <button type="button" wire:click="setDatePreset('this_month')" class="px-4 py-2 text-xs font-bold rounded-lg border bg-white text-gray-700 border-gray-200 hover:border-blue-300" style="{{ $datePreset === 'this_month' && !$useCustomDates ? 'background-color:#2563eb;border-color:#2563eb;color:#fff;' : '' }}">هذا الشهر</button>
                <button type="button" wire:click="setDatePreset('last_month')" class="px-4 py-2 text-xs font-bold rounded-lg border bg-white text-gray-700 border-gray-200 hover:border-blue-300" style="{{ $datePreset === 'last_month' && !$useCustomDates ? 'background-color:#2563eb;border-color:#2563eb;color:#fff;' : '' }}">الشهر السابق</button>
                <button type="button" wire:click="enableCustomDates" class="px-4 py-2 text-xs font-bold rounded-lg border bg-white text-gray-700 border-gray-200 hover:border-blue-300" style="{{ $useCustomDates ? 'background-color:#2563eb;border-color:#2563eb;color:#fff;' : '' }}">مخصص</button>
            </div>

            @if($useCustomDates)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                    <input type="date" wire:model.live="startDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                    <input type="date" wire:model.live="endDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الرسم البياني</label>
                    <select wire:model.live="chartType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="revenue">الإيرادات</option>
                        <option value="leads">العملاء المحتملون</option>
                        <option value="users">المسوقون</option>
                        <option value="withdrawals">السحوبات</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="enableComparison" class="ml-2 rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">تفعيل المقارنة</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Comparison Dates -->
        @if($enableComparison)
        <div class="mb-6 p-4 bg-gray-50 rounded-lg space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="setComparisonToPreviousPeriod" class="px-4 py-2 text-xs font-bold rounded-lg border bg-white text-gray-700 border-gray-200 hover:border-green-300">مقارنة بالفترة السابقة</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ المقارنة</label>
                    <input type="date" wire:model.live="comparisonStartDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ المقارنة</label>
                    <input type="date" wire:model.live="comparisonEndDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
        </div>
        @endif

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">الإيرادات</p>
                        <p class="text-2xl font-bold text-blue-900">{{ number_format($summaryStats['revenue'], 2) }} ريال</p>
                        @if(isset($summaryStats['revenue_change']))
                        <p class="text-sm {{ $summaryStats['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $summaryStats['revenue_change'] >= 0 ? '↑' : '↓' }} {{ abs(number_format($summaryStats['revenue_change'], 1)) }}%
                        </p>
                        @endif
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-amber-600 font-medium">العملاء المحتملون</p>
                        <p class="text-2xl font-bold text-amber-900">{{ number_format($summaryStats['leads']) }}</p>
                        @if(isset($summaryStats['leads_change']))
                        <p class="text-sm {{ $summaryStats['leads_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $summaryStats['leads_change'] >= 0 ? '↑' : '↓' }} {{ abs(number_format($summaryStats['leads_change'], 1)) }}%
                        </p>
                        @endif
                    </div>
                    <div class="bg-amber-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">المسوقون الجدد</p>
                        <p class="text-2xl font-bold text-purple-900">{{ number_format($summaryStats['users']) }}</p>
                        @if(isset($summaryStats['users_change']))
                        <p class="text-sm {{ $summaryStats['users_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $summaryStats['users_change'] >= 0 ? '↑' : '↓' }} {{ abs(number_format($summaryStats['users_change'], 1)) }}%
                        </p>
                        @endif
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-teal-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-teal-600 font-medium">السحوبات المعتمدة</p>
                        <p class="text-2xl font-bold text-teal-900">{{ number_format($summaryStats['withdrawals'], 2) }} ريال</p>
                        @if(isset($summaryStats['withdrawals_change']))
                        <p class="text-sm {{ $summaryStats['withdrawals_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $summaryStats['withdrawals_change'] >= 0 ? '↑' : '↓' }} {{ abs(number_format($summaryStats['withdrawals_change'], 1)) }}%
                        </p>
                        @endif
                    </div>
                    <div class="bg-teal-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-gray-50 rounded-lg p-4" wire:ignore>
            <canvas id="mainChart" width="400" height="200"></canvas>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            let mainChart = null;
            const ctx = document.getElementById('mainChart').getContext('2d');

            Livewire.hook('message.processed', (message, component) => {
                if (component.name === 'charts.dashboard-charts') {
                    updateChart();
                }
            });

            function updateChart() {
                const chartData = @json($chartData);

                if (mainChart) {
                    mainChart.destroy();
                }

                mainChart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: getChartTitle()
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString() + ' ريال';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function getChartTitle() {
                const chartType = '{{ $chartType }}';
                const titles = {
                    'revenue': 'مخطط الإيرادات',
                    'leads': 'مخطط العملاء المحتملين',
                    'users': 'مخطط المسوقين الجدد',
                    'withdrawals': 'مخطط السحوبات المعتمدة'
                };
                return titles[chartType] || 'مخطط البيانات';
            }

            // Initial chart render
            updateChart();
        });
    </script>
    @endpush
</div>
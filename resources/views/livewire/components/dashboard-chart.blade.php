<div class="chart-container-glass p-6 rounded-[2rem] transition-all hover:shadow-lg" x-data="{
    chart: null,
    init() {
        const options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Cairo, sans-serif',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            series: @js($initialData['series']),
            labels: @js($initialData['labels']),
            colors: @js($initialData['colors'] ?? ['#16a34a']),
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 4,
                lineCap: 'round'
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { left: 10, right: 10 }
            },
            markers: {
                size: 0,
                hover: {
                    size: 6,
                    sizeOffset: 3
                }
            },
            xaxis: {
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#64748b', fontWeight: 600, fontSize: '11px' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748b', fontWeight: 600, fontSize: '11px' }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'right',
                fontFamily: 'Cairo',
                fontWeight: 600,
                markers: { radius: 12 },
                itemMargin: { horizontal: 10 }
            },
            tooltip: {
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                    const value = series[seriesIndex][dataPointIndex];
                    const label = w.globals.labels[dataPointIndex];
                    const title = w.config.series[seriesIndex].name;
                    return `
                        <div class='apexcharts-tooltip-title'>${label}</div>
                        <div class='chart-tooltip-content'>
                            <span class='chart-tooltip-label'>${title}</span>
                            <span class='chart-tooltip-value'>${value.toLocaleString()} ${ '{{ $type }}' === 'revenue' ? 'ر.س' : '' }</span>
                        </div>
                    `;
                }
            }
        };

        this.chart = new ApexCharts(this.$refs.chart, options);
        this.chart.render();

        Livewire.on('refreshChart-{{ $chartId }}', (data) => {
            this.chart.updateOptions({
                series: data[0].series,
                labels: data[0].labels,
                colors: data[0].colors ? data[0].colors : undefined,
            });
        });

        Livewire.on('changeChartType-{{ $chartId }}', (type) => {
            const isStep = type[0] === 'step';
            this.chart.updateOptions({
                chart: { type: isStep ? 'line' : type[0] },
                stroke: {
                    curve: isStep ? 'stepline' : 'smooth',
                    width: (type[0] === 'bar') ? 0 : 4
                },
                fill: {
                    type: type[0] === 'area' ? 'gradient' : 'solid',
                },
                markers: {
                    size: (type[0] === 'line' || type[0] === 'step') ? 4 : 0
                }
            });
        });
    }
}">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-2 h-8 bg-green-500 rounded-full"></div>
            <h3 class="text-xl font-extrabold text-[#14532d]">{{ $chartTitle }}</h3>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Chart Type Selector -->
            <div class="flex bg-gray-100/50 p-1 rounded-xl border border-gray-100/50 backdrop-blur-sm">
                @foreach([
                'area' => 'M4 19h16M4 15l4-4 4 4 4-4 4 4',
                'bar' => 'M6 20V10m6 10V4m6 16v-6',
                'line' => 'M4 19L10 13L15 18L20 9',
                'step' => 'M4 19h5v-7h5v-7h6'
                ] as $type => $icon)
                <button wire:click="setChartType('{{ $type }}')"
                    class="p-2 rounded-lg transition-all duration-300 {{ $chartType === $type ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-green-600' }}"
                    title="{{ $type === 'area' ? 'مساحة' : ($type === 'bar' ? 'أعمدة' : ($type === 'line' ? 'خطي' : 'متدرج')) }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path>
                    </svg>
                </button>
                @endforeach
            </div>

            <!-- Period Selector -->
            <div class="flex bg-gray-100/50 p-1.5 rounded-2xl border border-gray-100/50 backdrop-blur-sm">
                @foreach(['day' => 'يومي', 'week' => 'أسبوعي', 'month' => 'شهري', 'year' => 'سنوي'] as $key => $label)
                <button wire:click="setPeriod('{{ $key }}')"
                    class="px-5 py-2 text-xs font-bold rounded-xl transition-all duration-300 {{ $period === $key ? 'bg-white text-green-600 shadow-md transform scale-105' : 'text-gray-500 hover:text-green-600' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mb-8">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @if($period === 'day')
                <button type="button" wire:click="setDatePreset('today')" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $datePreset === 'today' && !$useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">اليوم</button>
                <button type="button" wire:click="setDatePreset('yesterday')" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $datePreset === 'yesterday' && !$useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">أمس</button>
            @elseif($period === 'week')
                <button type="button" wire:click="setDatePreset('last_7_days')" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $datePreset === 'last_7_days' && !$useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">آخر 7 أيام</button>
            @elseif($period === 'year')
                <button type="button" wire:click="setDatePreset('this_year')" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $datePreset === 'this_year' && !$useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">هذه السنة</button>
            @else
                <button type="button" wire:click="setDatePreset('this_month')" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $datePreset === 'this_month' && !$useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">هذا الشهر</button>
                <button type="button" wire:click="setDatePreset('last_month')" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $datePreset === 'last_month' && !$useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">الشهر السابق</button>
                <button type="button" wire:click="setDatePreset('last_30_days')" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $datePreset === 'last_30_days' && !$useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">آخر 30 يوم</button>
            @endif
            <button type="button" wire:click="enableCustomDates" class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-300 bg-gray-100/50 text-gray-600 hover:text-green-600" style="{{ $useCustomDates ? 'background-color:#16a34a;color:#fff;border-color:#16a34a;' : '' }}">مخصص</button>
        </div>

        @if($useCustomDates)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">من</label>
                    <input type="date" wire:model.defer="startDate" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">إلى</label>
                    <input type="date" wire:model.defer="endDate" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>
                <div class="flex items-end">
                    <button type="button" wire:click="applyDateFilter" class="w-full px-4 py-2 text-xs font-bold rounded-xl bg-green-600 text-white hover:bg-green-700 transition-colors">تطبيق</button>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-2">
            <input id="admin-cmp-{{ $chartId }}" type="checkbox" wire:model.live="enableComparison" class="rounded text-green-600 focus:ring-green-500">
            <label for="admin-cmp-{{ $chartId }}" class="text-xs font-bold text-gray-700">مقارنة فترتين</label>
        </div>

        @if($enableComparison)
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <button type="button" wire:click="setComparisonToPreviousPeriod" class="px-4 py-2 text-xs font-bold rounded-xl bg-gray-100/50 text-gray-600 hover:text-green-600 transition-all">مقارنة بالفترة السابقة</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">من (المقارنة)</label>
                    <input type="date" wire:model.defer="comparisonStartDate" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">إلى (المقارنة)</label>
                    <input type="date" wire:model.defer="comparisonEndDate" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>
                <div class="flex items-end">
                    <button type="button" wire:click="applyDateFilter" class="w-full px-4 py-2 text-xs font-bold rounded-xl bg-white border border-gray-200 text-gray-700 hover:border-green-500 hover:text-green-600 transition-colors">تحديث المقارنة</button>
                </div>
            </div>
        @endif
    </div>

    <div wire:ignore x-ref="chart" class="min-h-[350px]"></div>
</div>
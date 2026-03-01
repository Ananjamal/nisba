<div class="relative w-full h-full bg-white rounded-[2rem]"
    x-data="{
    chart: null,
    init() {
        const options = {
            chart: {
                type: '{{ $chartType }}',
                height: 400,
                fontFamily: 'Cairo, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            series: @js($initialData['series']),
            labels: @js($initialData['labels']),
            colors: @js($initialData['colors'] ?? [$colorInfo[0]]),
            fill: {
                type: '{{ $chartType === 'area' ? 'gradient' : 'solid' }}',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.5,
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
                borderColor: '#f8fafc',
                strokeDashArray: 0,
                padding: { top: 20, right: 20, bottom: 0, left: 20 },
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } }
            },
            markers: {
                size: 0,
                colors: ['#fff'],
                strokeColors: '{{ $colorInfo[0] }}',
                strokeWidth: 3,
                hover: {
                    size: 8,
                }
            },
            xaxis: {
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#64748b', fontSize: '12px', fontFamily: 'Cairo', fontWeight: 600 },
                    offsetY: 5
                },
                tooltip: { enabled: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748b', fontSize: '12px', fontFamily: 'Cairo', fontWeight: 600 },
                    formatter: function(val) { return val.toLocaleString(); }
                },
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'right',
                fontFamily: 'Cairo',
                fontSize: '14px',
                fontWeight: 800,
                labels: {
                    colors: '#1e293b'
                },
                markers: {
                    width: 12,
                    height: 12,
                    radius: 6,
                    offsetX: -5
                },
                itemMargin: {
                    horizontal: 20,
                    vertical: 10
                }
            },
            tooltip: {
                theme: 'light',
                shared: true,
                intersect: false,
                y: {
                    formatter: function (val) {
                        return val.toLocaleString();
                    }
                },
                style: {
                    fontSize: '13px',
                    fontFamily: 'Cairo',
                },
                marker: { show: true },
            }
        };

        this.chart = new ApexCharts(this.$refs.chart, options);
        this.chart.render();

        Livewire.on('refreshAffiliateChart-{{ $chartId }}', (data) => {
            const chartData = data[0];
            this.chart.updateOptions({
                chart: { type: chartData.type },
                series: chartData.series,
                labels: chartData.labels,
                colors: chartData.colors ? chartData.colors : undefined,
                fill: {
                    type: chartData.type === 'area' ? 'gradient' : 'solid'
                }
            });
        });
    }
}">
    <!-- Controls Sub-Header -->
    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 border-b border-slate-50">
        <div class="flex items-center gap-6">
            <!-- Legend Label Placeholder (Legend is rendered by Apex, but we can add a label if needed) -->
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full" style="background-color: {{ $colorInfo[0] }}"></div>
                <span class="text-xs font-black text-slate-900">{{ $chartTitle ?: 'البيانات المعروضة' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Period Toggle -->
            <div class="flex bg-slate-100 p-1 rounded-2xl">
                @foreach(['week' => 'أسبوع', 'month' => 'شهر', 'year' => 'سنة'] as $key => $label)
                <button wire:click="setPeriod('{{ $key }}')"
                    class="px-4 py-1.5 text-xs font-black rounded-xl transition-all duration-300 {{ $period === $key ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <div class="w-px h-6 bg-slate-200 mx-1"></div>

            <!-- Type Toggle -->
            <div class="flex bg-slate-100 p-1 rounded-2xl">
                @foreach(['area' => 'مساحة', 'line' => 'خطي', 'bar' => 'أعمدة'] as $key => $label)
                <button wire:click="setChartType('{{ $key }}')"
                    class="px-4 py-1.5 text-xs font-black rounded-xl transition-all duration-300 {{ $chartType === $key ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Date Filters -->
    <div class="px-6 py-4 border-b border-slate-50">
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center gap-2">
                @if($period === 'week')
                    <button type="button" wire:click="setDatePreset('today')" class="btn btn-outline btn-sm" style="{{ $datePreset === 'today' && !$useCustomDates ? 'background-color: '.$colorInfo[0].';border-color: '.$colorInfo[0].';color: #fff;' : '' }}">اليوم</button>
                    <button type="button" wire:click="setDatePreset('last_7_days')" class="btn btn-outline btn-sm" style="{{ $datePreset === 'last_7_days' && !$useCustomDates ? 'background-color: '.$colorInfo[0].';border-color: '.$colorInfo[0].';color: #fff;' : '' }}">آخر 7 أيام</button>
                @elseif($period === 'year')
                    <button type="button" wire:click="setDatePreset('this_year')" class="btn btn-outline btn-sm" style="{{ $datePreset === 'this_year' && !$useCustomDates ? 'background-color: '.$colorInfo[0].';border-color: '.$colorInfo[0].';color: #fff;' : '' }}">هذه السنة</button>
                @else
                    <button type="button" wire:click="setDatePreset('this_month')" class="btn btn-outline btn-sm" style="{{ $datePreset === 'this_month' && !$useCustomDates ? 'background-color: '.$colorInfo[0].';border-color: '.$colorInfo[0].';color: #fff;' : '' }}">هذا الشهر</button>
                    <button type="button" wire:click="setDatePreset('last_month')" class="btn btn-outline btn-sm" style="{{ $datePreset === 'last_month' && !$useCustomDates ? 'background-color: '.$colorInfo[0].';border-color: '.$colorInfo[0].';color: #fff;' : '' }}">الشهر السابق</button>
                    <button type="button" wire:click="setDatePreset('last_30_days')" class="btn btn-outline btn-sm" style="{{ $datePreset === 'last_30_days' && !$useCustomDates ? 'background-color: '.$colorInfo[0].';border-color: '.$colorInfo[0].';color: #fff;' : '' }}">آخر 30 يوم</button>
                @endif
                <button type="button" wire:click="enableCustomDates" class="btn btn-outline btn-sm" style="{{ $useCustomDates ? 'background-color: '.$colorInfo[0].';border-color: '.$colorInfo[0].';color: #fff;' : '' }}">مخصص</button>
            </div>

            @if($useCustomDates)
                <div class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-black text-slate-500 mb-1">من</label>
                        <input type="date" wire:model.defer="startDate" class="form-input" />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-black text-slate-500 mb-1">إلى</label>
                        <input type="date" wire:model.defer="endDate" class="form-input" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="applyDateFilter" class="btn btn-primary btn-sm">تطبيق</button>
                    </div>
                </div>
            @endif

            <div class="flex items-start gap-3">
                <input id="cmp-{{ $chartId }}" type="checkbox" wire:model.live="enableComparison" class="mt-1">
                <label for="cmp-{{ $chartId }}" class="text-sm font-black text-slate-700">مقارنة فترتين</label>
            </div>

            @if($enableComparison)
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="setComparisonToPreviousPeriod" class="btn btn-outline btn-sm">مقارنة بالفترة السابقة</button>
                </div>
                <div class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-black text-slate-500 mb-1">من (المقارنة)</label>
                        <input type="date" wire:model.defer="comparisonStartDate" class="form-input" />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-black text-slate-500 mb-1">إلى (المقارنة)</label>
                        <input type="date" wire:model.defer="comparisonEndDate" class="form-input" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="applyDateFilter" class="btn btn-outline btn-sm">تحديث المقارنة</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Chart Body -->
    <div wire:ignore class="relative p-4 md:p-6 min-h-[420px]">
        <div x-ref="chart" class="min-h-[420px]"></div>
        <div x-show="!chart" class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm z-50">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-slate-100 border-t-{{ $chartId === 'affiliate-sales-tab' ? 'blue' : ($chartId === 'affiliate-commissions-tab' ? 'emerald' : 'indigo') }}-500 rounded-full animate-spin"></div>
                <p class="text-sm font-bold text-slate-400">جاري تحليل البيانات...</p>
            </div>
        </div>
    </div>
</div>
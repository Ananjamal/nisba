<div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm" x-data="{
    chart: null,
    init() {
        const options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Cairo, sans-serif'
            },
            series: @js($initialData['series']),
            labels: @js($initialData['labels']),
            colors: ['#0f172a'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
            },
            xaxis: {
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#64748b', fontWeight: 600 } }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontWeight: 600 } }
            },
            tooltip: {
                theme: 'light',
                x: { show: true },
                y: { formatter: (val) => val.toLocaleString() + ( '{{ $type }}' === 'revenue' ? ' ر.س' : '') }
            }
        };

        this.chart = new ApexCharts(this.$refs.chart, options);
        this.chart.render();

        Livewire.on('refreshChart-{{ $chartId }}', (data) => {
            this.chart.updateOptions({
                series: data[0].series,
                labels: data[0].labels
            });
        });
    }
}">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-900">{{ $chartTitle }}</h3>
        <div class="flex bg-gray-50 p-1 rounded-xl border border-gray-100">
            @foreach(['day' => 'يومي', 'week' => 'أسبوعي', 'month' => 'شهري', 'year' => 'سنوي'] as $key => $label)
            <button wire:click="setPeriod('{{ $key }}')"
                class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all {{ $period === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <div wire:ignore x-ref="chart" class="min-h-[350px]"></div>
</div>
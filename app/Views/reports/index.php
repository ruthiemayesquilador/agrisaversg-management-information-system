<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    /* ── Page Header ── */
    .rpt-page-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
    }
    .rpt-page-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #FF8C42 0%, #FF6820 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(255,140,66,0.30);
    }
    .rpt-page-title {
        font-size: 26px;
        font-weight: 700;
        color: #2a2a2a;
        line-height: 1.1;
    }
    .rpt-page-subtitle {
        font-size: 13px;
        color: #888;
        margin-top: 2px;
    }

    /* ── Summary Stat Cards ── */
    .rpt-stat-card {
        border: none;
        border-radius: 14px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .rpt-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.09);
    }
    .rpt-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .rpt-stat-icon.orange  { background: #fff3ea; color: #FF8C42; }
    .rpt-stat-icon.blue    { background: #e8f3ff; color: #3b82f6; }
    .rpt-stat-icon.green   { background: #e6faf3; color: #22c55e; }
    .rpt-stat-icon.purple  { background: #f3eeff; color: #8b5cf6; }
    .rpt-stat-label {
        font-size: 12px;
        color: #888;
        font-weight: 500;
        margin-bottom: 3px;
    }
    .rpt-stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1.1;
    }

    /* ── Chart Cards ── */
    .rpt-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        background: #fff;
        overflow: hidden;
    }
    .rpt-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 22px 0 22px;
    }
    .rpt-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }
    .rpt-card-icon.orange  { background: #fff3ea; color: #FF8C42; }
    .rpt-card-icon.blue    { background: #e8f3ff; color: #3b82f6; }
    .rpt-card-icon.green   { background: #e6faf3; color: #22c55e; }
    .rpt-card-icon.purple  { background: #f3eeff; color: #8b5cf6; }
    .rpt-card-title {
        font-size: 15px;
        font-weight: 650;
        color: #1a1a1a;
        margin: 0;
        line-height: 1.2;
    }
    .rpt-card-sub {
        font-size: 11.5px;
        color: #aaa;
        margin: 0;
    }
    .rpt-card-body {
        padding: 14px 22px 20px 22px;
    }

    /* ── Category List ── */
    .rpt-cat-list {
        max-height: 240px;
        overflow-y: auto;
    }
    .rpt-cat-list::-webkit-scrollbar { width: 4px; }
    .rpt-cat-list::-webkit-scrollbar-track { background: transparent; }
    .rpt-cat-list::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 4px; }
    .rpt-cat-list .list-group-item {
        border-left: 0;
        border-right: 0;
        font-size: 13px;
        color: #444;
        padding: 0.45rem 0;
        display: flex;
        align-items: center;
        background: transparent;
        border-radius: 0 !important;
        transition: color 0.15s;
        cursor: pointer;
        text-decoration: none;
    }
    .rpt-cat-list .list-group-item:first-child { border-top: 0; }
    .rpt-cat-list .list-group-item:last-child  { border-bottom: 0; }
    .rpt-cat-list .list-group-item:hover { color: #FF8C42; background: transparent; }

    .rpt-cat-active {
        color: #FF8C42 !important;
        font-weight: 650;
    }
    .rpt-cat-indicator {
        width: 4px;
        height: 26px;
        border-radius: 3px;
        background: transparent;
        margin-right: 10px;
        flex-shrink: 0;
        transition: background 0.15s;
    }
    .rpt-cat-active .rpt-cat-indicator { background: #FF8C42; }

    .rpt-badge {
        background: #f0f0f0;
        color: #666;
        font-size: 11px;
        font-weight: 500;
        border-radius: 20px;
        padding: 2px 9px;
        transition: background 0.15s, color 0.15s;
    }
    .rpt-cat-active .rpt-badge {
        background: #FF8C42;
        color: #fff;
    }

    /* ── Divider rule between sections ── */
    .rpt-section-divider {
        border: none;
        border-top: 1px solid #f0f0f0;
        margin: 0 22px;
    }

    /* ── Stock card highlight border ── */
    .rpt-card-stock {
        border: 2px solid #7c3aed !important;
        box-shadow: 0 2px 16px rgba(124,58,237,0.13) !important;
    }

    /* ── Sales filter toolbar ── */
    .rpt-filter-btn {
        font-size: 11.5px;
        padding: 3px 10px;
        border-color: #e0e0e0;
        color: #666;
    }
    .rpt-filter-btn:hover,
    .rpt-filter-btn.active {
        background: #FF8C42 !important;
        border-color: #FF8C42 !important;
        color: #fff !important;
    }
    .btn-rpt-apply {
        background: #FF8C42;
        border-color: #FF8C42;
        color: #fff;
        font-size: 11.5px;
    }
    .btn-rpt-apply:hover { background: #e8762f; border-color: #e8762f; color: #fff; }
</style>

<div class="container-fluid px-4 py-4">

    <!-- ── Sales Bar Graph ── -->
    <div class="rpt-card mb-4">
        <div class="rpt-card-header d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="rpt-card-icon orange"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <p class="rpt-card-title">Sales Bar Graph</p>
                    <p class="rpt-card-sub" id="sales-sub">Cash sales + receivables — last 12 months</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap mt-1 pe-1">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary rpt-filter-btn active" data-filter="monthly">12 Months</button>
                    <button type="button" class="btn btn-outline-secondary rpt-filter-btn" data-filter="last-month">Last Month</button>
                    <button type="button" class="btn btn-outline-secondary rpt-filter-btn" data-filter="daily">Daily</button>
                    <button type="button" class="btn btn-outline-secondary rpt-filter-btn" data-filter="custom">Custom</button>
                </div>
                <div id="sales-custom-range" class="d-none d-flex align-items-center gap-2 flex-wrap">
                    <input type="date" id="sales-from" class="form-control form-control-sm" style="width:145px;">
                    <span class="text-muted small">–</span>
                    <input type="date" id="sales-to" class="form-control form-control-sm" style="width:145px;">
                    <button type="button" class="btn btn-sm btn-rpt-apply" id="sales-apply">Apply</button>
                </div>
            </div>
        </div>
        <hr class="rpt-section-divider mt-3">
        <div class="rpt-card-body">
            <div id="chart-sales"></div>
            <p id="chart-sales-empty" class="text-center text-muted py-5" style="display:none;"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No sales data for the selected period.</p>
            <div id="chart-sales-loading" class="text-center py-5" style="display:none;"><div class="spinner-border text-secondary" role="status" style="width:1.5rem;height:1.5rem;"></div></div>
        </div>
    </div>

    <!-- ── Categories + Pie Chart ── -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="rpt-card h-100">
                <div class="rpt-card-header">
                    <div class="rpt-card-icon green"><i class="bi bi-tags-fill"></i></div>
                    <div>
                        <p class="rpt-card-title">Categories</p>
                        <p class="rpt-card-sub">Click to filter the chart</p>
                    </div>
                </div>
                <hr class="rpt-section-divider mt-3">
                <div class="rpt-card-body pt-2">
                    <?php if (empty($categories)): ?>
                        <p class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No categories found.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush rpt-cat-list">
                            <a href="#" class="list-group-item rpt-cat-item rpt-cat-active" data-cat-id="all">
                                <span class="rpt-cat-indicator"></span>
                                All Categories
                                <span class="badge rpt-badge ms-auto"><?= array_sum(array_column($categories, 'product_count')) ?></span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="#" class="list-group-item rpt-cat-item" data-cat-id="<?= esc((string) ($cat['category_id'] ?? '')) ?>">
                                    <span class="rpt-cat-indicator"></span>
                                    <?= esc((string) ($cat['category_name'] ?? '')) ?>
                                    <span class="badge rpt-badge ms-auto"><?= (int)($cat['product_count'] ?? 0) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="rpt-card h-100">
                <div class="rpt-card-header">
                    <div class="rpt-card-icon purple"><i class="bi bi-pie-chart-fill"></i></div>
                    <div>
                        <p class="rpt-card-title">Product Pie Chart &nbsp;<span id="pie-subtitle" style="font-size:12px;font-weight:400;color:#aaa;">(All Categories)</span></p>
                        <p class="rpt-card-sub">Products by stock quantity</p>
                    </div>
                </div>
                <hr class="rpt-section-divider mt-3">
                <div class="rpt-card-body">
                    <div id="chart-pie"></div>
                    <p id="pie-no-data" class="text-center text-muted py-5" style="display:none;"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No product data for this category.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Stock Bar Graph ── -->
    <div class="rpt-card rpt-card-stock mb-4">
        <div class="rpt-card-header">
            <div class="rpt-card-icon blue"><i class="bi bi-boxes"></i></div>
            <div>
                <p class="rpt-card-title">Stock Bar Graph</p>
                <p class="rpt-card-sub">Inventory breakdown — top 10 items</p>
            </div>
        </div>
        <hr class="rpt-section-divider mt-3">
        <div class="rpt-card-body">
            <?php if (empty($stockLabels)): ?>
                <p class="text-center text-muted py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No stock data available.</p>
            <?php else: ?>
                <div id="chart-stocks"></div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── PHP data injected as JSON ──
    var salesLabels   = <?= json_encode($salesLabels ?? []) ?>;
    var salesValues   = <?= json_encode($salesValues ?? []) ?>;
    var catProducts   = <?= json_encode($catProducts  ?? []) ?>;
    var allCategories = <?= json_encode(array_values($categories ?? [])) ?>;
    var stockLabels   = <?= json_encode($stockLabels   ?? []) ?>;
    var stockItemsIn  = <?= json_encode($stockItemsIn  ?? []) ?>;
    var stockItemsOut = <?= json_encode($stockItemsOut ?? []) ?>;
    var stockValues   = <?= json_encode($stockValues   ?? []) ?>;

    // Build "All" overview: category name → total_stock
    var allLabels = allCategories.map(function(c){ return c.category_name; });
    var allSeries = allCategories.map(function(c){ return parseInt(c.total_stock) || 0; });

    // ── Brand palette ──
    var brandColors = ['#FF8C42','#3b82f6','#22c55e','#8b5cf6','#f59e0b','#06b6d4','#ec4899','#ef4444','#64748b','#10b981'];

    // ── 1. Sales Area Chart with filter ──
    var salesChart = null;

    function renderSalesChart(labels, values) {
        var emptyEl = document.getElementById('chart-sales-empty');
        var loadEl  = document.getElementById('chart-sales-loading');
        var chartEl = document.getElementById('chart-sales');
        if (loadEl) loadEl.style.display = 'none';
        if (!labels || labels.length === 0) {
            if (emptyEl) emptyEl.style.display = '';
            chartEl.innerHTML = '';
            if (salesChart) { salesChart.destroy(); salesChart = null; }
            return;
        }
        if (emptyEl) emptyEl.style.display = 'none';
        var opts = {
            series: [{ name: 'Sales', data: values }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: { enabled: true, easing: 'easeinout', speed: 600 }
            },
            colors: ['#60a5fa'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    colorStops: [
                        { offset: 0,   color: '#bfdbfe', opacity: 0.85 },
                        { offset: 100, color: '#eff6ff', opacity: 0.15 }
                    ]
                }
            },
            stroke: { curve: 'smooth', width: 1.8, colors: ['#93c5fd'] },
            dataLabels: { enabled: false },
            xaxis: {
                categories: labels,
                labels: {
                    style: { fontSize: '11px', colors: '#9ca3af' },
                    rotate: 0,
                    trim: true
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
                crosshairs: { show: true, stroke: { color: '#d1d5db', width: 1, dashArray: 3 } }
            },
            yaxis: {
                min: 0,
                tickAmount: 5,
                labels: {
                    style: { fontSize: '11px', colors: '#9ca3af' },
                    formatter: function(val){ return '₱' + val.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}); }
                }
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 3,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 0, right: 16, bottom: 0, left: 10 }
            },
            markers: { size: 0 },
            tooltip: {
                theme: 'light',
                x: { show: true },
                y: { formatter: function(val){ return '₱' + val.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}); } }
            }
        };
        if (salesChart) {
            salesChart.updateOptions(opts, true, true);
        } else {
            salesChart = new ApexCharts(chartEl, opts);
            salesChart.render();
        }
    }

    function filterSales(type, from, to, subtitle) {
        var loadEl  = document.getElementById('chart-sales-loading');
        var emptyEl = document.getElementById('chart-sales-empty');
        var subEl   = document.getElementById('sales-sub');
        if (loadEl)  loadEl.style.display = '';
        if (emptyEl) emptyEl.style.display = 'none';
        var url = '<?= base_url('reports/sales-data') ?>?type=' + encodeURIComponent(type);
        if (from) url += '&from=' + encodeURIComponent(from);
        if (to)   url += '&to='   + encodeURIComponent(to);
        fetch(url)
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (subEl && subtitle) subEl.textContent = subtitle;
                renderSalesChart(data.labels, data.values);
            })
            .catch(function(){
                if (loadEl) loadEl.style.display = 'none';
            });
    }

    // Initial render with PHP-injected data (no extra request needed)
    renderSalesChart(salesLabels, salesValues);

    // Filter button listeners
    document.querySelectorAll('.rpt-filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.rpt-filter-btn').forEach(function(b){ b.classList.remove('active'); });
            this.classList.add('active');
            var customRange = document.getElementById('sales-custom-range');
            var filter = this.dataset.filter;
            if (filter === 'custom') {
                if (customRange) customRange.classList.remove('d-none');
                return; // Wait for Apply click
            }
            if (customRange) customRange.classList.add('d-none');
            if (filter === 'monthly') {
                filterSales('monthly', null, null, 'Cash sales + receivables — last 12 months');
            } else if (filter === 'last-month') {
                var now  = new Date();
                var fmt  = function(d){ return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); };
                var from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                var to   = new Date(now.getFullYear(), now.getMonth(), 0);
                var monthName = from.toLocaleString('default', { month: 'long', year: 'numeric' });
                filterSales('daily', fmt(from), fmt(to), 'Cash sales + receivables — ' + monthName + ' (daily)');
            } else if (filter === 'daily') {
                var now  = new Date();
                var fmt  = function(d){ return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); };
                var from = new Date(); from.setDate(from.getDate() - 29);
                filterSales('daily', fmt(from), fmt(now), 'Cash sales + receivables — last 30 days (daily)');
            }
        });
    });

    var applyBtn = document.getElementById('sales-apply');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            var from = document.getElementById('sales-from').value;
            var to   = document.getElementById('sales-to').value;
            if (!from || !to) { alert('Please select both a start and end date.'); return; }
            if (from > to)    { alert('Start date must be before end date.'); return; }
            filterSales('daily', from, to, 'Cash sales + receivables — ' + from + ' to ' + to);
        });
    }

    // ── 2. Product Distribution Donut Chart ──
    var pieChart = null;

    function renderPie(labels, series, subtitle) {
        var noData = document.getElementById('pie-no-data');
        var pieEl  = document.getElementById('chart-pie');
        var sub    = document.getElementById('pie-subtitle');

        if (sub) sub.textContent = '(' + subtitle + ')';

        var hasData = series.length > 0 && series.some(function(v){ return v > 0; });

        if (!hasData) {
            if (pieChart) { pieChart.destroy(); pieChart = null; }
            pieEl.innerHTML = '';
            if (noData) noData.style.display = '';
            return;
        }

        if (noData) noData.style.display = 'none';

        var options = {
            series: series,
            labels: labels,
            chart: { type: 'pie', height: 420 },
            colors: ['#0e7490','#06b6d4','#67e8f9','#0284c7','#38bdf8','#7dd3fc','#0369a1','#bae6fd','#0c4a6e','#22d3ee'],
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    return val.toFixed(1) + '%';
                },
                style: { fontSize: '11px', fontWeight: '600' },
                dropShadow: { enabled: false }
            },
            legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 6, vertical: 3 } },
            stroke: { width: 2, colors: ['#fff'] },
            tooltip: { y: { formatter: function(val){ return val + ' units'; } } }
        };

        if (pieChart) {
            pieChart.updateOptions(options, true, true);
        } else {
            pieChart = new ApexCharts(pieEl, options);
            pieChart.render();
        }
    }

    if (allLabels.length > 0) {
        renderPie(allLabels, allSeries, 'All Categories');
    }

    // Category click → drill down
    document.querySelectorAll('.rpt-cat-item').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.rpt-cat-item').forEach(function(l){ l.classList.remove('rpt-cat-active'); });
            this.classList.add('rpt-cat-active');

            var catId = this.dataset.catId;
            if (catId === 'all') {
                renderPie(allLabels, allSeries, 'All Categories');
            } else if (catProducts[catId]) {
                var catName = this.querySelector('.rpt-cat-indicator').nextSibling.textContent.trim();
                renderPie(catProducts[catId].labels, catProducts[catId].series, catName);
            } else {
                renderPie([], [], this.querySelector('.rpt-cat-indicator').nextSibling.textContent.trim());
            }
        });
    });

    // ── 3. Stock Bar Graph (grouped multi-series) ──
    if (stockLabels.length > 0) {
        var stockOptions = {
            series: [
                { name: 'Items In',       data: stockItemsIn },
                { name: 'Items Out',      data: stockItemsOut },
                { name: 'Current Stock',  data: stockValues }
            ],
            chart: { type: 'bar', height: 320, toolbar: { show: false } },
            colors: ['#38bdf8', '#0369a1', '#0c4a6e'],
            plotOptions: {
                bar: { horizontal: false, columnWidth: '70%', borderRadius: 2 }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '11px',
                markers: { radius: 3 }
            },
            xaxis: {
                categories: stockLabels,
                labels: {
                    style: { fontSize: '10px', colors: '#888' },
                    rotate: -30,
                    trim: true,
                    maxHeight: 60,
                    formatter: function(val){
                        return val && val.length > 14 ? val.substring(0, 14) + '…' : val;
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                labels: { style: { fontSize: '11px', colors: '#888' } }
            },
            grid: { borderColor: '#f3f3f3', strokeDashArray: 4 },
            tooltip: { shared: true, intersect: false, y: { formatter: function(val){ return val + ' units'; } } }
        };
        new ApexCharts(document.querySelector('#chart-stocks'), stockOptions).render();
    }

});
</script>

<?php $this->endSection(); ?>

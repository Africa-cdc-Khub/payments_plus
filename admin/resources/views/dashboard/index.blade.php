@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<!-- Highcharts Core -->
<script src="https://code.highcharts.com/highcharts.js"></script>

<!-- Essential Highcharts Modules -->
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/modules/no-data-to-display.js"></script>
<script src="https://code.highcharts.com/modules/offline-exporting.js"></script>

<!-- Additional useful modules -->
<script src="https://code.highcharts.com/modules/annotations.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/heatmap.js"></script>
<script src="https://code.highcharts.com/modules/treemap.js"></script>
<script src="https://code.highcharts.com/modules/sankey.js"></script>
<script src="https://code.highcharts.com/modules/sunburst.js"></script>
<script src="https://code.highcharts.com/modules/waterfall.js"></script>
<script src="https://code.highcharts.com/modules/funnel.js"></script>
<script src="https://code.highcharts.com/modules/variable-pie.js"></script>
<script src="https://code.highcharts.com/modules/wordcloud.js"></script>

<!-- Highcharts 3D -->
<script src="https://code.highcharts.com/highcharts-3d.js"></script>

<!-- Highcharts Maps -->
<script src="https://code.highcharts.com/maps/modules/map.js"></script>
<script src="https://code.highcharts.com/maps/modules/mapline.js"></script>
<script src="https://code.highcharts.com/maps/modules/mappoint.js"></script>
<script src="https://code.highcharts.com/maps/modules/mapbubble.js"></script>
@endpush

@section('content')
@php
    $admin = auth('admin')->user();
@endphp

@if($admin && in_array($admin->role, ['admin', 'travels','secretariat','finance']))
<!-- Main Stats Cards Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Total Registrations</p>
                <p class="text-2xl font-bold">{{ number_format($stats['total_participants']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Includes group members</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Paid Participants</p>
                <p class="text-2xl font-bold">{{ number_format($stats['paid_participants']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Confirmed attendees</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Pending Payments</p>
                <p class="text-2xl font-bold">{{ number_format($stats['pending_payments']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Excluding delegates</p>
                @if($stats['pending_invoices_revenue'] > 0)
                <small class="text-gray-500 image.png">Invoice value: ${{ number_format($stats['pending_invoices_revenue'], 2) }}</small>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-dollar-sign text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Total Revenue</p>
                <p class="text-2xl font-bold">${{ number_format($stats['total_revenue'], 2) }}</p>
                @if($stats['pending_invoices_revenue'] > 0)
                <small class="text-gray-500 image.png">Invoice value: ${{ number_format($stats['paid_invoices_revenue'], 2) }}</small>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delegates Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Delegates -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                <i class="fas fa-user-tie text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Total Delegates</p>
                <p class="text-xl font-bold text-indigo-600">{{ number_format($stats['delegates']['total']) }}</p>
            </div>
        </div>
    </div>
    
    <!-- Approved -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Approved Delegates</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($stats['delegates']['approved']) }}</p>
            </div>
        </div>
    </div>
    
    <!-- Pending -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Pending Delegates</p>
                <p class="text-xl font-bold text-yellow-600">{{ number_format($stats['delegates']['pending']) }}</p>
            </div>
        </div>
    </div>
    
    <!-- Rejected -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Rejected Delegates</p>
                <p class="text-xl font-bold text-red-600">{{ number_format($stats['delegates']['rejected']) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Delegate Categories Chart -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Registrations by Delegate Category</h3>
        <div id="delegateCategoryChart" style="width: 100%; height: 400px;"></div>
    </div>

    <!-- Delegate Approvals Chart -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Approvals by Delegate Category</h3>
        <div id="delegateApprovalChart" style="width: 100%; height: 400px;"></div>
    </div>
</div>

<!-- Geographic Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Participants by Continent -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Participants by Continent</h3>
        <div id="continentChart" style="width: 100%; height: 400px;"></div>
    </div>

    <!-- Participants by Nationality -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Top 10 Nationalities</h3>
        <div id="nationalityChart" style="width: 100%; height: 400px;"></div>
    </div>
</div>

<!-- Recent Activity Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Registrations -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Registrations</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recent_registrations as $registration)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $registration->user->full_name }}</div>
                            <div class="text-sm text-gray-500">{{ $registration->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->package->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($registration->payment_status === 'completed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($registration->total_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No registrations found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Payments</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recent_payments as $payment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $payment->user->full_name }}</div>
                            <div class="text-sm text-gray-500">{{ $payment->package->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            ${{ number_format($payment->payment_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->payment_completed_at?->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold">You are not authorized to access this page</h3>
    <p>Use Menu to navigate to the desired page</p>
</div>
@endif

@endsection

@push('scripts')
<script>
// Wait for both DOM and Highcharts to be ready
function initializeCharts() {
    // Check if Highcharts is loaded
    if (typeof Highcharts === 'undefined') {
        console.log('Highcharts not loaded yet, retrying...');
        setTimeout(initializeCharts, 200);
        return;
    }

    // Check if DOM elements exist
    const chartContainers = [
        'delegateCategoryChart',
        'delegateApprovalChart', 
        'continentChart',
        'nationalityChart'
    ];
    
    const missingContainers = chartContainers.filter(id => !document.getElementById(id));
    if (missingContainers.length > 0) {
        console.log('Missing chart containers:', missingContainers);
        setTimeout(initializeCharts, 200);
        return;
    }

    console.log('All chart containers found, initializing charts...');

    try {
        // Global Highcharts options
        Highcharts.setOptions({
            credits: {
                enabled: false
            },
            accessibility: {
                enabled: false
            },
            chart: {
                style: {
                    fontFamily: 'Inter, system-ui, sans-serif'
                },
                backgroundColor: 'transparent'
            },
            exporting: {
                enabled: true,
                buttons: {
                    contextButton: {
                        menuItems: [
                            'downloadPNG',
                            'downloadJPEG',
                            'downloadPDF',
                            'downloadSVG',
                            'separator',
                            'downloadCSV',
                            'downloadXLS',
                            'viewData',
                            'separator',
                            'printChart'
                        ]
                    }
                }
            },
            lang: {
                downloadPNG: 'Download PNG',
                downloadJPEG: 'Download JPEG',
                downloadPDF: 'Download PDF',
                downloadSVG: 'Download SVG',
                downloadCSV: 'Download CSV',
                downloadXLS: 'Download XLS',
                viewData: 'View data table',
                printChart: 'Print chart'
            }
        });

// Delegate Categories Chart
const delegateCategoryData = @json($delegateCategoryStats);
console.log('Delegate Category Data:', delegateCategoryData);

if (document.getElementById('delegateCategoryChart')) {
    console.log('Creating delegate category chart...');
    Highcharts.chart('delegateCategoryChart', {
        chart: {
            type: 'pie'
        },
        title: {
            text: 'Registrations by Delegate Category'
        },
        series: [{
            name: 'Registrations',
            data: delegateCategoryData.map((item, index) => ({
                name: item.delegate_category || 'Not Specified',
                y: parseInt(item.total) || 0
            }))
        }]
    });
    console.log('Delegate category chart created');
} else {
    console.log('Delegate category chart container not found');
}

// Delegate Approvals Chart
const delegateApprovalData = @json($delegateApprovalStats);
console.log('Delegate Approval Data:', delegateApprovalData);

const approvalData = {};
delegateApprovalData.forEach(item => {
    if (!approvalData[item.delegate_category]) {
        approvalData[item.delegate_category] = { approved: 0, pending: 0, rejected: 0 };
    }
    approvalData[item.delegate_category][item.status] = parseInt(item.total) || 0;
});

const categories = Object.keys(approvalData);
const approvedData = categories.map(cat => approvalData[cat].approved || 0);
const pendingData = categories.map(cat => approvalData[cat].pending || 0);
const rejectedData = categories.map(cat => approvalData[cat].rejected || 0);

if (document.getElementById('delegateApprovalChart')) {
    console.log('Creating delegate approval chart...');
    Highcharts.chart('delegateApprovalChart', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Approvals by Delegate Category'
        },
        xAxis: {
            categories: categories.map(cat => cat || 'Not Specified')
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Number of Delegates'
            }
        },
        series: [{
            name: 'Approved',
            data: approvedData,
            color: '#059669'
        }, {
            name: 'Pending',
            data: pendingData,
            color: '#eab308'
        }, {
            name: 'Rejected',
            data: rejectedData,
            color: '#ef4444'
        }]
    });
    console.log('Delegate approval chart created');
} else {
    console.log('Delegate approval chart container not found');
}

// Continent Chart
const continentData = @json($continentStats);
console.log('Continent Data:', continentData);

const continentEntries = Object.entries(continentData);
if (document.getElementById('continentChart')) {
    console.log('Creating continent chart...');
    Highcharts.chart('continentChart', {
        chart: {
            type: 'pie'
        },
        title: {
            text: 'Participants by Continent'
        },
        series: [{
            name: 'Participants',
            data: continentEntries.map(([continent, total]) => ({
                name: continent,
                y: parseInt(total) || 0
            }))
        }]
    });
    console.log('Continent chart created');
} else {
    console.log('Continent chart container not found');
}

// Nationality Chart
const nationalityData = @json($nationalityStats);
console.log('Nationality Data:', nationalityData);

if (document.getElementById('nationalityChart')) {
    console.log('Creating nationality chart...');
    Highcharts.chart('nationalityChart', {
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Top 10 Nationalities'
        },
        xAxis: {
            categories: nationalityData.map(item => item.nationality)
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Number of Participants'
            }
        },
        series: [{
            name: 'Participants',
            data: nationalityData.map(item => parseInt(item.total) || 0),
            color: '#1e40af'
        }]
    });
    console.log('Nationality chart created');
} else {
    console.log('Nationality chart container not found');
}

        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    }

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, starting chart initialization...');
    initializeCharts();
});

// Also try after a short delay to ensure everything is loaded
setTimeout(function() {
    if (typeof Highcharts !== 'undefined') {
        console.log('Fallback initialization...');
        initializeCharts();
    }
}, 1000);
</script>
@endpush


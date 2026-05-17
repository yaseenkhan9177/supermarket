<x-filament-panels::page>
    <style>
        /* --- 1. HIDE DEFAULT FILAMENT UI --- */
        .fi-topbar,
        .fi-sidebar,
        .fi-header {
            display: none !important;
        }

        .fi-main {
            padding: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        .fi-body {
            padding: 0 !important;
            background: transparent !important;
        }

        /* --- 2. ANIMATED GRADIENT BORDER --- */
        @keyframes borderRotate {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .gradient-border-wrapper {
            position: relative;
            border-radius: 0.75rem;
            /* rounded-xl */
            padding: 2px;
            /* Border width */
            background: transparent;
            transition: all 0.3s ease;
        }

        /* On Hover: Show animated gradient */
        .gradient-border-wrapper:hover {
            background: linear-gradient(60deg, #6366f1, #ec4899, #8b5cf6, #3b82f6);
            background-size: 300% 300%;
            animation: borderRotate 3s ease infinite;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }

        /* --- 3. GLASSMORPHISM & UTILS --- */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .dark .glass-panel {
            background: rgba(17, 24, 39, 0.7);
        }

        /* Smooth Theme Transition */
        html,
        body,
        div,
        span,
        button,
        p {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-duration: 300ms;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>

    <div class="max-w-7xl mx-auto w-full">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <div class="relative overflow-hidden rounded-3xl p-6 text-white shadow-2xl group cursor-pointer transition-transform hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-700"></div>
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md"><span class="text-xl">💰</span></div>
                        <span class="text-xs font-bold uppercase tracking-wider text-white">Net Sales</span>
                    </div>
                    <h2 class="text-4xl font-black tracking-tight text-white">125,430<span class="text-lg font-medium opacity-70">.00</span></h2>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="bg-green-400/20 text-green-300 px-2 py-0.5 rounded text-[10px] font-bold">+12.5%</span>
                        <span class="text-[10px] text-indigo-200">vs last 7 days</span>
                    </div>
                </div>
            </div>

            <div class="glass-panel rounded-3xl p-6 border border-white/20 dark:border-gray-700 shadow-xl dark:shadow-black/20 group hover:border-indigo-500/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cash Drawer</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rs. 32,000</h3>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-green-500 h-full w-[80%] rounded-full"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 text-right">Target: 40k</p>
            </div>

            <div class="glass-panel rounded-3xl p-6 border border-white/20 dark:border-gray-700 shadow-xl dark:shadow-black/20 group hover:border-red-500/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Receivables</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rs. 18,500</h3>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex -space-x-2 mt-2">
                    <div class="h-6 w-6 rounded-full bg-gray-200 border-2 border-white dark:border-gray-800"></div>
                    <div class="h-6 w-6 rounded-full bg-gray-300 border-2 border-white dark:border-gray-800"></div>
                    <div class="h-6 w-6 rounded-full bg-gray-400 border-2 border-white dark:border-gray-800 flex items-center justify-center text-[8px] font-bold text-white">+5</div>
                </div>
            </div>

            <div class="glass-panel rounded-3xl p-6 border border-white/20 dark:border-gray-700 shadow-xl dark:shadow-black/20 group hover:border-orange-500/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Low Stock</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">7 Items</h3>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
                <button class="w-full py-2 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 text-xs font-bold rounded-lg hover:bg-orange-100 transition-colors">
                    Reorder Now
                </button>
            </div>

        </div>


    <!-- Data Injection -->
    <div id="filament-chart-data" class="hidden"
        data-paid="{{ json_encode($chartData['paid_vs_unpaid']) }}"
        data-debit="{{ json_encode($chartData['daily_debit']) }}"
        data-sales="{{ json_encode($chartData['monthly_sales']) }}"
        data-balance="{{ json_encode($chartData['customer_balance']) }}"
        data-cashflow="{{ json_encode($chartData['cash_flow']) }}"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const dataElement = document.getElementById('filament-chart-data');
                // Ensure data exists
                if (!dataElement) return;

                const paidData = JSON.parse(dataElement.dataset.paid);
                const debitData = JSON.parse(dataElement.dataset.debit);
                const salesData = JSON.parse(dataElement.dataset.sales);
                const balanceData = JSON.parse(dataElement.dataset.balance);
                const cashFlowData = JSON.parse(dataElement.dataset.cashflow);

                // Check for dark mode
                const isDarkMode = document.documentElement.classList.contains('dark');
                const textColor = isDarkMode ? '#9ca3af' : '#6B7280';
                const gridColor = isDarkMode ? '#374151' : '#f3f4f6';

                Chart.defaults.font.family = "'Roboto', sans-serif";
                Chart.defaults.color = textColor;
                Chart.defaults.scale.grid.color = gridColor;

                // 1. Paid vs Unpaid (Doughnut)
                new Chart(document.getElementById('paidVsUnpaidChart'), {
                    type: 'doughnut',
                    data: {
                        labels: paidData.labels,
                        datasets: [{
                            data: paidData.data,
                            backgroundColor: ['#10B981', '#EF4444'], // Green, Red
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    color: textColor
                                }
                            }
                        }
                    }
                });

                // 2. Customer Balance (Horizontal Bar)
                new Chart(document.getElementById('customerBalanceChart'), {
                    type: 'bar',
                    data: {
                        labels: balanceData.labels,
                        datasets: [{
                            label: 'Balance (Rs)',
                            data: balanceData.data,
                            backgroundColor: '#6366F1', // Indigo
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y', // Horizontal
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor
                                }
                            }
                        }
                    }
                });

                // 3. Daily Debit (Line)
                new Chart(document.getElementById('dailyDebitChart'), {
                    type: 'line',
                    data: {
                        labels: debitData.labels,
                        datasets: [{
                            label: 'Debit',
                            data: debitData.data,
                            borderColor: '#F59E0B', // Orange
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [2, 4],
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor
                                }
                            }
                        }
                    }
                });

                // 4. Cash Flow (Line - Multi-axis/Multi-dataset)
                new Chart(document.getElementById('cashFlowChart'), {
                    type: 'line',
                    data: {
                        labels: cashFlowData.labels,
                        datasets: [{
                            label: 'Inflow',
                            data: cashFlowData.inflow,
                            borderColor: '#10B981', // Green
                            tension: 0.3,
                            pointRadius: 0
                        }, {
                            label: 'Outflow',
                            data: cashFlowData.outflow,
                            borderColor: '#EF4444', // Red
                            tension: 0.3,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 6,
                                    color: textColor
                                }
                            }
                        },
                        scales: {
                            y: {
                                grid: {
                                    borderDash: [2, 4],
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor
                                }
                            }
                        }
                    }
                });

                // 5. Monthly Sales (Bar)
                new Chart(document.getElementById('monthlySalesChart'), {
                    type: 'bar',
                    data: {
                        labels: salesData.labels,
                        datasets: [{
                            label: 'Sales',
                            data: salesData.data,
                            backgroundColor: '#3B82F6', // Blue
                            borderRadius: 4,
                            barPercentage: 0.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [2, 4],
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor
                                }
                            }
                        }
                    }
                });
            }, 100); // Small delay to ensure rendering
        });
    </script>

</x-filament-panels::page>
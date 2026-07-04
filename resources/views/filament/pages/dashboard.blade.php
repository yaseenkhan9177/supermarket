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
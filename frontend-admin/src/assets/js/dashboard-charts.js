/**
 * Dashboard Charts JavaScript
 * Professional dashboard with interactive charts
 */

class DashboardCharts {
    constructor() {
        this.charts = {};
        this.init();
    }

    init() {
        this.createMonthlyFinancialChart();
        this.createWorkersNationalityChart();
        this.createMonthlyContractsChart();
        this.createWorkersStagesChart();
        this.createPaymentsMethodChart();
        this.addChartAnimations();
    }

    createMonthlyFinancialChart() {
        const ctx = document.getElementById('monthlyFinancialChart');
        if (!ctx) return;

        this.charts.monthlyFinancial = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                datasets: [{
                    label: 'المدفوعات',
                    data: this.getMonthlyData(window.monthlyPaymentsData),
                    borderColor: '#4BC0C0',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4BC0C0',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }, {
                    label: 'المصروفات',
                    data: this.getMonthlyData(window.monthlyExpensesData),
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#FF6384',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#4BC0C0',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('ar-SA').format(value);
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    createWorkersNationalityChart() {
        const ctx = document.getElementById('workersNationalityChart');
        if (!ctx) return;

        this.charts.workersNationality = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: window.workersNationalityLabels || [],
                datasets: [{
                    data: window.workersNationalityData || [],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#FF6384',
                        '#C9CBCF',
                        '#4BC0C0',
                        '#FF6384'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 2000
                }
            }
        });
    }

    createMonthlyContractsChart() {
        const ctx = document.getElementById('monthlyContractsChart');
        if (!ctx) return;

        this.charts.monthlyContracts = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                datasets: [{
                    label: 'عدد العقود',
                    data: this.getMonthlyData(window.monthlyContractsData),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    createWorkersStagesChart() {
        const ctx = document.getElementById('workersStagesChart');
        if (!ctx) return;

        this.charts.workersStages = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: window.workersStagesLabels || [],
                datasets: [{
                    data: window.workersStagesData || [],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#FF6384',
                        '#C9CBCF'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 2000
                }
            }
        });
    }

    createPaymentsMethodChart() {
        const ctx = document.getElementById('paymentsMethodChart');
        if (!ctx) return;

        this.charts.paymentsMethod = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: window.paymentsMethodLabels || [],
                datasets: [{
                    label: 'المبلغ',
                    data: window.paymentsMethodData || [],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'المبلغ: ' + new Intl.NumberFormat('ar-SA').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('ar-SA').format(value);
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    getMonthlyData(data) {
        return Array.from({length: 12}, (_, index) => {
            const found = data.find(item => item.month == index + 1);
            return found ? found.total || found.count : 0;
        });
    }

    addChartAnimations() {
        // Add intersection observer for chart animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        });

        document.querySelectorAll('.chart-container').forEach(container => {
            observer.observe(container);
        });
    }

    // Method to update charts with new data
    updateChart(chartName, newData) {
        if (this.charts[chartName]) {
            this.charts[chartName].data = newData;
            this.charts[chartName].update();
        }
    }

    // Method to destroy all charts
    destroy() {
        Object.values(this.charts).forEach(chart => {
            chart.destroy();
        });
        this.charts = {};
    }
}

// Initialize dashboard charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set global data variables for charts
    window.monthlyPaymentsData = @json($monthly_payments);
    window.monthlyExpensesData = @json($monthly_expenses);
    window.monthlyContractsData = @json($monthly_contracts);
    window.workersNationalityLabels = @json($workers_by_nationality->pluck('nationality'));
    window.workersNationalityData = @json($workers_by_nationality->pluck('count'));
    window.workersStagesLabels = @json($workers_by_stages->pluck('stage_name'));
    window.workersStagesData = @json($workers_by_stages->pluck('count'));
    window.paymentsMethodLabels = @json($payments_by_method->pluck('method_name'));
    window.paymentsMethodData = @json($payments_by_method->pluck('total'));

    // Initialize dashboard charts
    window.dashboardCharts = new DashboardCharts();
});

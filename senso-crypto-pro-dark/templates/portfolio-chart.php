<?php
/**
 * Portfolio Chart Template
 */

if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    echo '<div class="scp-empty-state"><p>Please login to view your portfolio chart</p></div>';
    return;
}

$user_id = get_current_user_id();
$portfolio = SCP_Portfolio::instance();

$chart_type = isset($atts['type']) ? $atts['type'] : 'line';
$days = isset($atts['days']) ? intval($atts['days']) : 30;

$history = $portfolio->get_history($user_id, $days);
$chart_id = 'scpChart_' . uniqid();
?>

<div class="scp-portfolio-chart-wrapper scp-glass-card scp-fade-in">
    <div class="scp-chart-header">
        <h3 class="scp-neon-text">📈 Portfolio Performance</h3>
        <div class="scp-chart-controls">
            <button class="scp-chart-btn active" data-days="7" data-chart="<?php echo $chart_id; ?>">7D</button>
            <button class="scp-chart-btn" data-days="30" data-chart="<?php echo $chart_id; ?>">30D</button>
            <button class="scp-chart-btn" data-days="90" data-chart="<?php echo $chart_id; ?>">90D</button>
            <button class="scp-chart-btn" data-days="365" data-chart="<?php echo $chart_id; ?>">1Y</button>
        </div>
    </div>
    
    <div class="scp-chart-container">
        <canvas id="<?php echo $chart_id; ?>"></canvas>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        return;
    }
    
    const ctx = document.getElementById('<?php echo $chart_id; ?>');
    if (!ctx) return;
    
    const history = <?php echo json_encode($history); ?>;
    
    if (!history || history.length === 0) {
        ctx.parentElement.innerHTML = '<div class="scp-empty-state"><p>No portfolio history available</p></div>';
        return;
    }
    
    const chartCtx = ctx.getContext('2d');
    
    // Create gradient
    const gradient = chartCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(6, 182, 212, 0.4)');
    gradient.addColorStop(1, 'rgba(6, 182, 212, 0)');
    
    const chart = new Chart(chartCtx, {
        type: '<?php echo esc_js($chart_type); ?>',
        data: {
            labels: history.map(h => {
                const date = new Date(h.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Portfolio Value',
                data: history.map(h => parseFloat(h.total_value)),
                borderColor: '#06b6d4',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 0,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#06b6d4',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
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
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#06b6d4',
                    bodyColor: '#f1f5f9',
                    borderColor: '#06b6d4',
                    borderWidth: 2,
                    padding: 16,
                    displayColors: false,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 16,
                        weight: 'bold'
                    },
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Store chart instance
    window['chart_' + '<?php echo $chart_id; ?>'] = chart;
});
</script>

<style>
.scp-portfolio-chart-wrapper {
    padding: 2rem;
    margin-bottom: 2rem;
}

.scp-chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.scp-chart-header h3 {
    margin: 0;
    font-size: 1.5rem;
}

.scp-chart-controls {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .scp-chart-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .scp-chart-container {
        height: 300px;
    }
}
</style>

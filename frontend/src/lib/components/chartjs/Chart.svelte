<script>
  import { onMount, onDestroy } from 'svelte';
  import Chart from 'chart.js/auto';

  let { type = 'bar', labels = [], datasets = [], height = 220 } = $props();

  let canvas;
  let chart = $state(null);

  const tickColor = '#94a3b8';
  const gridColor = 'rgba(148, 163, 184, 0.12)';

  function buildOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          display: type === 'doughnut',
          position: 'bottom',
          labels: { color: tickColor, boxWidth: 10, boxHeight: 10, font: { size: 11, weight: 600 } }
        },
        tooltip: {
          backgroundColor: '#0f172a',
          titleColor: '#e2e8f0',
          bodyColor: '#cbd5e1',
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            label: (ctx) => {
              const raw = type === 'doughnut' ? ctx.parsed : ctx.parsed.y;
              const value = typeof raw === 'object' ? raw.r : raw;
              return `${ctx.dataset.label ?? 'Nilai'}: ${formatMoney(value ?? 0)}`;
            }
          }
        }
      },
      scales:
        type === 'bar'
          ? {
              x: { grid: { color: 'transparent' }, ticks: { color: tickColor, font: { size: 10 } }, border: { color: gridColor } },
              y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 } }, border: { display: false } }
            }
          : undefined
    };
  }

  function formatMoney(value) {
    return Number(value).toLocaleString('id-ID');
  }

  onMount(() => {
    chart = new Chart(canvas, { type, data: { labels, datasets }, options: buildOptions() });
  });

  $effect(() => {
    if (!chart) return;
    chart.data = { labels, datasets };
    chart.options = buildOptions();
    chart.update();
  });

  onDestroy(() => {
    chart?.destroy();
  });
</script>

<canvas bind:this={canvas} style="height: {height}px; width: 100%;"></canvas>
import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

/* stimulusFetch: 'lazy' */
/*
 * Allocation pie charts of the dashboard, moved as is from home.js (replaced by the new dashboard).
 * Colors come from the CSS tokens of app.css (hex / rgba: Chart.js 3 does not parse oklch()),
 * read again on `theme:change` so the charts follow the light / dark switch.
 */
const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
const randomBetween = (min, max) => min + Math.floor(Math.random() * (max - min + 1));

export default class extends Controller {
    #charts = [];
    // Datasets whose colors are tokens: [chart, dataset index, slice index or null, token name].
    #tokenColors = [];
    #applyTheme = () => {
        Chart.defaults.color = token('--muted-foreground');
        Chart.defaults.borderColor = token('--border');
        Chart.defaults.elements.arc.borderColor = token('--popover');
        this.#tokenColors.forEach(([chart, dataset, slice, name]) => {
            const colors = chart.data.datasets[dataset].backgroundColor;
            colors[slice] = token(name);
        });
        this.#charts.forEach((chart) => {
            chart.data.datasets.forEach((dataset) => { dataset.borderColor = token('--popover'); });
            chart.options.color = token('--muted-foreground');
            chart.update();
        });
    };

    connect() {
        this.#applyTheme();
        window.addEventListener('theme:change', this.#applyTheme);
        this.#render();
    }

    disconnect() {
        window.removeEventListener('theme:change', this.#applyTheme);
        this.#charts.forEach((chart) => chart.destroy());
        this.#charts = [];
        this.#tokenColors = [];
    }

    #render() {
        const element = this.element;
        const positions = element.dataset.pie;
        const positions_stable = element.dataset.piestable;
        const cryptoTotal = parseInt(element.dataset.crypto);
        const stableTotal = parseInt(element.dataset.stable);


        const json_positions = JSON.parse(positions);
        const json_positions_stable = JSON.parse(positions_stable);
        if (json_positions.length > 1) {
            const labels = [];
            const datasets = [];
            const rgbs = [];
            let percentOther = 0;
            json_positions.forEach(position => {
                if (position.percent > 0.3) {
                    labels.push(position.libelle);
                    datasets.push(String(position.percent))
                    if (position.color) {
                        rgbs.push(position.color)
                    } else {
                        const r = randomBetween(0, 255);
                        const g = randomBetween(0, 255);
                        const b = randomBetween(0, 255);
                        const rgb = `rgb(${r},${g},${b})`;
                        rgbs.push(rgb);
                    }
                } else {
                    percentOther += position.percent;
                }
            });

            labels.push("Autre(s)");
            datasets.push(String(percentOther));
            rgbs.push(token('--chart-5'));

            const data_crypto = {
                labels: labels,
                datasets: [{
                    label: 'Mes positions',
                    data: datasets,
                    backgroundColor: rgbs,
                }]
            };


            const canvas = document.getElementById('chart-pos').getContext('2d');
            const chartPos = new Chart(canvas, {
                type: 'doughnut',
                data: data_crypto,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            onClick: (e) => e.stopPropagation()
                        }
                    }
                }
            });
            this.#charts.push(chartPos);
            this.#tokenColors.push([chartPos, 0, rgbs.length - 1, '--chart-5']);
        }

        if (stableTotal > 0) {
            const totalStableCrypto = stableTotal + cryptoTotal;
            const percentStable = stableTotal * 100 / totalStableCrypto;
            const percentStableRounded = parseInt(percentStable.toPrecision(3));
            const percentCrypto = 100.0 - percentStableRounded;

            const data_ratioStableCrypto = {
                labels: [
                    'Crypto', 'Stable'
                ],
                datasets: [{
                    label: 'Mes positions',
                    data: [
                        percentCrypto, percentStableRounded
                    ],
                    backgroundColor: [
                        token('--chart-1'), token('--chart-2'),
                    ],
                }]
            };

            const canvas_ratio = document.getElementById('chart-ratio').getContext('2d');
            const chartRatio = new Chart(canvas_ratio, {
                type: 'pie',
                data: data_ratioStableCrypto
            });
            this.#charts.push(chartRatio);
            this.#tokenColors.push([chartRatio, 0, 0, '--chart-1'], [chartRatio, 0, 1, '--chart-2']);


            if (json_positions_stable.length > 1) {
                const labels_stable = [];
                const datasets_stable = [];
                const rgbs_stable = [];
                json_positions_stable.forEach(position => {
                    labels_stable.push(position.libelle);
                    datasets_stable.push(String(position.percent))
                    if (position.color) {
                        rgbs_stable.push(position.color)
                    } else {
                        const r = randomBetween(0, 255);
                        const g = randomBetween(0, 255);
                        const b = randomBetween(0, 255);
                        const rgb = `rgb(${r},${g},${b})`;
                        rgbs_stable.push(rgb);
                    }
                });


                const data_crypto_stable = {
                    labels: labels_stable,
                    datasets: [{
                        label: 'Mes positions Stable',
                        data: datasets_stable,
                        backgroundColor: rgbs_stable,
                    }]
                };

                const canvas_stable = document.getElementById('chart-stable').getContext('2d');
                this.#charts.push(new Chart(canvas_stable, {
                    type: 'pie',
                    data: data_crypto_stable
                }));
            }

        }
    }
}

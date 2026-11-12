import { Controller } from '@hotwired/stimulus';

/*
 * Wallet history chart of the dashboard. Adds to the view built by WalletHistoryChartFactory what PHP cannot
 * serialize: colors read from the CSS tokens (hex, Chart.js does not parse oklch()), Intl formats, the tooltip.
 *
 * Not lazy on purpose: it must listen to `chartjs:pre-connect` before the (lazy) `chart` controller of
 * symfony/ux-chartjs, on the same canvas, builds the chart. It imports nothing but Stimulus.
 */
const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

const withAlpha = (hex, alpha) => {
    const value = hex.replace('#', '');
    const full = value.length === 3 ? [...value].map((c) => c + c).join('') : value.slice(0, 6);
    const [r, g, b] = [0, 2, 4].map((i) => parseInt(full.slice(i, i + 2), 16));

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const DATE_FORMATS = {
    hour: { hour: '2-digit', minute: '2-digit' },
    day: { day: 'numeric', month: 'short' },
    month: { month: 'short', year: 'numeric' },
};

export default class extends Controller {
    static values = { currency: String, unit: String, hourly: Boolean };

    #chart = null;
    #onPreConnect = (event) => this.#configure(event.detail.config);
    #onConnect = (event) => { this.#chart = event.detail.chart; };
    #onThemeChange = () => {
        if (this.#chart) {
            this.#paint(this.#chart.data.datasets, this.#chart.options);
            this.#chart.update('none');
        }
    };

    connect() {
        this.element.addEventListener('chartjs:pre-connect', this.#onPreConnect);
        this.element.addEventListener('chartjs:connect', this.#onConnect);
        window.addEventListener('theme:change', this.#onThemeChange);
    }

    disconnect() {
        this.element.removeEventListener('chartjs:pre-connect', this.#onPreConnect);
        this.element.removeEventListener('chartjs:connect', this.#onConnect);
        window.removeEventListener('theme:change', this.#onThemeChange);
        // The `chart` controller destroys the chart itself.
        this.#chart = null;
    }

    #configure(config) {
        const { options } = config;
        const [value] = config.data.datasets;
        const lastIndex = value.data.length - 1;

        // A marked point on the current value.
        value.pointRadius = (context) => (context.dataIndex === lastIndex ? 4 : 0);
        value.pointBorderWidth = 2;

        const number = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 });
        const thousands = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 1 });
        const money = (amount) => `${amount < 0 ? '−' : ''}${number.format(Math.abs(amount))} ${this.currencyValue}`;
        const tick = new Intl.DateTimeFormat('fr-FR', DATE_FORMATS[this.unitValue] ?? DATE_FORMATS.day);
        const longDate = new Intl.DateTimeFormat('fr-FR', this.hourlyValue
            ? { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }
            : { day: 'numeric', month: 'short', year: 'numeric' });

        Object.assign(options.scales.x, {
            // 5 evenly spaced dates, the first and last ones inside the chart.
            afterBuildTicks: (axis) => {
                axis.ticks = [0, 1, 2, 3, 4].map((k) => ({ value: axis.min + ((axis.max - axis.min) * k) / 4 }));
            },
            ticks: { autoSkip: false, maxRotation: 0, align: 'inner', callback: (ms) => tick.format(ms) },
        });
        options.scales.y.ticks = {
            ...options.scales.y.ticks,
            callback: (amount) => (Math.abs(amount) >= 1000
                ? `${thousands.format(amount / 1000)} k${this.currencyValue}`
                : money(amount)),
        };
        options.plugins.tooltip = {
            displayColors: false,
            padding: 12,
            cornerRadius: 12,
            borderWidth: 1,
            callbacks: {
                title: (items) => longDate.format(items[0].parsed.x),
                label: (item) => `${item.dataset.label} : ${money(item.parsed.y)}`,
                afterBody: (items) => {
                    const [wallet, invested] = [0, 1].map((index) => items.find((item) => item.datasetIndex === index)?.parsed.y);
                    if (wallet === undefined || invested === undefined) {
                        return '';
                    }
                    const result = wallet - invested;

                    return `Résultat : ${result > 0 ? '+' : ''}${money(result)}`;
                },
            },
        };

        this.#paint(config.data.datasets, options);
    }

    #paint(datasets, options) {
        const line = token('--chart-1');
        const muted = token('--muted-foreground');
        const popover = token('--popover');
        const [value, invested] = datasets;

        value.borderColor = line;
        value.pointBackgroundColor = line;
        value.pointBorderColor = popover;
        value.pointHoverBackgroundColor = line;
        value.pointHoverBorderColor = popover;
        value.backgroundColor = ({ chart }) => {
            if (!chart.chartArea) {
                return 'transparent';
            }
            const gradient = chart.ctx.createLinearGradient(0, chart.chartArea.top, 0, chart.chartArea.bottom);
            gradient.addColorStop(0, withAlpha(line, 0.26));
            gradient.addColorStop(1, withAlpha(line, 0));

            return gradient;
        };
        if (invested) {
            invested.borderColor = muted;
        }

        options.scales.x.ticks.color = muted;
        options.scales.y.ticks.color = muted;
        (options.scales.y.grid ??= {}).color = token('--border');
        Object.assign(options.plugins.tooltip, {
            backgroundColor: popover,
            borderColor: token('--border'),
            titleColor: muted,
            bodyColor: token('--foreground'),
            footerColor: token('--foreground'),
        });
    }
}

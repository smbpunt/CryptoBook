import Chart from 'chart.js/auto';

const randomBetween = (min, max) => min + Math.floor(Math.random() * (max - min + 1));
const element = document.getElementById('id-array-positions');
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
    json_positions.forEach(position => {
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
    });

    const data_crypto = {
        labels: labels,
        datasets: [{
            label: 'Mes positions',
            data: datasets,
            backgroundColor: rgbs,
        }]
    };


    const canvas = document.getElementById('chart-pos').getContext('2d');
    new Chart(canvas, {
        type: 'pie',
        data: data_crypto
    });
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
                '#AAAAAA', '#505050'
            ],
        }]
    };

    const canvas_ratio = document.getElementById('chart-ratio').getContext('2d');
    new Chart(canvas_ratio, {
        type: 'pie',
        data: data_ratioStableCrypto
    });


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

        console.log(data_crypto_stable);

        const canvas_stable = document.getElementById('chart-stable').getContext('2d');
        new Chart(canvas_stable, {
            type: 'pie',
            data: data_crypto_stable
        });
    }

}

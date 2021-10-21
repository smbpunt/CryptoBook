import Chart from 'chart.js/auto';

const randomBetween = (min, max) => min + Math.floor(Math.random() * (max - min + 1));
const positions = document.getElementById('id-array-positions').dataset.pie;
const json_positions = JSON.parse(positions);
const labels = [];
const datasets = [];
const rgbs = [];

json_positions.forEach(position => {
    console.log(position);
    labels.push(position.libelle);
    datasets.push(String(position.valueUsd))
    const r = randomBetween(0, 255);
    const g = randomBetween(0, 255);
    const b = randomBetween(0, 255);
    const rgb = `rgb(${r},${g},${b})`;
    rgbs.push(rgb);
})

console.log(datasets);

const data = {
    labels: labels,
    datasets: [{
        label: 'Mes positions',
        data: datasets,
        backgroundColor: rgbs,
    }]
};

const canvas = document.getElementById('chart-pos').getContext('2d');
const chart = new Chart(canvas, {
    type: 'pie',
    data: data
});
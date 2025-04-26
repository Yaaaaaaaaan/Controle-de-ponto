const data = {
    labels: labels,
    datasets: [{
        label: " ",
        data: dataPoints,
        backgroundColor: [
            'rgb(173,181,189)',
            'rgb(32,201,151)',
            'rgb(253,126,20)'
        ],
        hoverOffset: 4
    }]
};

const config = {
    type: 'pie',
    data: data,
};



document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('pointControlUsersData').getContext('2d');
    new Chart(ctx, config);
});
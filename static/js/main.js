// Query string
var qs = (function(a) {
    if (a == "") return {};
    var b = {};
    for (var i = 0; i < a.length; ++i) {
        var p=a[i].split('=');
        if (p.length != 2) continue;
        b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
    }
    return b;
})(window.location.search.substr(1).split(/[&;]/));

$(function() {
	$('a.external').click(function() {
		window.open(this.href);
		return false;
	});
	
	if(qs.error !== undefined) {
		delete qs.error;
	}
	
	if(qs.success !== undefined) {
		delete qs.success;
	}
	
	replaceHistory();
	
//	google.setOnLoadCallback(drawChart);
	$('#chart').highcharts({
        chart: {
            type: 'line',
            marginRight: 250,
            marginBottom: 25
        },
        title: {
            text: 'Stats'
        },
        subtitle: {
            text: 'Median response time'
        },
        xAxis: {
            categories: chart_points || ['-30d', '-7d', '-3d', '-1d', '-12h', '-3h', '-1h']
        },
        yAxis: {
            title: {
                text: 'Response time (ms)'
            },
            plotLines: [{
                value: 0,
                width: 12,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: 'ms'
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            borderWidth: 0
        },
        series: chart_series
    });
});

//function drawChart() {
//    var data = google.visualization.arrayToDataTable([
//      ['Year', 'Sales', 'Expenses'],
//      ['2004',  1000,      400],
//      ['2005',  1170,      460],
//      ['2006',  660,       1120],
//      ['2007',  1030,      540]
//    ]);
//
//    var options = {};
//
//    var chart = new google.visualization.LineChart(document.getElementById('chart'));
//    chart.draw(data, options);
//}

function replaceHistory() {
	var new_qs= [];
  	
	for(var p in qs) {
		new_qs.push(p+'='+qs[p]);
	}
	
	if(new_qs.length === 0) {
		new_qs= '';
	} else {
		new_qs= '?' + new_qs.join(';');
	}
	
	var pathname= window.location.pathname;
	
	if(window.location.pathname.match(/\/moderate\/\d+/)) {
		delete qs.page;
		pathname= '/moderate';
	}
	
	window.history.replaceState(null, null, '//' + window.location.host + pathname + new_qs + (location.hash ? location.hash : ''));
}
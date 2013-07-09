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

var chart;

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
	
	chart= new Highcharts.Chart({
		chart: {
			renderTo: 'chart',
            type: 'line',
            zoomType: 'x',
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
            categories: chart_points || ['-30d', '-7d', '-3d', '-1d', '-12h', '-3h', '-1h', '-45m', '-30m', '-15m', '-5m']
        },
        yAxis: {
            title: {
                text: 'Response time (ms)'
            },
            plotLines: [{
                value: 0,
                width: 12,
                color: '#808080'
            }],
            min: 0
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
    
    $('.tablesorter').tablesorter(); 
	
//	drawChart(chart_series);
//    setInterval(function() {
//    	$.get('/chart-data', function(r) {
//    		console.log(r);
//    		drawChart(r);
//    	});
//    }, 15000);
});

function drawChart(series) {
	for(var site_data in series) {
//		console.log(site_data, series[site_data].data);
		chart.series[site_data].setData(series[site_data].data);
	}
}

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
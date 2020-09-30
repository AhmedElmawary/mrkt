const API_URL = 'api';


function updateDasboard()
{
	updateStats();
	updateGraphs();
}

function updateStats()
{
	const data = {
		call: 'dashboard_stats',
		args: {
			year: document.getElementById('stats_year').value,
			month: document.getElementById('stats_month').value,
			day: document.getElementById('stats_day').value
		}
	};
	
	callGraphApi(data, null, applyStats);
}

function updateGraphs()
{
	const data = {
		year: document.getElementById('graph_year').value,
		month: document.getElementById('graph_month').value,
		day: document.getElementById('graph_day').value
	};
	
	updateGraphUsersCount(data);
	updateGraphOrdersCount(data);
	updateGraphOrdersPriceSum(data);
	updateGraphOrdersPaymentMethod(data);
	updateGraphAvgRevenuePerStore(data);
	updateGraphRevenuePerStore(data);
}

function applyStats(data, extra)
{
	for(const i in data) document.getElementById(i).innerHTML = data[i];
}

function updateGraphUsersCount(args)
{
	var time_order = 'month';
	
	if(args.day != '%') time_order = 'hour';
	else if(args.month != '%') time_order = 'day';
	
	const data = {
		call: 'graph_users_count',
		args: args
	};
	
	const graphOptions = {
		graph_area_id: 'graph-user-count',
		titles: ['Users', 'Delivery staff'],
		time_order: time_order
	};
	
	callGraphApi(data, graphOptions, plotLineChart);
}

function updateGraphOrdersCount(args)
{
	var time_order = 'month';
	
	if(args.day != '%') time_order = 'hour';
	else if(args.month != '%') time_order = 'day';
	
	const data = {
		call: 'graph_orders_count',
		args: args
	};
	
	const graphOptions = {
		graph_area_id: 'graph-orders-count',
		titles: ['Completed', 'Canceled'],
		time_order: time_order
	};
	
	callGraphApi(data, graphOptions, plotLineChart);
}

function updateGraphOrdersPriceSum(args)
{
	var time_order = 'month';
	
	if(args.day != '%') time_order = 'hour';
	else if(args.month != '%') time_order = 'day';
	
	const data = {
		call: 'graph_orders_price_sum',
		args: args
	};
	
	const graphOptions = {
		graph_area_id: 'graph-orders-price-sum',
		titles: ['Completed', 'Canceled'],
		time_order: time_order
	};
	
	callGraphApi(data, graphOptions, plotLineChart);
}

function updateGraphOrdersPaymentMethod(args)
{
	const data = {
		call: 'graph_orders_payment_method',
		args: args
	};
	
	const graphOptions = {
		graph_area_id: 'graph-orders-payment-method'
	};
	
	callGraphApi(data, graphOptions, plotPieChart);
}

function updateGraphAvgRevenuePerStore(args)
{
	const data = {
		call: 'graph_avg_revenue_per_store',
		args: args
	};
	
	const graphOptions = {
		graph_area_id: 'graph-avg-revenue-per-store'
	};
	
	callGraphApi(data, graphOptions, plotBarChart);
}

function updateGraphRevenuePerStore(args)
{
	const data = {
		call: 'graph_revenue_per_store',
		args: args
	};
	
	const graphOptions = {
		graph_area_id: 'graph-revenue-per-store'
	};
	
	callGraphApi(data, graphOptions, plotBarChart);
}

function callGraphApi(data, graphOptions, plotCallback)
{
	var xmlhttp = new XMLHttpRequest();
	
	xmlhttp.onreadystatechange = function()
	{
		if(xmlhttp.readyState != 4 || xmlhttp.status != 200) return;
		
		const output = JSON.parse(xmlhttp.responseText);
		if(!output)
		{
			alert('Wrong JSON format!');
			return;
		}
		
		plotCallback(output, graphOptions);
	};
	
	xmlhttp.open('POST', API_URL, true);
	xmlhttp.setRequestHeader('Content-type', 'application/json; charset=utf-8');
	xmlhttp.send(JSON.stringify(data));
}

function plotLineChart(data, graphOptions)
{
	var graph = jQuery('#'+ graphOptions.graph_area_id);
	if(!data.length)
	{
		graph[0].innerHTML = '<p><mark>No data.</mark></p>';
		return;
	}
	
	const opacities = [0.7, 0.5];
	var i, j, valueSum, minX = 1, maxX = 12, maxCount = 0;
	
	if(graphOptions.time_order == 'hour')
	{
		minX = 0;
		maxX = 24;
	}
	else if(graphOptions.time_order == 'day') maxX = 31;
	
	if(graphOptions.titles.length == 1) data = [data];
	
	for(j = 0; j < graphOptions.titles.length; j++)
	{
		valueSum = 0;
		
		for(i = 0; i < data[j].length; i++)
		{
			valueSum += data[j][i].value;
			if(data[j][i].value > maxCount) maxCount = data[j][i].value;
			
			data[j][i] = [
				data[j][i][graphOptions.time_order],
				data[j][i].value
			];
		}
		
		data[j] = {
			label: graphOptions.titles[j].trim() +' ('+ valueSum +')',
			data: data[j],
			lines: {
				show: true,
				fill: true,
				fillColor: {
					colors: [{opacity: opacities[j]}, {opacity: opacities[j]}]
				}
			},
			points: {
				show: true,
				radius: 4
			}
		};
	}
	
	$.plot(graph, data,
		{
			xaxis: {
				min: minX,
				max: maxX,
				tickSize: 1,
				tickDecimals: 0
			},
			yaxis: {
				min: 0,
				max: Math.ceil(maxCount * 1.1),
				tickSize: Math.ceil(maxCount / 10),
				tickDecimals: 0
			},
			colors: ['#abe37d', '#faad7d'],
			grid: {
				borderWidth: 1,
				hoverable: true
			}
		}
	);
	
	createTooltips(graph);
}

function plotBarChart(data, graphOptions)
{
	var graph = jQuery('#'+ graphOptions.graph_area_id);
	if(!data.length)
	{
		graph[0].innerHTML = '<p><mark>No data.</mark></p>';
		return;
	}
	
	var i, maxCount = 0, series = [], labels = [];
	
	for(i = 0; i < data.length; i++)
	{
		series[series.length] = {
			label: data[i].label +' ('+ data[i].value +')',
			data: [[i, data[i].value]],
			bars: {
				show: true,
				barWidth: data.length / 4,
				lineWidth: 0,
				align: 'center',
				fillColor: {
					colors: [{opacity: .7}, {opacity: .7}]
				}
			}
		};
		
		labels[labels.length] = [i, data[i].label];
		if(data[i].value > maxCount) maxCount = data[i].value;
	}
	
	jQuery.plot(graph,
		series,
		{
			legend: {
				show: true
			},
			grid: {
				borderWidth: 1,
				hoverable: true
			},
			yaxis: {
				min: 0,
				max: Math.ceil(maxCount * 1.1),
				tickSize: Math.ceil(maxCount / 10),
				tickDecimals: 0,
				tickColor: '#f5f5f5'
			},
			xaxis: {
				ticks: labels,
				tickColor: '#f5f5f5'
			}
		}
	);
	
	createTooltips(graph);
}

function plotPieChart(data, graphOptions)
{
	var graph = jQuery('#'+ graphOptions.graph_area_id);
	if(!data.length)
	{
		graph[0].innerHTML = '<p><mark>No data.</mark></p>';
		return;
	}
	
	$.plot(graph, data, {
		legend: {show: true},
		series: {
			pie: {
				show: true,
				radius: 1,
				label: {
					show: true,
					radius: 2 / 3,
					formatter: function(label, pieSeries) {
						return '<div class="flot-pie-label">' + label + '<br />' + Math.round(pieSeries.percent) + '%</div>';
					},
					background: {
						opacity: .7,
						color: '#000000'
					}
				}
			}
		}
	});
}

function createTooltips(graph)
{
	graph.bind('plothover', function(event, pos, item)
	{
		jQuery('.js-flot-tooltip').remove();
		if(!item) return;
		
		const ttlabel = '<strong>' + item.datapoint[1] + '</strong>';
		jQuery('<div class="js-flot-tooltip flot-tooltip" align="center">' + ttlabel + '</div>').css({top: item.pageY - 45, left: item.pageX + 5}).appendTo('body').show();
	});
}
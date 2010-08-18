$(function(){
	var sources = [];
	
	$('#view_all > span').mouseover(function(){
		$(this).css({
			"text-decoration": "underline"
		})
	});
	
	$('#view_all > span').mouseout(function(){
		$(this).css({
			"text-decoration": "none"
		})
	});
	
	$('#view_all > span').click(function(){
		$(this).empty();
		$(this).html('<img src="/images/loader.gif" alt="loading" width="32" height="32"');
		$('tr.over').show();
		$(this).empty();
	});
	
	$('tr.record').click(function(){
	
		var temp_sources = [];
		var source = $(this).children('td.source').text();
		var $tr = $(this).parent();
		
		var is_equal = false;
		for (var i in sources) {
			if (sources[i] == source) {
				$(this).css({
					'background-color': '#fff'
				});
				is_equal = true;
			} else {
				temp_sources.push(sources[i]);
			}
		}
		
		if (is_equal == false) {
			if (sources.length >= 3) {
				alert('Select up to three.');
				return false;
			}
			
			$(this).css({
				'background-color': '#c8fff0'
			});
			temp_sources.push(source);
		}
		
		sources = temp_sources;
		$('#compare > #target').html(sources.join(' + '));
		
		if (sources.length >= 2) {
			$('button#submit').css({
				'visibility': 'visible'
			});
		} else {
			$('button#submit').css({
				'visibility': 'hidden'
			});
		}
		return;
	});
	
	$('button#submit').click(function(){
		startLoading('#chart');
		
		var form = $('form').serialize();
		form = form + '&source%5B%5D=' + sources.join('&source%5B%5D=');
		$.ajax({
			url: "/api/time_table",
			type: "post",
			dataType: "json",
			data: form,
			success: function(json, status_code){
				
				if(typeof json.error != 'undefined'){
					alert(json.error);
					endLoading('#chart');
					return false;
				}
				
				var block = Math.ceil(json.max / 4);
				var data = {};
				for (var i = 0; i <= 23; i++) {
					var j = String(i);
					if (j.length == 1) {
						j = '0' + j;
					}
					
					for (var k in json.data) {
						if (typeof(data[k]) == 'undefined') {
							data[k] = [];
						}
						var count = json.data[k].time_table[j] ? json.data[k].time_table[j] : 0;
						data[k].push(count);
					}
				}
				
				var chd_array = [];
				var source_array = [];
				for (var l in data) {
					chd_array.push(data[l].join(','));
					source_array.push(l);
				}

				var url = 'http://chart.apis.google.com/chart?';
				var chxl = 'chxl=0:|0|1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|1:|0|' + block + '|' + (block * 2) + '|' + (block * 3) + '|' + json.max + '|2:|Hour';
				var chxs = 'chxs=0,0E7FB7,11,0,l,676767|1,676767,12,0,l,676767|2,3072F3,12,0,l,676767';
				var chxr = 'chxr=0,0,23|1,0,' + json.max;
				var chds = 'chds=0,' + json.max;
				var chd = 'chd=t:' + chd_array.join('|');
				var chdl = 'chdl=' + sources.join('|');
				//var chtt = 'chtt=' + title;
				//var chts = 'chts=222222,12';
				var chart = '<img src="' + url + chxl + '&' + chxs + '&' + chds + '&chxt=x,y,x&' + chxr + '&chdlp=b&chs=400x370&cht=lc&chco=FF0000,3072F3,399D39&' + chd + '&' + chdl + '&chg=4.347,5,2,2&chls=1|1" alt="" width="400" height="370"/>'
				$('#graph').empty();
				$('#graph').html(chart);
				
				endLoading();
			},
			error: function(http_request, status_code){
				endLoading();
			}
		});
	});
	
	$('#extract').submit(function(){
		startLoading('#table');
		return true;
	});
	
	function startLoading(target){
		var outer_width = $(target).outerWidth();
		var outer_height = $(target).outerHeight();
		var top = $(target).position().top;
		var left = $(target).position().left;
		
		$('div#chart_overlay').hide();
		$('div#overlay_image').hide();
		
		$('div#chart_overlay').css({
			'position': 'absolute',
			'top': parseInt(top) + 'px',
			'left': left + 'px',
			'width': outer_width + 'px',
			'height': outer_height + 'px',
			'background-color': '#fff',
			'-ms-filter': 'alpha(opacity=50)',
			'filter': 'alpha(opacity=50)',
			'opacity': 0.5,
			'z-index': 1000
		});
		
		$('div#overlay_image').css({
			'position': 'absolute',
			'top': (parseInt(top) + parseInt(outer_height / 2) - 50) + 'px',
			'left': (left + (outer_width / 2) - 100) + 'px',
			'width': '200px',
			'text-align': 'center',
			'padding': '15px',
			'font-weight': 'bold',
			'background-color': '#fff',
			'z-index': 1001,
			'border': '1px solid #666'
		});
		
		$('div#chart_overlay').show();
		$('div#overlay_image').show();
	}
	
	function endLoading(){
		$('div#chart_overlay').hide();
		$('div#overlay_image').hide();
	}
});

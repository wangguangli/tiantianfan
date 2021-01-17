function city_arr(city) {
	var nameEl = document.getElementById('picker');
	var first = [];
	var second = [];
	var third = [];
	var selectedIndex = [0, 0, 0];
	var checked = [0, 0, 0];
	function creatList(obj, list) {
		obj.forEach(function(item, index, arr) {
			var temp = new Object();
			temp.text = item.name;
			temp.id = item.id;
			temp.value = index;
			list.push(temp);
		})
	}
	creatList(city, first);
	if (city[selectedIndex[0]].hasOwnProperty('gc')) {
		creatList(city[selectedIndex[0]].gc, second);
	} else {
		second = [{
			text: '',
			value: 0
		}];
	}
	if (city[selectedIndex[0]].gc[selectedIndex[1]].hasOwnProperty('gc')) {
		creatList(city[selectedIndex[0]].gc[selectedIndex[1]].gc, third);
	} else {
		third = [{
			text: '',
			value: 0
		}];
	}
	var picker = new Picker({
		data: [first, second, third],
		selectedIndex: selectedIndex,
		title: '选择商品分类'
	});
	picker.on('picker.select', function(selectedIndex) {
		var text1 = first[selectedIndex[0]].text;
		var text2 = second[selectedIndex[1]].text;
		var text3 = third[selectedIndex[2]] ? third[selectedIndex[2]].text : '';
		nameEl.value = text1 + '_' + text2 + '_' + text3;
		// var id1 = first[selectedIndex[0]].id;
		// var id2 = second[selectedIndex[1]].id;
		var id3 = third[selectedIndex[2]] ? third[selectedIndex[2]].id : '';
		// if(!third[selectedIndex[2]]){
		// 	alert('请选择完整的商品分类');
		// 	return false
		// }
		$(nameEl).attr('data-id',id3); //id1 + '-' + id2 + '-' +
	});
	picker.on('picker.change', function(index, selectedIndex) {
		if (index === 0) {
			firstChange();
		} else if (index === 1) {
			secondChange();
		}
		function firstChange() {
			second = [];
			third = [];
			checked[0] = selectedIndex;
			var firstCity = city[selectedIndex];
			if (firstCity.hasOwnProperty('gc')) {
				creatList(firstCity.gc, second);
				var secondCity = city[selectedIndex].gc[0];
				if (secondCity.hasOwnProperty('gc')) {
					creatList(secondCity.gc, third);
				} else {
					third = [{
						text: '',
						value: 0
					}];
					checked[2] = 0;
				}
			} else {
				second = [{
					text: '',
					value: 0
				}];
				third = [{
					text: '',
					value: 0
				}];
				checked[1] = 0;
				checked[2] = 0;
			}
			picker.refillColumn(1, second);
			picker.refillColumn(2, third);
			picker.scrollColumn(1, 0);
			picker.scrollColumn(2, 0)
		}
		function secondChange() {
			third = [];
			checked[1] = selectedIndex;
			var first_index = checked[0];
			if (city[first_index].gc[selectedIndex].hasOwnProperty('gc')) {
				var secondCity = city[first_index].gc[selectedIndex];
				creatList(secondCity.gc, third);
				picker.refillColumn(2, third);
				picker.scrollColumn(2, 0)
			} else {
				third = [{
					text: '',
					value: 0
				}];
				checked[2] = 0;
				picker.refillColumn(2, third);
				picker.scrollColumn(2, 0)
			}
		}
	});
	picker.on('picker.valuechange', function(selectedIndex) {
		console.log(selectedIndex);
	});
	nameEl.addEventListener('click', function() {
		picker.show();
	});
}
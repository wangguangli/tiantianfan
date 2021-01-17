/**
 * 后台共用
 * 自定义方法
 * 自 2019.10.28
 */

$(document).ready(function(){


	/**
	 * 后台分页跳转的处理
	 * @zd
	 */
	$('.page_jump').click(function(){
		var jumpurl = $(".jumpurl").text();
		var totalpage = $(".total_pages_hidden").text();
		var jump_num = $(".jump_num").val();
		totalpage = parseInt(totalpage);
		jump_num = parseInt(jump_num);
		if (jump_num > totalpage)
		{
			alert('跳转页码不可大于总页码');
			return;
		}
		jumpurl = jumpurl.replace('x9x9x', jump_num);
		window.location.href = jumpurl;
	});

});


/**
 * 后台批量删除操作
 * type  横线前面数字 代表删除的哪些内容，1=用户，2商家，3代理，4订单，5商品，6商品三级分类，7日志
 *       横线后面数字 代理删除类型，0逻辑删除，1彻底删除
 * ids   要删除的数组信息
 * isajax   是否ajax操作，1是，0否，默认1
 * truedel   是否真实删除，1是，0否
 * @zd
 */
function delete_much(type='0_0', truedel=0)
{
	if (truedel == 1)
	{
		var notice = "是否确认彻底删除？删除后无法恢复！！！";
	}
	else
	{
		var notice = "是否确认删除？";
	}
	var arr=[];
	$("input[name*='much_id']:checked").each(function(){
		var checkValue = $(this).val();
		arr.push(checkValue);
	});

	layui.use('layer', function(){
		var $ = layui.jquery;
		var layer = layui.layer;
		if (arr === undefined || arr.length == 0) {
			layer.msg('请至少选择一项');
			return;
		}
		layer.confirm(notice, {icon: 3, title:'提示'}, function(index){
			$.ajax({
				type : "POST",
				url:"/index.php/Admin/Goods/deleteMuch",
				data : {ids:arr, type:type, isajax:1},
				dataType:'json',
				success: function(data){
					console.log(data);
					if(data.status == 0){
						layer.msg('操作成功', {icon:1,time:1000},function(){
							window.location.reload();
						});
					}else{
						layer.msg('操作失败');
					}
				}
			});
			layer.close(index);
		});
	});
}


/**
 * 后台批量删除操作
 * type  代表恢复的哪些内容，1=用户，2商家，3代理，4订单，5商品，6商品三级分类，7日志
 * ids   要删除的数组信息
 * isajax   是否ajax操作，1是，0否，默认1
 * @zd
 */
function recover_much(type='0')
{
	var notice = "是否确认恢复选中项？";
	var arr=[];
	$("input[name*='much_id']:checked").each(function(){
		var checkValue = $(this).val();
		arr.push(checkValue);
	});

	layui.use('layer', function(){
		var $ = layui.jquery;
		var layer = layui.layer;
		if (arr === undefined || arr.length == 0) {
			layer.msg('请至少选择一项');
			return;
		}
		layer.confirm(notice, {icon: 3, title:'提示'}, function(index){
			$.ajax({
				type : "POST",
				url:"/index.php/Admin/Goods/recoverMuch",
				data : {ids:arr, type:type, isajax:1},
				dataType:'json',
				success: function(data){
					console.log(data);
					if(data.status == 0){
						layer.msg('操作成功', {icon:1,time:1000},function(){
							window.location.reload();
						});
					}else{
						layer.msg('操作失败');
					}
				}
			});
			layer.close(index);
		});
	});
}
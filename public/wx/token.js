function getUrlGet(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]); return null;
}
var token=$.cookie('token');
if(token && token!='null'){
	console.log(token);
}else{
	var token=getUrlGet('token');
	if(token){
		$.cookie('token',token);
	}else{
		location.href="https://www.yuanzigo.com/api/wechat/getToken";
	}
}
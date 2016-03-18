var youku = "v.youku.com";
var tudou = "www.tudou.com";

function videoparser(url){
	var v_type = url.split("/")[2];
	if(v_type == youku){
		var v_id = url.split("/")[4].slice(3,16);
		return "http://player.youku.com/embed/" + v_id;
	} else if(v_type == tudou){
        var code = url.split("/")[5].slice(0,11);
		var lcode = url.split("/")[4].slice(0,11);
        return "http://www.tudou.com/programs/view/html5embed.action?type=1&code=" + code + "&lcode=" + lcode + "&resourceId=0_06_05_99"
	}
}
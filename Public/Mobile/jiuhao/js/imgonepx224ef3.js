define("a/gotoappdetail.js",["biz_common/dom/event.js","biz_common/utils/report.js","biz_wap/utils/ajax.js","biz_common/dom/class.js","biz_wap/jsapi/core.js"],function(t){
"use strict";
function e(t){
"undefined"!=typeof l&&l.log&&l.log(t);
}
function a(t,e){
o("http://mp.weixin.qq.com/mp/ad_report?action=follow&type="+t+e.report_param);
}
function n(t){
var n=t.btn,o=t.js_app_rating;
if(!n)return!1;
var _={},u=t.adData,f="",v="",g=u.md5sum,j="",b=t.pos_type||0;
if(function(){
var t=1*u.app_rating;
t>5&&(t=5),0>t&&(t=0);
var e=["","one","two","three","four","five"],a="",n=Math.floor(t);
if(a="star_"+e[n],t>n&&(t=n+.5,a+="_half"),o&&t>0){
var i=o.getElementsByClassName("js_stars"),r=o.getElementsByClassName("js_scores");
i&&r&&i[0]&&r[0]&&(i=i[0],r=r[0],r.innerHTML=t,i.style.display="inline-block",p.addClass(i,a));
}
}(),"104"==u.pt){
var h=u.androiddownurl;
if(v=f=u.channel_id||"",h&&h.match){
var w=/&channelid\=([^&]*)/,y=h.match(w);
y&&y[1]&&(f=y[1],u.androiddownurl=h.replace(w,""));
}
f&&(f="&channelid="+f),t.via&&(j=["&via=ANDROIDWX.YYB.WX.ADVERTISE",t.via].join("."));
}
l.ready(function(){
"104"==u.pt&&(l.invoke("getInstallState",{
packageName:c
},function(t){
var a=t.err_msg;
e("getInstallState @yingyongbao : "+a);
var n=a.lastIndexOf("_")+1,i=a.substring(n);
1*i>=m&&a.indexOf("get_install_state:yes")>-1&&(d=!0);
}),l.invoke("getInstallState",{
packageName:u.pkgname
},function(t){
var a=t.err_msg;
e("getInstallState @"+u.pkgname+" : "+a);
var i=a.lastIndexOf("_")+1,o=a.substring(i);
1*o>=u.versioncode&&a.indexOf("get_install_state:yes")>-1&&(s=!0,n.innerHTML="已安装",
p.removeClass(n,"btn_download"),p.addClass(n,"btn_installed"));
})),i.on(n,"click",function(){
if(e("click @js_app_action"),s&&"104"==u.pt)return!1;
var n=function(){
if("104"==u.pt)return d?(a(24,t),void(location.href="tmast://download?oplist=1;2&pname="+u.pkgname+f+j)):(a(25,t),
void(location.href="http://mp.weixin.qq.com/mp/ad_app_info?t=ad/app_detail&app_id="+u.app_id+(t.appdetail_params||"")+"&channel_id="+v+"&md5sum="+g+"&auto=1#wechat_redirect"));
if("103"==u.pt){
a(23,t);
var e="http://"+location.host+"/mp/ad_redirect?url="+encodeURIComponent(u.appinfo_url)+"&uin="+uin+"&ticket="+(t.ticket||window.ticket);
l.invoke("downloadAppInternal",{
appUrl:u.appinfo_url
},function(t){
t.err_msg&&-1!=t.err_msg.indexOf("ok")||(location.href=e);
});
}
};
return u.rl&&u.traceid?_[u.traceid]||(_[u.traceid]=!0,r({
url:"/mp/advertisement_report?report_type=2&click_pos=0&type="+u.type+"&url="+encodeURIComponent(u.androiddownurl)+"&tid="+u.traceid+"&rl="+encodeURIComponent(u.rl)+"&pos_type="+b+"&__biz="+biz+"&pt="+u.pt+"&r="+Math.random(),
type:"GET",
timeout:1e3,
complete:function(){
_[u.traceid]=!1,n();
},
async:!0
})):n(),!1;
});
});
}
var i=t("biz_common/dom/event.js"),o=t("biz_common/utils/report.js"),r=t("biz_wap/utils/ajax.js"),p=t("biz_common/dom/class.js"),s=!1,l=t("biz_wap/jsapi/core.js"),d=!1,c="com.tencent.android.qqdownloader",m=1060125;
return n;
});define("a/ios.js",["biz_common/dom/event.js","biz_common/utils/report.js","biz_wap/utils/ajax.js","biz_wap/jsapi/core.js"],function(t){
"use strict";
function i(t){
"undefined"!=typeof WeixinJSBridge&&WeixinJSBridge.log&&WeixinJSBridge.log(t);
}
function o(t,i){
n("http://mp.weixin.qq.com/mp/ad_report?action=follow&type="+t+i.report_param);
}
function e(t){
var e=t.btn;
if(!e)return!1;
var n=t.adData,p=!1,c={};
t.report_param=t.report_param||"";
var d="http://"+location.host+"/mp/ad_redirect?url="+encodeURIComponent(n.appinfo_url)+"&uin"+uin+"&ticket="+(t.ticket||window.ticket);
r.on(e,"click",function(){
if(i("click @js_app_action"),p)return i("is_app_installed"),o(n.is_appmsg?17:13,t),
void(location.href=n.app_id+"://");
var e=function(){
i("download"),o(n.is_appmsg?15:11,t),i("go : "+d),location.href=d;
};
return i("download"),n.rl&&n.traceid?c[n.traceid]||(c[n.traceid]=!0,a({
url:"/mp/advertisement_report?report_type=2&type="+n.type+"&url="+encodeURIComponent(n.appinfo_url)+"&tid="+n.traceid+"&rl="+encodeURIComponent(n.rl)+"&pt="+n.pt+t.report_param,
type:"GET",
timeout:1e3,
complete:function(){
i("ready to download"),c[n.traceid]=!1,e();
},
async:!0
})):e(),!1;
});
}
{
var r=t("biz_common/dom/event.js"),n=t("biz_common/utils/report.js"),a=t("biz_wap/utils/ajax.js");
t("biz_wap/jsapi/core.js");
}
return e;
});define("a/android.js",["biz_common/dom/event.js","biz_common/utils/report.js","biz_wap/utils/ajax.js","biz_wap/jsapi/core.js"],function(n){
"use strict";
function a(n){
"undefined"!=typeof d&&d.log&&d.log(n);
}
function e(n,a){
o("http://mp.weixin.qq.com/mp/ad_report?action=follow&type="+n+a.report_param);
}
function t(n){
function t(){
d.invoke("getInstallState",{
packageName:s.pkgname
},function(n){
var a=n.err_msg;
a.indexOf("get_install_state:yes")>-1&&(window.clearInterval(y),g=!0,r.innerHTML=T.installed);
});
}
function o(){
j&&d.invoke("queryDownloadTask",{
download_id:j
},function(t){
if(t&&t.state){
if("download_succ"==t.state){
a("download_succ"),e(s.is_appmsg?18:14,n),window.clearInterval(b),k=!1,I=!0,r.innerHTML=T.downloaded;
var o=document.createEvent("MouseEvents");
o.initMouseEvent("click",!0,!0,window,0,0,0,0,0,!1,!1,!1,!1,0,null),r.dispatchEvent(o);
}
if("downloading"==t.state)return;
("download_fail"==t.state||"default"==t.state)&&(a("fail, download_state : "+t.state),
window.clearInterval(b),k=!1,alert("下载失败"),r.innerHTML=T.download);
}
});
}
var r=n.btn;
if(!r)return!1;
var l={},s=n.adData,c="",u="",m=s.androiddownurl;
if(m&&m.match){
var _=/&channelid\=([^&]*)/,p=m.match(_);
p&&p[1]&&(c="&channelid="+p[1],s.androiddownurl=m.replace(_,""));
}
n.via&&(u=["&via=ANDROIDWX.YYB.WX.ADVERTISE",n.via].join("."));
var f=!1,w="com.tencent.android.qqdownloader",v=1060125,g=!1,k=!1,I=!1,j=0,b=null,y=null,T={
download:"下载",
downloading:"下载中",
downloaded:"安装",
installed:"已安装"
};
r.innerHTML=T.download,d.ready(function(){
d.invoke("getInstallState",{
packageName:w
},function(n){
var e=n.err_msg;
a("getInstallState @yingyongbao : "+e);
var t=e.lastIndexOf("_")+1,o=e.substring(t);
1*o>=v&&e.indexOf("get_install_state:yes")>-1&&(f=!0);
}),d.invoke("getInstallState",{
packageName:s.pkgname
},function(n){
var e=n.err_msg;
a("getInstallState @"+s.pkgname+" : "+e);
var t=e.lastIndexOf("_")+1,o=e.substring(t);
1*o>=s.versioncode&&e.indexOf("get_install_state:yes")>-1&&(g=!0,r.innerHTML=T.installed);
}),r.addEventListener("click",function(){
if(a("click @js_app_action"),!k){
if(g)return!1;
if(I)return d.invoke("installDownloadTask",{
download_id:j,
file_md5:s.md5sum
},function(n){
var e=n.err_msg;
a("installDownloadTask : "+e),e.indexOf("install_download_task:ok")>-1?y=setInterval(t,1e3):alert("安装失败！");
}),!1;
var m=function(){
return f?(e(s.is_appmsg?16:12,n),void(location.href="tmast://download?oplist=1,2&pname="+s.pkgname+c+u)):void d.invoke("addDownloadTask",{
task_name:s.appname,
task_url:s.androiddownurl,
extInfo:n.task_ext_info,
file_md5:s.md5sum
},function(t){
var i=t.err_msg;
a("addDownloadTask : "+i),i.indexOf("add_download_task:ok")>-1?(e(s.is_appmsg?15:11,n),
k=!0,j=t.download_id,a("download_id : "+j),r.innerHTML=T.downloading,b=setInterval(o,1e3)):alert("调用下载器失败！");
});
};
return s.rl&&s.traceid?l[s.traceid]||(l[s.traceid]=!0,i({
url:"/mp/advertisement_report?report_type=2&type="+s.type+"&url="+encodeURIComponent(s.androiddownurl)+"&tid="+s.traceid+"&rl="+encodeURIComponent(s.rl)+"&__biz="+biz+"&pt="+s.pt+"&r="+Math.random(),
type:"GET",
timeout:1e3,
complete:function(){
l[s.traceid]=!1,m();
},
async:!0
})):m(),!1;
}
});
});
}
var o=(n("biz_common/dom/event.js"),n("biz_common/utils/report.js")),i=n("biz_wap/utils/ajax.js"),d=n("biz_wap/jsapi/core.js");
return t;
});define("a/profile.js",["biz_common/dom/event.js","biz_common/utils/report.js","biz_wap/utils/ajax.js","biz_wap/jsapi/core.js"],function(e){
"use strict";
function t(e,t){
a("http://mp.weixin.qq.com/mp/ad_report?action=follow&type="+e+t.report_param);
}
function n(e){
location.href=e;
}
function i(e){
var i=e.adData,c=e.pos_type||0,s={};
e.report_param=e.report_param||"",function(){
function m(){
var e=u.dataset;
if(e.rl&&e.url&&e.type&&e.tid){
var t=e.tid,n=e.type,i=e.url,o=e.rl;
s[t]||(s[t]=!0,r({
url:"/mp/advertisement_report?report_type=2&click_pos=0&type="+n+"&url="+encodeURIComponent(i)+"&tid="+t+"&pos_type="+c+"&rl="+encodeURIComponent(o)+"&uin="+uin+"&key="+key+"&__biz="+biz+"&pt=100&r="+Math.random(),
type:"GET",
timeout:1e3,
complete:function(){
s[t]=!1,_();
},
async:!0
}));
}else _();
}
var u=e.btnAddContact,d=e.btnViewProfile;
if(u&&u.dataset){
var l=function(o,r){
var c=o.err_msg,s=i.is_appmsg?6:1;
-1!=c.indexOf("ok")?(d.style.display="inline-block",u.style.display="none",s=i.is_appmsg?9:4):"add_contact:added"==c?s=i.is_appmsg?7:2:"add_contact:cancel"==c?s=i.is_appmsg?8:3:(--r,
r>=0?p.invoke("addContact",{
scene:scene,
webtype:"1",
username:i.usename
},function(e){
l(e,r);
}):(c="addContact:fail|msg:"+c+"|uin:"+uin+"|biz:"+biz,a("http://mp.weixin.qq.com/mp/jsreport?key=13&content="+c+"&r="+Math.random()),
n(i.url))),t(s,e);
},_=function(){
t(i.is_appmsg?10:5,e),p.invoke("addContact",{
scene:scene,
webtype:"1",
username:i.usename
},function(e){
l(e,1);
});
};
o.on(u,"click",m);
}
}(),function(){
var t=e.btnViewProfile;
t&&o.on(t,"click",function(){
return n(i.url),!1;
});
}();
}
var o=e("biz_common/dom/event.js"),a=e("biz_common/utils/report.js"),r=e("biz_wap/utils/ajax.js"),p=e("biz_wap/jsapi/core.js");
return i;
});define("biz_common/utils/report.js",[],function(){
"use strict";
return function(n){
var e=new Image;
e.src=n;
};
});define("biz_common/utils/cookie.js",[],function(){
"use strict";
var e={
get:function(e){
if(""==e)return"";
var t=new RegExp(e+"=([^;]*)"),n=document.cookie.match(t);
return n&&n[1]||"";
},
set:function(e,t){
var n=new Date;
n.setDate(n.getDate()+1);
var r=n.toGMTString();
return document.cookie=e+"="+t+";expires="+r,!0;
}
};
return e;
});define("pages/report.js",["biz_wap/utils/ajax.js","pages/version4video.js"],function(e){
"use strict";
function t(e){
var t=["/mp/pagereport?type=","undefined"==typeof e.type?1:e.type,"&comment_id=",e.comment_id,"&action=",e.action,"&__biz=",e.__biz||window.biz||"","&mid=",e.mid||window.mid||"","&idx=",e.idx||window.idx||"","&t=",Math.random(),"#wechat_redirect"].join(""),o=new Image;
o.src=t;
}
function o(e){
p({
type:"POST",
url:"/mp/videoreport?#wechat_redirect",
timeout:2e4,
async:!1,
data:e.data
});
}
function i(e){
p({
type:"POST",
url:"/mp/musicreport?#wechat_redirect",
timeout:2e4,
async:!1,
data:e.data
});
}
function n(e){
document.domain="qq.com";
var t=encodeURIComponent(top.window.location.href.replace(/(\?|&)(key|uin)=([\S\s]*?)(&|$)/g,"$1").replace(/&$/,"")),o=["http://btrace.qq.com/kvcollect?BossId=2973&Pwd=1557019983&step=1009&vid=","undefined"!=typeof e.vid?e.vid:"","&platform=",a(),"&val=","undefined"!=typeof e.val?e.val:"","&val1=","undefined"!=typeof e.val1?e.val1:"","&vurl=",encodeURIComponent(e.vurl),"&t=",Math.random(),"&url=",t].join(""),i=new Image;
i.src=o.substr(0,1024);
}
function d(e){
if(3==e.step||6==e.step||1999==e.step){
document.domain="qq.com";
var t=encodeURIComponent(top.window.location.href.replace(/(\?|&)(key|uin)=([\S\s]*?)(&|$)/g,"$1").replace(/&$/,"")),o=["http://btrace.qq.com/kvcollect?BossId=2973&Pwd=1557019983&step=",e.step,"&vid=","undefined"!=typeof e.vid?e.vid:"","&platform=",a(),"&loadwait=","undefined"!=typeof e.loadwait?e.loadwait:"","&val=","undefined"!=typeof e.val?e.val:"","&t=",Math.random(),"&url=",t].join(""),i=new Image;
i.src=o.substr(0,1024);
}
}
function a(){
return s.ipad?60101:s.is_android_phone?60301:s.iphone?60401:s.is_android_tablet?60501:"";
}
function r(){
return s.ipad?"v4010":s.is_android_phone?"v5010":s.iphone?"v3010":s.is_android_tablet?"v6010":"";
}
var p=e("biz_wap/utils/ajax.js"),c=e("pages/version4video.js"),s=c.device;
return{
report:t,
videoreport:o,
getPlatformType:a,
getsdtfrom:r,
getinfoReport:n,
qqvideo_common_report:d,
musicreport:i
};
});define("pages/love_comment.js",["biz_common/utils/string/html.js","biz_common/dom/event.js","biz_common/tmpl.js","biz_common/dom/class.js","biz_wap/utils/ajax.js"],function(e){
"use strict";
function t(e){
function t(){
if("function"==typeof document.querySelectorAll)for(var e=document.querySelectorAll("#comment_content span[data-time]"),t=0,n=e.length;n>t;t++){
var o=1*e[t].getAttribute("data-time");
e[t].innerText=_(o);
}
}
function a(){
c.tap(e.love_btn,f),"function"==typeof e.comment_input_focus&&c.on(e.comment_input,"focus",function(){
e.comment_input_focus();
}),c.on(e.comment_input,"input",function(){
l(e.comment_len_main);
var t=s(e.comment_input.innerText),n=v(t);
e.comment_len.innerText=n,n>140?m.addClass(e.comment_len,"warn"):m.removeClass(e.comment_len,"warn");
}),c.on(e.comment_submit_btn,"click",g,!1),c.tap(e.comment_content,b);
}
function l(e,t){
e.style.display=t?t:"block";
}
function u(e){
e.style.display="none";
}
function s(e){
return e.replace(/^\s+|\s+$/g,"");
}
function _(e){
var t=(new Date).getTime(),n=new Date;
n.setDate(n.getDate()+1),n.setHours(0),n.setMinutes(0),n.setSeconds(0),n=n.getTime();
var o=t/1e3-e,c=n/1e3-e,i=new Date(n).getFullYear(),m=new Date(1e3*e);
return 3600>o?Math.ceil(o/60)+"分钟前":86400>c?Math.floor(o/60/60)+"小时前":172800>c?"昨天":604800>c?Math.floor(c/24/60/60)+"天前":m.getFullYear()==i?m.getMonth()+1+"月"+m.getDate()+"日":m.getFullYear()+"年"+(m.getMonth()+1)+"月"+m.getDate()+"日";
}
function d(){
var t=e.love_text;
t.innerText="undefined"!=typeof T.loverCount&&1*T.loverCount>0?1*T.loverCount:"赞";
}
function f(){
var t=e.love_content,o=e.love_img_content,c="1";
if(m.hasClass(t,e.love_class))m.removeClass(t,e.love_class),o.removeChild(o.children[0]),
"undefined"!=typeof T.loverCount&&(T.loverCount-=1),c="0";else{
m.addClass(t,e.love_class);
var r=document.createElement("div");
r.innerHTML=i.render(e.love_img_tpl,{
imgurl:e.head_img_url
}),o.insertBefore(r.children[0],o.children[0]),"undefined"!=typeof T.loverCount&&(T.loverCount+=1),
c="1";
}
return d(),n({
like:c,
comment_id:e.comment_id,
reportType:e.reportType,
action:2
}),"function"==typeof e.love_callback&&e.love_callback(),!1;
}
function p(){
var t=s(e.comment_input.innerText),n=v(t);
return 1>n?(alert("评论不能为空"),!1):n>140?(alert("字数不能多于140个"),!1):!0;
}
function v(e){
return e?Math.ceil(e.replace(/[^\x00-\xff]/g,"01").length/2):0;
}
function b(t){
if(!T.comment_deleting){
var n=t.target||t.srcElement;
if(-1!=n.className.indexOf("js_del_comment")){
var o=e.comment_content.children[0].nodeName;
if(n.nodeName!=o)for(;n=n.parentNode;){
if(n==e.comment_content||n==document.body||n==document){
n=null;
break;
}
if(n.nodeName==o)break;
}
if(null!==n){
var c=n.getAttribute("data-contentid");
if(c=c||c+""=="0"?c:null,null!==c)return window.confirm("确定要删除评论？")!==!0?!1:(T.comment_deleting=!0,
r({
type:"POST",
url:"/mp/interestcomment?#wechat_redirect",
timeout:2e4,
data:{
action:"delete",
comment_id:e.comment_id,
comment_content_id:c,
type:e.reportType
},
success:function(t){
T.comment_deleting=!1;
try{
var t=JSON.parse(t);
}catch(o){
return void alert("系统繁忙，请稍后再试");
}
if(!t||!t.base_resp)return void alert("系统繁忙，请稍后再试");
switch(1*t.base_resp.ret){
case 0:
e.toast_content.innerText="删除成功",l(e.comment_toast),e.comment_content.removeChild(n),
setTimeout(function(){
u(e.comment_toast);
},1500);
break;

default:
alert("系统繁忙，请稍后再试");
}
},
error:function(){
alert("系统繁忙，请稍后再试"),T.comment_deleting=!1;
}
}),!1);
}
}
}
}
function g(){
if(!T.commenting&&p()!==!1){
T.addComment=!1,T.commenting=!0;
var t=s(e.comment_input.innerText);
return r({
type:"POST",
url:"/mp/interestcomment?#wechat_redirect",
timeout:2e4,
data:{
action:"post",
comment_id:e.comment_id,
content:t,
type:e.reportType
},
success:function(t){
try{
var t=JSON.parse(t);
}catch(n){
return void alert("系统繁忙，请稍后再试");
}
if(!t||!t.base_resp)return void alert("系统繁忙，请稍后再试");
switch(1*t.base_resp.ret){
case 0:
e.toast_content.innerText="已评论",l(e.comment_toast),setTimeout(function(){
e.comment_input.blur(),u(e.comment_toast),h(t),e.comment_input.innerText="",e.comment_len.innerText=0,
m.removeClass(e.comment_len,"warn"),"function"==typeof e.comment_submit_callback&&e.comment_submit_callback(t),
T.addComment=!0,T.commenting=!1;
},1500);
break;

default:
y(),alert("系统繁忙，请稍后再试");
}
},
error:function(){
alert("系统繁忙，请稍后再试"),y();
}
}),!1;
}
}
function y(){
T.commenting=!1;
}
function h(t){
var n=s(e.comment_input.innerText),n=n.htmlEncode().replace(/\n/g,"<br>"),o=document.createElement("div");
o.innerHTML=i.render(e.comment_tpl,{
imgurl:e.head_img_url,
content:n,
nickname:"我",
comment_content_id:t.comment_content_id
}),e.comment_content.insertBefore(o.children[0],e.comment_content.children[0]);
}
var T={
curIndex:0,
pageCount:10,
addComment:!1
};
return t(),a(),o({
comment_id:e.comment_id,
callback:function(e){
var t=e.topic_list[0];
t&&(T.loverCount=t.like_count,d());
}
}),{};
}
function n(e){
r({
type:"POST",
url:"/mp/interestlike?#wechat_redirect",
data:{
comment_id:e.comment_id,
like:1*e.like===1?1:0,
type:"undefined"==typeof e.reportType?1:e.reportType,
action:"undefined"==typeof e.action?1:e.action
},
timeout:2e4
});
}
function o(e){
var t="/mp/interestlike?comment_id="+e.comment_id+"#wechat_redirect";
r({
type:"GET",
url:t,
timeout:2e4,
success:function(t){
try{
var t=JSON.parse(t);
}catch(n){
return;
}
!t||!t.base_resp||0!=t.base_resp.ret||t.topic_list.length<=0||0==t.topic_list[0].topic_id||"function"==typeof e.callback&&e.callback(t);
}
});
}
e("biz_common/utils/string/html.js");
var c=e("biz_common/dom/event.js"),i=e("biz_common/tmpl.js"),m=e("biz_common/dom/class.js"),r=e("biz_wap/utils/ajax.js");
return{
init:t,
love_request:n,
getLoveCount:o
};
});define("biz_wap/utils/localstorage.js",[],function(){
"use strict";
var t={};
return t=window.localStorage?{
set:function(t,e){
null!==this.get(t)&&this.remove(t),localStorage.setItem(t,e);
},
get:function(t){
var e=localStorage.getItem(t);
return void 0===e?null:e;
},
remove:function(t){
localStorage.removeItem(t);
},
clear:function(){
localStorage.clear();
},
each:function(t){
for(var e,o=localStorage.length,l=0,t=t||function(){};o>l&&(e=localStorage.key(l),
t.call(this,e,this.get(e))!==!1);l++)localStorage.length<o&&(o--,l--);
}
}:{
set:function(){},
get:function(){}
};
});define("biz_wap/utils/qqmusic_player.js",[],function(){
"use strict";
function t(t){
function e(t){
var e=t.playurl||"";
t.mid_str?e="http://thirdparty.gtimg.com/C100"+t.mid_str+".m4a?fromtag=38":t.mid_int&&(e="http://thirdparty.gtimg.com/"+t.mid_int+".m4a?fromtag=38"),
f&&f.src==e?i():l?(f&&"function"==typeof f.pause&&(f.pause(),document.body.removeChild(f)),
setTimeout(function(){
var t=document.createElement("audio");
c(t),t.setAttribute("style","height:0;width:0;display:none"),t.setAttribute("autoplay",""),
t.src=e,document.body.appendChild(t),t.load(),f=t;
},50)):(f.src=e,f.play());
}
function n(){
f&&"function"==typeof f.pause&&f.pause();
}
function i(){
f&&(f.ended||f.paused?(f.ended&&(f.currentTime=0),f.play()):f.pause());
}
function o(t){
if("object"==typeof f)try{
f.currentTime=parseInt(t);
}catch(e){
console.log("set audio currentTime error : "+e);
}
}
function u(){
if("object"==typeof f)try{
return f.currentTime;
}catch(t){
return 0;
}
}
function a(){
return f;
}
function r(t,e){
for(var n in e)t[n]=e[n];
return t;
}
function c(t){
y.needVioceMutex&&_vioceMutexList.push(t),t.addEventListener("play",function(t){
try{
d();
}catch(t){}
"function"==typeof y.play&&y.play(t);
},!1),t.addEventListener("ended",function(t){
"function"==typeof y.ended&&y.ended(t);
},!1),t.addEventListener("pause",function(t){
"function"==typeof y.pause&&y.pause(t);
},!1),t.addEventListener("timeupdate",function(t){
"function"==typeof y.timeupdate&&y.timeupdate(t);
},!1);
}
function d(){
if(y.needVioceMutex)for(var t=0,e=_vioceMutexList.length;e>t;t++){
var n=_vioceMutexList[t];
n&&n!=f&&"function"==typeof n.pause&&n.pause();
}
}
function p(t){
if(s)return void("object"==typeof t&&(y=r(y,t)));
y=r(y,t),s=!0;
var e=document.createElement("audio");
f=e,c(e),e.setAttribute("style","height:0;width:0;display:none"),e.setAttribute("autoplay",""),
document.body.appendChild(e),l&&window.addEventListener("canplay",function(t){
t.target&&"function"==typeof t.target.play&&t.target.play();
},!0);
}
var f=null,s=!1,y={
needVioceMutex:!0
},l=!1;
return/android\s2\.3/i.test(navigator.userAgent)&&(l=!0),"undefined"==typeof window._vioceMutexList&&(window._vioceMutexList=[]),
p("object"==typeof t?t:{}),{
play:e,
pause:n,
toggle:i,
setPlayTime:o,
getPlayTime:u,
getAudioObj:a,
init:p
};
}
return t;
});define("appmsg/reward_entry.js",["biz_common/dom/event.js","biz_wap/utils/ajax.js"],function(e){
"use strict";
function n(e){
e&&(e.style.display="block");
}
function t(e){
e&&(e.style.display="none");
}
function o(e){
var o=window.innerWidth||document.documentElement.innerWidth,r=(Math.ceil((c-188)/42)+1)*Math.floor((o-15)/42);
l="/mp/reward?act=getrewardheads&__biz="+biz+"&appmsgid="+mid+"&idx="+idx+"&sn="+sn+"&offset=0&count="+r+"&source=1#wechat_redirect";
var i="&uin="+encodeURIComponent(window.uin)+"&key="+encodeURIComponent(window.key)+"&pass_ticket="+encodeURIComponent(window.pass_ticket),w=document.getElementById("js_reward_link");
w&&(w.href="https://mp.weixin.qq.com/bizmall/reward?__biz="+biz+"&appmsgid="+mid+"&idx="+idx+"&sn="+sn+"&timestamp="+e.timestamp+"&showwxpaytitle=1"+i),
u=e.reward_head_imgs,_=e.self_head_img;
var p=d();
m.reward&&1==e.can_reward?(n(m.reward),s.on(window,"load",function(){
s.on(window,"scroll",a);
})):t(m.reward);
var f=document.getElementById("js_reward_inner");
f&&p>0&&n(f);
var g=document.getElementById("js_reward_total");
g&&(g.innerText=e.reward_total,g.setAttribute("href",l));
}
function r(e,n){
var t=document.createElement("span");
t.className="reward_user_avatar";
var o=new Image;
return o.onload=function(){
window.logs&&window.logs.reward_heads_total++,o.onload=o.onerror=null;
},o.onerror=function(){
window.logs&&window.logs.reward_heads_total++,window.logs&&window.logs.reward_heads_fail++,
o.onload=o.onerror=null;
},o.src=n,t.appendChild(o),e.appendChild(t),t;
}
function d(){
if(u.length||_){
var e=document.getElementById("js_reward_list"),n=0,t=document.createDocumentFragment();
if(e){
_&&(n++,r(t,_));
for(var o=0,d=u.length;d>o&&(n++,r(t,u[o]),n!=3*i);++o);
n>i&&(e.className+=" tl"),e.innerHTML="",e.appendChild(t);
}
return n;
}
}
function a(){
var e=window.pageYOffset||document.documentElement.scrollTop;
e+c>m.reward.offsetTop&&(w({
type:"GET",
url:"/bizmall/reward?act=report&__biz="+biz+"&appmsgid="+mid+"&idx="+idx,
async:!0
}),s.off(window,"scroll",a),a=null);
}
var i,l,s=e("biz_common/dom/event.js"),w=e("biz_wap/utils/ajax.js"),c=window.innerHeight||document.documentElement.clientHeight,m={
reward:document.getElementById("js_reward_area")
},u=[],_=null;
return window.logs&&(window.logs.reward_heads_total=0,window.logs.reward_heads_fail=0),
{
handle:function(e,n){
i=n,o(e);
},
render:function(e){
i=e,d();
}
};
});define("appmsg/comment.js",["biz_common/dom/event.js","biz_common/dom/class.js","biz_wap/utils/ajax.js","biz_common/utils/string/html.js","biz_common/tmpl.js"],function(e){
"use strict";
function t(e,t){
e.style.display=t?t:"block";
}
function n(e){
e.style.display="none";
}
function m(){
setTimeout(function(){
t(q.toast);
},750),setTimeout(function(){
n(q.toast);
},1500);
}
function o(e){
return e.replace(/^\s+|\s+$/g,"");
}
function i(){
clearTimeout(B),B=setTimeout(function(){
if(!v&&-1!=I){
var e=window.innerHeight||document.documentElement.clientHeight,m=window.pageYOffset||document.documentElement.scrollTop,o=document.documentElement.scrollHeight;
if(!(I>0&&o-m-e>500)){
v=!0,n(q.tips),t(q.loading);
var i="/mp/appmsg_comment?action=getcomment&__biz="+biz+"&appmsgid="+appmsgid+"&idx="+idx+"&comment_id="+comment_id+"&offset="+I+"&limit="+E;
try{
N++,N>1&&((new Image).src="http://mp.weixin.qq.com/mp/jsreport?key=27&content="+encodeURIComponent(i)),
D.indexOf(i)>-1&&((new Image).src="http://mp.weixin.qq.com/mp/jsreport?key=25&content="+encodeURIComponent(i)),
D.push(i);
}catch(s){}
j({
url:i,
type:"get",
success:function(e){
var t={};
try{
t=window.eval.call(window,"("+e+")");
}catch(n){}
var m=t.base_resp&&t.base_resp.ret;
0==m?c(t):w.src="http://mp.weixin.qq.com/mp/jsreport?key=18&content=type:resperr;url:"+encodeURIComponent(i)+";ret="+m+"&r="+Math.random();
},
error:function(){
w.src="http://mp.weixin.qq.com/mp/jsreport?key=18&content=type:ajaxerr;url:"+encodeURIComponent(i)+"&r="+Math.random();
},
complete:function(){
v=!1,n(q.loading);
}
});
}
}
},50);
}
function c(e){
var m,o=document.createDocumentFragment();
O++,O>1&&(M.src="http://mp.weixin.qq.com/mp/jsreport?key=26&content="+encodeURIComponent(JSON.stringify({
comment_id:comment_id,
offset:I,
url:location.href
}))),0==I?(k=e.logo_url,C=e.nick_name,m=e.elected_comment,m&&m.length?(l(m,o,"elected"),
q.list.appendChild(o),t(q.main),1!=e.is_fans?t(document.getElementById("js_cmt_nofans1"),"block"):t(document.getElementById("js_cmt_addbtn1")),
e.elected_comment_total_cnt<=10&&(t(document.getElementById("js_cmt_statement")),
t(document.getElementById("js_cmt_qa")))):(n(q.main),t(1!=e.is_fans?document.getElementById("js_cmt_nofans2"):document.getElementById("js_cmt_addbtn2"))),
function(){
var e=location.href.indexOf("scrolltodown")>-1?!0:!1,t=(document.getElementById("img-content"),
document.getElementById("js_cmt_area"));
if(e&&t&&t.offsetTop){
var n=t.offsetTop;
window.scrollTo(0,n-25);
}
}()):(m=e.elected_comment,m&&m.length&&(l(m,o,"elected"),q.list.appendChild(o))),
0==e.elected_comment_total_cnt?(I=-1,f.off(window,"scroll",i),n(document.getElementById("js_cmt_loading")),
n(document.getElementById("js_cmt_statement")),n(document.getElementById("js_cmt_qa"))):I+E>=e.elected_comment_total_cnt?(I=-1,
f.off(window,"scroll",i),n(document.getElementById("js_cmt_loading")),t(document.getElementById("js_cmt_statement")),
t(document.getElementById("js_cmt_qa"))):I+=e.elected_comment.length;
}
function s(){
var e=o(q.input.value);
if(!h.hasClass(q.submit,"btn_disabled")){
if(e.length<1)return d("评论不能为空");
if(e.length>600)return d("字数不能多于600个");
h.addClass(q.submit,"btn_disabled");
var n=document.getElementById("activity-name"),i="/mp/appmsg_comment?action=addcomment&comment_id="+comment_id+"&__biz="+biz+"&idx="+idx+"&appmsgid="+appmsgid+"&sn="+sn;
j({
url:i,
data:{
content:e,
title:n&&o(n.innerText),
head_img:k,
nickname:C
},
type:"POST",
success:function(n){
var o={},c=document.createDocumentFragment();
try{
o=window.eval.call(window,"("+n+")");
}catch(s){}
switch(+o.ret){
case 0:
m(),l([{
content:e,
nick_name:C,
create_time:(new Date).getTime()/1e3|0,
is_elected:0,
logo_url:k,
like_status:0,
content_id:0,
like_num_format:0,
like_num:0,
is_from_friend:0,
is_from_me:1,
my_id:o.my_id
}],c,"mine"),q.mylist.insertBefore(c,q.mylist.firstChild),t(q.mylist.parentNode),
q.input.value="";
break;

case-6:
d("你评论的太频繁了，休息一下吧");
break;

case-7:
d("你还未关注该公众号，不能参与评论");
break;

case-10:
d("字数不能多于600个");
break;

case-15:
d("评论已关闭");
break;

default:
d("系统错误，请重试");
}
0!=o.ret&&(w.src="http://mp.weixin.qq.com/mp/jsreport?key=19&content=type:resperr;url:"+encodeURIComponent(i)+";ret="+o.ret+"&r="+Math.random());
},
error:function(){
w.src="http://mp.weixin.qq.com/mp/jsreport?key=19&content=type:ajaxerr;url:"+encodeURIComponent(i)+"&r="+Math.random();
},
complete:function(){
""!=q.input.value&&h.removeClass(q.submit,"btn_disabled");
}
});
}
}
function a(){
if(0==x){
var e="/mp/appmsg_comment?action=getmycomment&__biz="+biz+"&appmsgid="+appmsgid+"&idx="+idx+"&comment_id="+comment_id,m=document.getElementById("js_mycmt_loading");
x=1,t(m),j({
url:e,
type:"get",
success:function(n){
var m={};
try{
m=window.eval.call(window,"("+n+")");
}catch(o){}
var i=m.base_resp&&m.base_resp.ret;
if(0==i){
var c=m.my_comment,s=document.createDocumentFragment();
c&&c.length&&(l(c,s,"mine"),q.mylist.appendChild(s),t(q.mylist.parentNode)),x=2;
}else x=0,w.src="http://mp.weixin.qq.com/mp/jsreport?key=18&content=type:resperr;url:"+encodeURIComponent(e)+";ret="+i+"&r="+Math.random();
},
error:function(){
x=0,w.src="http://mp.weixin.qq.com/mp/jsreport?key=18&content=type:ajaxerr;url:"+encodeURIComponent(e)+"&r="+Math.random();
},
complete:function(){
n(m);
}
});
}
}
function r(e){
var t=(new Date).getTime(),n=new Date;
n.setDate(n.getDate()+1),n.setHours(0),n.setMinutes(0),n.setSeconds(0),n=n.getTime();
var m=t/1e3-e,o=n/1e3-e,i=new Date(n).getFullYear(),c=new Date(1e3*e);
return 3600>m?Math.ceil(m/60)+"分钟前":86400>o?Math.floor(m/60/60)+"小时前":172800>o?"昨天":604800>o?Math.floor(o/24/60/60)+"天前":c.getFullYear()==i?c.getMonth()+1+"月"+c.getDate()+"日":c.getFullYear()+"年"+(c.getMonth()+1)+"月"+c.getDate()+"日";
}
function l(e,t,n){
var m,o="",i=document.createElement("div"),c="http://mmbiz.qpic.cn/mmbiz/ByCS3p9sHiak6fjSeA7cianwo25C0CIt5ib8nAcZjW7QT1ZEmUo4r5iazzAKhuQibEXOReDGmXzj8rNg/0";
T={};
for(var s,a=0;s=e[a];++a){
s.time=r(s.create_time),s.status="",s.logo_url=s.logo_url||c,s.logo_url=-1!=s.logo_url.indexOf("wx.qlogo.cn")?s.logo_url.replace(/\/132$/,"/96"):s.logo_url,
s.content=s.content.htmlDecode().htmlEncode(),s.nick_name=s.nick_name.htmlDecode().htmlEncode(),
s.like_num_format=parseInt(s.like_num)>=1e4?(s.like_num/1e4).toFixed(1)+"万":s.like_num,
s.is_from_friend=s.is_from_friend||0,s.is_from_me="mine"==n?1:s.is_from_me||0,s.reply=s.reply||{
reply_list:[]
},s.is_mine=n?!1:!0,s.is_elected="elected"==n?1:s.is_elected,s.reply.reply_list.length>0&&(s.reply.reply_list[0].time=r(s.reply.reply_list[0].create_time)),
o+=b.render("t_cmt",s);
try{
var l=s.nick_name+s.content,d=!1,_=23;
T[l]&&(d=!0,_=24),z.indexOf(s.content_id)>-1&&(d=!0,_=23),z.push(s.content_id),T[l]=!0,
d&&(M.src="http://mp.weixin.qq.com/mp/jsreport?key="+_+"&content="+encodeURIComponent(JSON.stringify({
comment_id:comment_id,
content_id:s.content_id,
offset:I,
length:e.length,
url:location.href
})));
}catch(p){}
}
for(i.innerHTML=o;m=i.children.item(0);)t.appendChild(m);
}
function d(e){
return setTimeout(function(){
alert(e);
});
}
function _(e){
"#comment"==location.hash?(n(q.article),t(q.mine),window.scrollTo(0,0),a()):"onload"!=e&&(n(q.mine),
t(q.article),window.scrollTo(0,document.documentElement.scrollHeight)),q.input.blur();
}
function p(e){
var t=e.target||e.srcElement,n=null;
if(h.hasClass(t,"js_comment_praise")&&(n=t),h.hasClass(t,"icon_praise_gray")&&"i"==t.nodeName.toLowerCase()&&(n=t.parentElement),
h.hasClass(t,"praise_num")&&"span"==t.nodeName.toLowerCase()&&(n=t.parentElement),
n){
var m=parseInt(n.dataset.status),o=0==m?1:0,i=n.dataset.contentId,c="/mp/appmsg_comment?action=likecomment&&like="+o+"&__biz="+biz+"&appmsgid="+appmsgid+"&comment_id="+comment_id+"&content_id="+i;
u(n),j({
url:c,
type:"GET"
});
}
}
function u(e){
var t=h.hasClass(e,"praised"),n=e.querySelector(".praise_num"),m=n.innerHTML,o=m.indexOf("万"),i=parseInt(m)?parseInt(m):0;
t?(-1==o&&(n.innerHTML=i-1>0?i-1:""),h.removeClass(e,"praised"),e.dataset.status=0):(-1==o&&(n.innerHTML=i+1),
h.addClass(e,"praised"),e.dataset.status=1);
}
function g(e){
var m=e.delegatedTarget,o=m.getAttribute("data-my-id"),i="/mp/appmsg_comment?action=delete&__biz="+biz+"&appmsgid="+appmsgid+"&comment_id="+comment_id+"&my_id="+o;
confirm("确定删除吗？")&&(j({
url:i,
success:function(e){
var i,c=m;
try{
e=JSON.parse(e);
}catch(s){
e={};
}
if(0==e.ret){
for(;c&&(c.nodeType!=c.ELEMENT_NODE||"li"!=c.tagName.toLowerCase());)c=c.parentNode;
c&&(c.parentNode.removeChild(c),i=document.getElementById("cid"+o),i&&i.parentNode.removeChild(i),
0==q.list.children.length&&(n(q.main),n(document.getElementById("js_cmt_statement")),
n(document.getElementById("js_cmt_qa")),t(document.getElementById("js_cmt_addbtn2"))),
0==q.mylist.children.length&&n(q.mylist.parentNode));
}else alert("删除失败，请重试");
},
error:function(){
alert("网络错误，请重试");
}
}),q.input.focus());
}
var y=document.getElementById("js_cmt_area");
if(0!=comment_id&&uin&&key){
if(-1==navigator.userAgent.indexOf("MicroMessenger"))return void(y&&(y.style.display="none"));
y&&(y.style.display="block");
var f=e("biz_common/dom/event.js"),h=e("biz_common/dom/class.js"),j=e("biz_wap/utils/ajax.js"),b=(e("biz_common/utils/string/html.js"),
e("biz_common/tmpl.js")),w=new Image,I=0,E=50,v=!1,B=null,k="",C="我",x=0,q={
article:document.getElementById("js_article"),
more:document.getElementById("js_cmt_more"),
mine:document.getElementById("js_cmt_mine"),
main:document.getElementById("js_cmt_main"),
input:document.getElementById("js_cmt_input"),
submit:document.getElementById("js_cmt_submit"),
addbtn:document.getElementById("js_cmt_addbtn"),
list:document.getElementById("js_cmt_list"),
mylist:document.getElementById("js_cmt_mylist"),
morelist:document.getElementById("js_cmt_morelist"),
toast:document.getElementById("js_cmt_toast"),
tips:document.getElementById("js_cmt_tips"),
loading:document.getElementById("js_cmt_loading")
},z=[],T={},M=new Image,D=[],N=0,O=0;
!function(){
i(),_("onload");
}(),f.on(window,"hashchange",_),f.on(q.input,"input",function(){
var e=o(q.input.value);
e.length<1?h.addClass(q.submit,"btn_disabled"):h.removeClass(q.submit,"btn_disabled");
}),f.on(q.more,"touchend",p),f.on(q.list,"touchend",p),f.on(q.mylist,"touchend",p),
f.on(q.list,"tap",".js_del",g),f.on(q.mylist,"tap",".js_del",g),f.on(q.submit,"touchend",s);
}
});define("appmsg/like.js",["biz_common/dom/event.js","biz_common/dom/class.js","biz_wap/utils/ajax.js"],function(require,exports,module){
"use strict";
function like_report(e){
var tmpAttr=el_like.getAttribute("like"),tmpHtml=el_likeNum.innerHTML,isLike=parseInt(tmpAttr)?parseInt(tmpAttr):0,like=isLike?0:1,likeNum=parseInt(tmpHtml)?parseInt(tmpHtml):0;
ajax({
url:"/mp/appmsg_like?__biz="+biz+"&mid="+mid+"&idx="+idx+"&like="+like+"&f=json&appmsgid="+appmsgid+"&itemidx="+itemidx,
type:"GET",
timeout:2e3,
success:function(res){
var data=eval("("+res+")");
0==data.base_resp.ret&&(isLike?(Class.removeClass(el_like,"praised"),el_like.setAttribute("like",0),
likeNum>0&&"100000+"!==tmpHtml&&(el_likeNum.innerHTML=likeNum-1==0?"赞":likeNum-1)):(el_like.setAttribute("like",1),
Class.addClass(el_like,"praised"),"100000+"!==tmpHtml&&(el_likeNum.innerHTML=likeNum+1)));
},
async:!0
});
}
var DomEvent=require("biz_common/dom/event.js"),Class=require("biz_common/dom/class.js"),ajax=require("biz_wap/utils/ajax.js"),el_toolbar=document.getElementById("js_toobar"),el_like=el_toolbar.querySelector("#like"),el_likeNum=el_toolbar.querySelector("#likeNum"),el_readNum=el_toolbar.querySelector("#readNum");
DomEvent.on(el_like,"click",function(e){
return like_report(e),!1;
});
});define("appmsg/a.js",["biz_common/dom/event.js","biz_common/utils/url/parse.js","biz_wap/utils/ajax.js","biz_wap/jsapi/core.js","a/profile.js","a/android.js","a/ios.js","a/gotoappdetail.js"],function(require,exports,module){
"use strict";
function ad_click(e,a,t,i,o,n,p,r,s,d,_,l){
if(!has_click[o]){
has_click[o]=!0;
var c=document.getElementById("loading_"+o);
c&&(c.style.display="inline"),ajax({
url:"/mp/advertisement_report?report_type=2&click_pos=1&type="+e+"&url="+encodeURIComponent(a)+"&tid="+o+"&rl="+encodeURIComponent(t)+"&__biz="+biz+"&pos_type="+d+"&pt="+s+"&r="+Math.random(),
type:"GET",
timeout:1e3,
complete:function(){
if(has_click[o]=!1,c&&(c.style.display="none"),"5"==e)location.href="/mp/profile?source=from_ad&tousername="+a+"&ticket="+n+"&uin="+uin+"&key="+key+"&__biz="+biz+"&mid="+mid+"&idx="+idx+"&tid="+o;else{
if(0==a.indexOf("https://itunes.apple.com/")||0==a.indexOf("http://itunes.apple.com/")){
var t=require("biz_wap/jsapi/core.js");
return t.invoke("downloadAppInternal",{
appUrl:a
},function(e){
e.err_msg&&-1!=e.err_msg.indexOf("ok")||(location.href="http://"+location.host+"/mp/ad_redirect?url="+encodeURIComponent(a)+"&ticket="+n+"&uin="+uin);
}),!1;
}
if(-1==a.indexOf("mp.weixin.qq.com"))a="http://"+location.host+"/mp/redirect?url="+encodeURIComponent(a);else if(-1==a.indexOf("mp.weixin.qq.com/s")&&-1==a.indexOf("mp.weixin.qq.com/mp/appmsg/show")){
var i={
source:4,
tid:o,
idx:idx,
mid:mid,
appuin:biz,
pt:s,
aid:r,
ad_engine:_,
pos_type:d
};
if("104"==s&&l){
var p=l.pkgname&&l.pkgname.replace(/\./g,"_");
i={
source:4,
traceid:o,
mid:mid,
idx:idx,
appuin:biz,
pt:s,
aid:r,
engine:_,
pos_type:d,
pkgname:p
};
}
a=URL.join(a,i);
}
location.href=a;
}
},
async:!0
});
}
}
var js_bottom_ad_area=document.getElementById("js_bottom_ad_area"),js_top_ad_area=document.getElementById("js_top_ad_area"),pos_type=window.pos_type||0,adDatas=window.adDatas,total_pos_type=2,el_gdt_areas={
pos_1:js_top_ad_area,
pos_0:js_bottom_ad_area
},gdt_as={
pos_1:js_top_ad_area.getElementsByClassName("js_ad_link"),
pos_0:js_bottom_ad_area.getElementsByClassName("js_ad_link")
};
if(!document.getElementsByClassName||-1==navigator.userAgent.indexOf("MicroMessenger"))return js_top_ad_area.style.display="none",
js_bottom_ad_area.style.display="none",!1;
var has_click={},DomEvent=require("biz_common/dom/event.js"),URL=require("biz_common/utils/url/parse.js"),ajax=require("biz_wap/utils/ajax.js"),ping_apurl={
pos_0:!1,
pos_1:!1
},innerHeight=window.innerHeight||document.documentElement.clientHeight,ad_engine=0;
if(adDatas.num>0){
var onScroll=function(){
for(var scrollTop=window.pageYOffset||document.documentElement.scrollTop,i=0;total_pos_type>i;++i)!function(i){
var pos_key="pos_"+i;
if(!ping_apurl[pos_key]){
var gdt_a=gdt_as[pos_key];
if(gdt_a=!!gdt_a&&gdt_a[0],gdt_a&&gdt_a.dataset&&gdt_a.dataset.apurl){
var gid=gdt_a.dataset.gid,tid=gdt_a.dataset.tid,apurl=gdt_a.dataset.apurl,pos_type=adDatas.ads[pos_key].a_info.pos_type,gdt_area=el_gdt_areas[pos_key],offsetTop=gdt_area.offsetTop;
adDatas.ads[pos_key].ad_engine=0,-1!=apurl.indexOf("ad.wx.com")&&(adDatas.ads[pos_key].ad_engine=1),
(0==pos_type&&scrollTop+innerHeight>offsetTop||1==pos_type&&(10>=scrollTop||scrollTop-10>=offsetTop))&&(ping_apurl[pos_key]=!0,
ajax({
url:"/mp/advertisement_report?report_type=1&tid="+tid+"&adver_group_id="+gid+"&apurl="+encodeURIComponent(apurl)+"&__biz="+biz+"&pos_type="+pos_type+"&r="+Math.random(),
success:function(res){
try{
res=eval("("+res+")");
}catch(e){
res={};
}
res&&0!=res.ret?ping_apurl[pos_key]=!1:ping_apurl.pos_0&&ping_apurl.pos_1&&DomEvent.off(window,"scroll",onScroll);
},
async:!0
}));
}
}
}(i);
};
DomEvent.on(window,"scroll",onScroll),onScroll();
}
for(var i=0;total_pos_type>i;++i)!function(e){
var a="pos_"+e,t=el_gdt_areas[a];
if(!t.getElementsByClassName)return t.style.display="none",!1;
var i=t.getElementsByClassName("js_ad_link")||[],o=adDatas.ads[a];
if(o){
for(var n=o.adData,p=o.a_info,r=p.pos_type,s=o.ad_engine,d=0,_=i.length;_>d;++d)!function(e,a){
var t=i[e],o=t.dataset,n=o.type,p=o.url,d=o.rl,_=o.apurl,l=o.tid,c=o.ticket,m=o.group_id,u=o.aid,g=o.pt;
DomEvent.on(t,"click",function(e){
var t=!!e&&e.target;
return t&&t.className&&-1!=t.className.indexOf("js_ad_btn")?void 0:(ad_click(n,p,d,_,l,c,m,u,g,r,s,a),
!1);
},!0);
}(d,n);
if(n){
n.adid=window.adid||n.adid;
var l="&tid="+n.traceid+"&uin="+uin+"&key="+key+"&__biz="+biz+"&source="+source+"&scene="+scene+"&appuin="+biz+"&aid="+n.adid+"&ad_engine="+s+"&pos_type="+r+"&r="+Math.random();
if("100"==n.pt){
var c=require("a/profile.js");
return void new c({
btnViewProfile:document.getElementById("js_view_profile_"+r),
btnAddContact:document.getElementById("js_add_contact_"+r),
adData:n,
pos_type:r,
report_param:l
});
}
if("102"==n.pt){
var m=require("a/android.js"),u=15,g=n.pkgname&&n.pkgname.replace(/\./g,"_");
return void new m({
btn:document.getElementById("js_app_action_"+r),
adData:n,
report_param:l,
task_ext_info:[n.adid,n.traceid,g,source,u,s].join("."),
via:[n.traceid,n.adid,g,source,u,s].join(".")
});
}
if("101"==n.pt){
var y=require("a/ios.js");
return void new y({
btn:document.getElementById("js_app_action_"+r),
adData:n,
ticket:n.ticket,
report_param:l
});
}
if("103"==n.pt||"104"==n.pt){
var f=require("a/gotoappdetail.js"),u=15,g=n.pkgname&&n.pkgname.replace(/\./g,"_");
return void new f({
btn:document.getElementById("js_appdetail_action_"+r),
js_app_rating:document.getElementById("js_app_rating_"+r),
adData:n,
report_param:l,
pos_type:r,
via:[n.traceid,n.adid,g,source,u,s].join("."),
ticket:n.ticket,
appdetail_params:["&aid="+n.adid,"traceid="+n.traceid,"pkgname="+g,"source="+source,"type="+u,"engine="+s,"appuin="+biz,"pos_type="+r,"ticket="+n.ticket,"scene="+scene].join("&")
});
}
}
}
}(i);
});define("pages/version4video.js",[],function(){
"use strict";
function i(){
return document.domain="qq.com",top.window.__appmsgCgiData.can_use_page&&(t.is_ios||t.is_android)?!0:!1;
}
function s(){
return window.__appmsgCgiData.can_use_page?!0:!1;
}
function n(){
return e.music_support;
}
var e={
music_support:"addEventListener"in window
},t={};
return function(i){
t.is_ios=/(iPhone|iPad|iPod|iOS)/i.test(i),t.is_android=/(Android)/i.test(i),t.is_android_phone=!!t.is_android&&/Mobile/i.test(i),
t.is_android_tablet=!!t.is_android&&!/Mobile/i.test(i),t.ipad=/iPad/i.test(i),t.iphone=!t.ipad&&/iPhone\sOS/i.test(i),
t.is_wp=/Windows\sPhone/i.test(i),t.is_pc=!t.is_wp&&/Windows|Mac\sOS/i.test(i),t.inWechat=/MicroMessenger/.test(i);
}(window.navigator.userAgent),{
device:t,
isShowMpVideo:i,
isShowMpMusic:s,
isSupportMusic:n
};
});define("biz_common/tmpl.js",[],function(){
"use strict";
var n=function(n,t){
var r=new Function("obj","var p=[],print=function(){p.push.apply(p,arguments);};with(obj){p.push('"+n.replace(/[\r\t\n]/g," ").split("<#").join("	").replace(/((^|#>)[^\t]*)'/g,"$1\r").replace(/\t=(.*?)#>/g,"',$1,'").split("	").join("');").split("#>").join("p.push('").split("\r").join("\\'")+"');}return p.join('');");
return r(t);
},t=function(t,r){
return n(document.getElementById(t).innerHTML,r);
};
return{
render:t,
tmpl:n
};
});define("biz_common/ui/imgonepx.js",[],function(){
"use strict";
return"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkJDQzA1MTVGNkE2MjExRTRBRjEzODVCM0Q0NEVFMjFBIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkJDQzA1MTYwNkE2MjExRTRBRjEzODVCM0Q0NEVFMjFBIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QkNDMDUxNUQ2QTYyMTFFNEFGMTM4NUIzRDQ0RUUyMUEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QkNDMDUxNUU2QTYyMTFFNEFGMTM4NUIzRDQ0RUUyMUEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6p+a6fAAAAD0lEQVR42mJ89/Y1QIABAAWXAsgVS/hWAAAAAElFTkSuQmCC";
});
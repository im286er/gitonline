define("biz_wap/ui/lazyload_img.js",["biz_wap/utils/mmversion.js","biz_common/dom/event.js","biz_common/dom/attr.js","biz_common/ui/imgonepx.js"],function(t){
"use strict";
function i(){
var t=this.images;
if(!t||t.length<=0)return!1;
var i=window.pageYOffset||document.documentElement.scrollTop,e=window.innerHeight||document.documentElement.clientHeight,o=e+40,n=this.offset||20,a=0;
if("wifi"==window.networkType){
var s={
bottom:1,
top:1
};
this.lazyloadHeightWhenWifi&&(s=this.lazyloadHeightWhenWifi()),n=Math.max(s.bottom*e,n),
a=Math.max(s.top*e,a);
}
for(var l=+new Date,d=[],c=this.sw,u=0,w=t.length;w>u;u++){
var p=t[u],f=p.el.offsetTop;
if(!p.show&&(i>=f&&i<=f+p.height+a||f>i&&i+o+n>f)){
var g=p.src,v=this;
this.inImgRead&&(i>=f&&i<=f+p.height||f>i&&i+o>f)&&this.inImgRead(g,networkType),
this.changeSrc&&(g=this.changeSrc(p.el,g)),p.el.onerror=function(){
!!v.onerror&&v.onerror(g);
},p.el.onload=function(){
var t=this;
m(t,"height","auto","important"),t.getAttribute("_width")?m(t,"width",t.getAttribute("_width"),"important"):m(t,"width","auto","important");
},h(p.el,"src",g),d.push(g),p.show=!0,m(p.el,"visibility","visible","important");
}
r.isWp&&1*p.el.width>c&&(p.el.width=c);
}
d.length>0&&this.detect&&this.detect({
time:l,
loadList:d,
scrollTop:i
});
}
function e(){
var t=document.getElementsByTagName("img"),e=[],o=this.container,n=this.attrKey||"data-src",r=o.offsetWidth,a=0;
o.currentStyle?a=o.currentStyle.width:"undefined"!=typeof getComputedStyle&&(a=getComputedStyle(o).width),
this.sw=1*a.replace("px","");
for(var s=0,d=t.length;d>s;s++){
var c=t.item(s),u=h(c,n);
if(u){
var w=100;
if(c.dataset&&c.dataset.ratio){
var p=1*c.dataset.ratio,f=1*c.dataset.w||r;
"number"==typeof p&&p>0?(f=r>=f?f:r,w=f*p,c.style.width&&c.setAttribute("_width",c.style.width),
m(c,"width",f+"px","important"),m(c,"visibility","visible","important"),c.setAttribute("src",l)):m(c,"visibility","hidden","important");
}else m(c,"visibility","hidden","important");
m(c,"height",w+"px","important"),e.push({
el:c,
src:u,
height:w,
show:!1
});
}
}
this.images=e,i.call(this);
}
function o(t){
var e=this,o=e.timer;
clearTimeout(o),e.timer=setTimeout(function(){
i.call(e,t);
},300);
}
function n(t){
a.on(window,"scroll",function(i){
o.call(t,i);
}),a.on(window,"load",function(i){
e.call(t,i);
}),a.on(document,"touchmove",function(i){
o.call(t,i);
});
}
var r=t("biz_wap/utils/mmversion.js"),a=t("biz_common/dom/event.js"),s=t("biz_common/dom/attr.js"),h=s.attr,m=s.setProperty,l=t("biz_common/ui/imgonepx.js");
return n;
});define("biz_common/log/jserr.js",[],function(){
function e(e,n){
return e?(r.replaceStr&&(e=e.replace(r.replaceStr,"")),n&&(e=e.substr(0,n)),encodeURIComponent(e.replace("\n",","))):"";
}
var r={};
return window.onerror=function(n,o,t,c,i){
return"Script error."==n||o?"undefined"==typeof r.key||"undefined"==typeof r.reporturl?!0:(setTimeout(function(){
c=c||window.event&&window.event.errorCharacter||0;
var l=[];
if(l.push("msg:"+e(n,100)),o&&(o=o.replace(/[^\,]*\/js\//g,"")),l.push("url:"+e(o,200)),
l.push("line:"+t),l.push("col:"+c),i&&i.stack)l.push("info:"+e(i.stack.toString(),200));else if(arguments.callee){
for(var s=[],u=arguments.callee.caller,a=3;u&&--a>0&&(s.push(u.toString()),u!==u.caller);)u=u.caller;
s=s.join(","),l.push("info:"+e(s,200));
}
var p=new Image;
if(p.src=(r.reporturl+"&key="+r.key+"&content="+l.join("||")).substr(0,1024),window.console&&window.console.log){
var f=l.join("\n");
try{
f=decodeURIComponent(f);
}catch(d){}
console.log(f);
}
},0),!0):!0;
},function(e){
r=e;
};
});define("appmsg/share.js",["biz_common/utils/string/html.js","appmsg/cdn_img_lib.js","biz_common/dom/event.js","biz_common/utils/url/parse.js","biz_wap/utils/mmversion.js","biz_wap/utils/ajax.js","biz_wap/jsapi/core.js"],function(i){
"use strict";
function e(i,e){
var n="";
""!=tid&&(n="tid="+tid+"&aid=54");
var t=i.split("?")[1]||"";
if(t=t.split("#")[0],""!=t){
var o=[t,"scene="+e];
return""!=n&&o.push(n),t=o.join("&"),i.split("?")[0]+"?"+t+"#"+(i.split("#")[1]||"");
}
}
function n(i,e,n){
var t=i.split("?").pop();
if(t=t.split("#").shift(),""!=t){
var o=[t,"action_type="+n,"uin="+e,"vid="+("undefined"!=typeof window.reportVid?window.reportVid.join(";"):""),"mid="+("undefined"!=typeof window.reportMid?window.reportMid.join(";"):"")].join("&");
m({
url:"/mp/appmsg/show",
type:"POST",
timeout:2e3,
data:o
});
}
}
function t(i,e){
return i.isCDN()&&(i=o.addParam(i,"wxfrom",e,!0)),i;
}
i("biz_common/utils/string/html.js"),i("appmsg/cdn_img_lib.js");
var o=(i("biz_common/dom/event.js"),i("biz_common/utils/url/parse.js")),s=i("biz_wap/utils/mmversion.js"),m=i("biz_wap/utils/ajax.js"),a=i("biz_wap/jsapi/core.js");
a.call("hideToolbar");
var r=msg_title.htmlDecode(),l=(msg_source_url.htmlDecode(),""),d=msg_cdn_url,u=msg_link.htmlDecode(),r=msg_title.htmlDecode(),c=msg_desc.htmlDecode();
c=c||u,d.isCDN()&&(d=d.replace(/\/0$/,"/300")),"1"==is_limit_user&&a.call("hideOptionMenu"),
a.on("menu:share:appmessage",function(i){
var o=1,s=t(d,"1");
i&&"favorite"==i.scene&&(o=4,s=t(d,"4")),a.invoke("sendAppMessage",{
appid:l,
img_url:s,
img_width:"640",
img_height:"640",
link:e(u,o),
desc:c,
title:r
},function(){
n(u,fakeid,o);
});
}),a.on("menu:share:timeline",function(){
var i=d;
s.isIOS||(i=t(d,"2")),n(u,fakeid,2),a.invoke("shareTimeline",{
img_url:i,
img_width:"640",
img_height:"640",
link:e(u,2),
desc:c,
title:r
},function(){});
});
a.on("menu:share:weiboApp",function(){
a.invoke("shareWeiboApp",{
img_url:d,
link:e(u,3),
title:r
},function(){
n(u,fakeid,3);
});
}),a.on("menu:share:facebook",function(){
n(u,fakeid,4),a.invoke("shareFB",{
img_url:d,
img_width:"640",
img_height:"640",
link:e(u,4),
desc:c,
title:r
},function(){});
}),a.on("menu:share:QZone",function(){
var i=t(d,"6");
n(u,fakeid,5),a.invoke("shareQZone",{
img_url:i,
img_width:"640",
img_height:"640",
link:e(u,5),
desc:c,
title:r
},function(){});
}),a.on("menu:share:qq",function(){
var i=t(d,"7");
n(u,fakeid,5),a.invoke("shareQQ",{
img_url:i,
img_width:"640",
img_height:"640",
link:e(u,5),
desc:c,
title:r
},function(){});
}),a.on("menu:share:email",function(){
n(u,fakeid,5),a.invoke("sendEmail",{
content:e(u,5),
title:r
},function(){});
});
});define("biz_wap/utils/mmversion.js",[],function(){
"use strict";
function n(){
var n=/MicroMessenger\/([\d\.]+)/i,t=s.match(n);
return t&&t[1]?t[1]:!1;
}
function t(t,r,i){
var e=n();
if(e){
e=e.split("."),t=t.split("."),e.pop();
for(var o,s,u=f["cp"+r],c=0,a=Math.max(e.length,t.length);a>c;++c){
o=e[c]||0,s=t[c]||0,o=parseInt(o)||0,s=parseInt(s)||0;
var p=f.cp0(o,s);
if(!p)return u(o,s);
}
return i||0==r?!0:!1;
}
}
function r(n){
return t(n,0);
}
function i(n,r){
return t(n,1,r);
}
function e(n,r){
return t(n,-1,r);
}
function o(){
return u?"ios":a?"android":"unknown";
}
var s=navigator.userAgent,u=/(iPhone|iPad|iPod|iOS)/i.test(s),c=/Windows\sPhone/i.test(s),a=/(Android)/i.test(s),f={
"cp-1":function(n,t){
return t>n;
},
cp0:function(n,t){
return n==t;
},
cp1:function(n,t){
return n>t;
}
};
return{
get:n,
cpVersion:t,
eqVersion:r,
gtVersion:i,
ltVersion:e,
getPlatform:o,
isWp:c,
isIOS:u,
isAndroid:a
};
});define("appmsg/cdn_img_lib.js",[],function(){
"use strict";
String.prototype.http2https=function(){
return this.replace(/http:\/\/mmbiz\.qpic\.cn\//g,"https://mmbiz.qlogo.cn/");
},String.prototype.https2http=function(){
return this.replace(/https:\/\/mmbiz\.qlogo\.cn\//g,"http://mmbiz.qpic.cn/");
},String.prototype.isCDN=function(){
return 0==this.indexOf("http://mmbiz.qpic.cn/")||0==this.indexOf("https://mmbiz.qlogo.cn/");
};
});define("biz_common/utils/url/parse.js",[],function(){
"use strict";
function r(r){
var n=r.length,e=r.indexOf("?"),t=r.indexOf("#");
t=-1==t?n:t,e=-1==e?t:e;
var s=r.substr(0,e),a=r.substr(e+1,t-e-1),i=r.substr(t+1);
return{
host:s,
query_str:a,
hash:i
};
}
function n(n,e){
var t=r(n),s=t.query_str,a=[];
for(var i in e)e.hasOwnProperty(i)&&a.push(i+"="+encodeURIComponent(e[i]));
return a.length>0&&(s+=(""!=s?"&":"")+a.join("&")),t.host+(""!=s?"?"+s:"")+(""!=t.hash?"#"+t.hash:"");
}
function e(r,n,e,t){
r=r||location.href,-1!=r.indexOf("&")&&-1==r.indexOf("?")&&(r=r.replace("&","?"));
var s=new RegExp("([\\?&]"+n+"=)[^&#]*");
return r.match(s)?t===!0?r.replace(s,"$1"+e):r:-1==r.indexOf("?")?r+"?"+n+"="+e:r+"&"+n+"="+e;
}
return{
parseUrl:r,
join:n,
addParam:e
};
});define("appmsg/index.js",["biz_common/utils/url/parse.js","appmsg/cdn_img_lib.js","biz_wap/utils/mmversion.js","appmsg/share.js","biz_common/log/jserr.js","biz_wap/ui/lazyload_img.js","appmsg/async.js","appmsg/copyright_report.js","biz_common/dom/event.js","biz_wap/jsapi/core.js","appmsg/outer_link.js","appmsg/review_image.js","appmsg/iframe.js","appmsg/qqmusic.js","appmsg/cdn_speed_report.js","appmsg/page_pos.js","appmsg/report_and_source.js","biz_common/dom/class.js","appmsg/report.js"],function(e){
"use strict";
function t(e,t){
var o={
lossy:"UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA",
lossless:"UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==",
alpha:"UklGRkoAAABXRUJQVlA4WAoAAAAQAAAAAAAAAAAAQUxQSAwAAAARBxAR/Q9ERP8DAABWUDggGAAAABQBAJ0BKgEAAQAAAP4AAA3AAP7mtQAAAA==",
animation:"UklGRlIAAABXRUJQVlA4WAoAAAASAAAAAAAAAAAAQU5JTQYAAAD/////AABBTk1GJgAAAAAAAAAAAAAAAAAAAGQAAABWUDhMDQAAAC8AAAAQBxAREYiI/gcA"
},n=new Image;
n.onload=function(){
var o=n.width>0&&n.height>0;
t(e,o);
},n.onerror=function(){
t(e,!1);
},n.src="data:image/webp;base64,"+o[e];
}
var o=document.getElementsByTagName("body");
if(!o||!o[0])return!1;
o=o[0];
var n=/^http(s)?:\/\/mp\.weixin\.qq\.com\//g;
try{
if(top!=window&&(!top||top&&top.location.href&&n.test(top.location.href)))throw new Error("in iframe");
}catch(i){
var r="",s=new Image;
s.src=("http://mp.weixin.qq.com/mp/jsreport?key=4&content=biz:"+biz+",mid:"+mid+",uin:"+uin+"[key4]"+r+"&r="+Math.random()).substr(0,1024);
}
/MicroMessenger/.test(navigator.userAgent)&&/#rd$/.test(location.href)&&location.replace(location.href.replace(/#rd$/,"#wechat_redirect"));
var a=e("biz_common/utils/url/parse.js");
e("appmsg/cdn_img_lib.js"),window.page_endtime=+new Date;
var p=e("biz_wap/utils/mmversion.js"),m=!p.isWp&&-1==navigator.userAgent.indexOf("MicroMessenger");
if(e("appmsg/share.js"),window.logs={},"mp.weixin.qq.com"==location.host){
var c=e("biz_common/log/jserr.js");
c({
key:0,
reporturl:"http://mp.weixin.qq.com/mp/jsreport?1=1",
replaceStr:/http(s)?:(.*?)js\//g
});
}
window.logs.webplog={
lossy:0,
lossless:0,
alpha:0,
animation:0,
total:0
};
var l=-1!=navigator.userAgent.indexOf("TBS/"),d=function(e,o){
t(e,function(e,t){
if(window.logs.webplog[e]=t?1:0,window.logs.webplog.total++,4==window.logs.webplog.total){
var n=window.logs.webplog,i=Math.random();
l&&1>=i&&(n.lossy=n.lossless=n.alpha=1,window.logs.webplog=n);
var r=n.lossy&n.lossless&n.alpha;
o(!!r);
}
});
},A=function(e){
d("lossy",e),d("lossless",e),d("alpha",e),d("animation",e);
};
window.webp=!1,A(function(t){
window.webp=t,t&&window.localStorage&&window.localStorage.setItem&&window.localStorage.setItem("webp","1"),
window.logs.img={
download:{},
read:{},
load:{}
};
var o=document.getElementById("js_cover");
if(o){
var n=o.getAttribute("data-src");
if(n){
if(n.isCDN()){
var i=new Date;
for(i.setFullYear(2014,9,1);-1!=n.indexOf("?tp=webp");)n=n.replace("?tp=webp","");
for(;-1!=n.indexOf("&tp=webp");)n=n.replace("&tp=webp","");
1e3*ct>=i.getTime()&&""!=img_format&&"gif"!=img_format&&(n=n.replace(/\/0$/,"/640"),
n=n.replace(/\/0\?/,"/640?"),o.dataset&&(o.dataset.s="300,640")),t&&(n=a.addParam(n,"tp","webp",!0)),
n=a.addParam(n,"wxfrom","5",!0),is_https_res&&(n=n.http2https());
}
o.setAttribute("src",n),window.logs.img.read[n]=!0,window.logs.img.load[n]=!0,o.removeAttribute("data-src");
}
}
var r=e("biz_wap/ui/lazyload_img.js");
new r({
attrKey:"data-src",
lazyloadHeightWhenWifi:function(){
var e,t=1,o=1;
e=window.svr_time?new Date(1e3*window.svr_time):new Date;
var n=e.getHours();
return n>=20&&23>n&&(t=.5,o=0),{
bottom:t,
top:o
};
},
inImgRead:function(e){
e&&(window.logs.img.read[e]=!0);
},
changeSrc:function(e,t){
if(!t)return"";
for(var o=t;-1!=o.indexOf("?tp=webp");)o=o.replace("?tp=webp","");
for(;-1!=o.indexOf("&tp=webp");)o=o.replace("&tp=webp","");
t.isCDN()&&(e.dataset&&e.dataset.s&&(o=o.replace(/\/0$/,"/640"),o=o.replace(/\/0\?/,"/640?")),
window.webp&&(o=a.addParam(o,"tp","webp",!0)),o=a.addParam(o,"wxfrom","5",!0),is_https_res&&(o=o.http2https()));
var n=/^http\:\/\/(a|b)(\d)+\.photo\.store\.qq\.com/g;
return o=o.replace(n,"http://m.qpic.cn"),window.logs.img.load[o]=!0,o;
},
onerror:function(e){
if(e&&e.isCDN()){
var t=10;
/tp\=webp/.test(e)&&(t=11);
var o=new Image;
o.src="http://mp.weixin.qq.com/mp/jsreport?key="+t+"&content="+encodeURIComponent(e)+"&r="+Math.random();
}
},
detect:function(e){
if(e&&e.time&&e.loadList){
var t=e.time,o=e.loadList;
window.logs.img.download[t]=o;
}
},
container:document.getElementById("page-content")
});
}),e("appmsg/async.js");
var g=e("appmsg/copyright_report.js"),u=e("biz_common/dom/event.js"),w=e("biz_wap/jsapi/core.js");
!function(){
var e=document.getElementById("post-user"),t=document.getElementById("copyright_info"),o=[];
e&&o.push({
dom:e,
username:user_name_new||user_name
}),t&&source_username&&o.push({
dom:t,
username:source_username
});
for(var n=0,i=o.length;i>n;n++)!function(e){
u.on(e.dom,"click",function(){
return w.invoke("profile",{
username:e.username,
scene:"57"
}),"copyright_info"==e.dom.id&&source_username&&g.card_click_report({
scene:"0"
}),!1;
}),p.isWp&&e.dom.setAttribute("href","weixin://profile/"+e.username);
}(o[n]);
}(),function(){
location.href.match(/fontScale=\d+/)&&p.isIOS&&w.on("menu:setfont",function(e){
e.fontScale<=0&&(e.fontScale=100),document.getElementsByTagName("html").item(0).style.webkitTextSizeAdjust=e.fontScale+"%",
document.getElementsByTagName("html").item(0).style.lineHeight=160/e.fontScale;
});
}();
var f=e("appmsg/outer_link.js");
if(new f({
container:document.getElementById("js_content"),
changeHref:function(e,t){
if(e&&0==e.indexOf("http://mp.weixin.qq.com/s"))e=e.replace(/#rd\s*$/,"#wechat_redirect");else if(0!=e.indexOf("http://mp.weixin.qq.com/mp/redirect"))return"http://"+location.host+"/mp/redirect?url="+encodeURIComponent(e)+"&action=appmsg_redirect&uin="+uin+"&biz="+biz+"&mid="+mid+"&idx="+idx+"&type="+t+"&scene=0";
return e;
}
}),!m){
var _=e("appmsg/review_image.js");
new _({
container:document.getElementById("js_content"),
is_https_res:is_https_res
});
}
e("appmsg/iframe.js"),e("appmsg/qqmusic.js"),e("appmsg/cdn_speed_report.js"),e("appmsg/page_pos.js"),
setTimeout(function(){
u.tap(document.getElementById("copyright_logo"),function(){
location.href="http://kf.qq.com/touch/sappfaq/150211YfyMVj150326iquI3e.html";
}),e("appmsg/report_and_source.js"),function(){
if(m){
var t=e("biz_common/dom/class.js");
t.addClass(o,"not_in_mm");
var n=document.createElement("link");
n.rel="stylesheet",n.type="text/css",n.async=!0,n.href=not_in_mm_css;
var i=document.getElementsByTagName("head")[0];
i.appendChild(n);
var r=document.getElementById("js_pc_qr_code_img");
if(r){
var s=10000004,a=document.referrer;
0==a.indexOf("http://weixin.sogou.com")?s=10000001:0==a.indexOf("https://wx.qq.com")&&(s=10000003),
r.setAttribute("src","/mp/qrcode?scene="+s+"&size=102&__biz="+biz),document.getElementById("js_pc_qr_code").style.display="block";
var p=new Image;
p.src="/mp/report?action=pcclick&__biz="+biz+"&uin="+uin+"&scene="+s+"&r="+Math.random();
}
var c=document.getElementById("js_profile_qrcode"),l=document.getElementById("js_profile_arrow_wrp"),d=document.getElementById("post-user");
if(c&&d&&l){
var A=function(){
var e=10000005,t=document.referrer;
0==t.indexOf("http://weixin.sogou.com")?e=10000006:0==t.indexOf("https://wx.qq.com")&&(e=10000007);
var o=document.getElementById("js_profile_qrcode_img");
o&&o.setAttribute("src","/mp/qrcode?scene="+e+"&size=102&__biz="+biz),c.style.display="block";
var n=new Image;
return n.src="/mp/report?action=pcclick&__biz="+biz+"&uin="+uin+"&scene="+e+"&r="+Math.random(),
l.style.left=d.offsetLeft-c.offsetLeft+d.offsetWidth/2-8+"px",!1;
};
u.on(d,"click",A),u.on(c,"click",A),u.on(document,"click",function(e){
var t=e.target||e.srcElement;
t!=d&&t!=c&&(c.style.display="none");
});
}
}else{
var g=document.getElementById("js_report_article");
!!g&&(g.style.display="");
}
}(),function(){
var e=location.href.indexOf("scrolltodown")>-1?!0:!1,t=document.getElementById("img-content");
if(e&&t&&t.getBoundingClientRect){
var o=t.getBoundingClientRect().height;
window.scrollTo(0,o);
}
}(),e("appmsg/report.js");
for(var t=document.getElementsByTagName("map"),n=0,i=t.length;i>n;++n)t[n].parentNode.removeChild(t[n]);
g.card_pv_report();
},1e3);
});
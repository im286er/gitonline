define("biz_common/dom/attr.js",[],function(){
"use strict";
function t(t,e,n){
return"undefined"==typeof n?t.getAttribute(e):t.setAttribute(e,n);
}
function e(t,e,n,r){
t.style.setProperty?(r=r||null,t.style.setProperty(e,n,r)):"undefined"!=typeof t.style.cssText&&(r=r?"!"+r:"",
t.style.cssText+=";"+e+":"+n+r+";");
}
return{
attr:t,
setProperty:e
};
});define("biz_wap/utils/ajax.js",["biz_common/utils/url/parse.js"],function(e){
"use strict";
function t(e){
var t={};
return"undefined"!=typeof uin&&(t.uin=uin),"undefined"!=typeof key&&(t.key=key),
"undefined"!=typeof pass_ticket&&(t.pass_ticket=pass_ticket),o.join(e,t);
}
function n(e){
var n=(e.type||"GET").toUpperCase(),o=t(e.url),r="undefined"==typeof e.async?!0:e.async,s=new XMLHttpRequest,a=null,u=null;
if("object"==typeof e.data){
var i=e.data;
u=[];
for(var c in i)i.hasOwnProperty(c)&&u.push(c+"="+encodeURIComponent(i[c]));
u=u.join("&");
}else u="string"==typeof e.data?e.data:null;
s.open(n,o,r),s.onreadystatechange=function(){
3==s.readyState&&e.received&&e.received(s),4==s.readyState&&(s.onreadystatechange=null,
s.status>=200&&s.status<400?e.success&&e.success(s.responseText):e.error&&e.error(s),
clearTimeout(a),e.complete&&e.complete(),e.complete=null);
},"POST"==n&&s.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8"),
s.setRequestHeader("X-Requested-With","XMLHttpRequest"),"undefined"!=typeof e.timeout&&(a=setTimeout(function(){
s.abort("timeout"),e.complete&&e.complete(),e.complete=null;
},e.timeout));
try{
s.send(u);
}catch(p){
e.error&&e.error();
}
}
var o=e("biz_common/utils/url/parse.js");
return n;
});define("biz_common/utils/string/html.js",[],function(){
"use strict";
return String.prototype.html=function(t){
var e=["&#39;","'","&quot;",'"',"&nbsp;"," ","&gt;",">","&lt;","<","&amp;","&","&yen;","¥"];
t&&e.reverse();
for(var n=0,r=this;n<e.length;n+=2)r=r.replace(new RegExp(e[n],"g"),e[n+1]);
return r;
},String.prototype.htmlEncode=function(){
return this.html(!0);
},String.prototype.htmlDecode=function(){
return this.html(!1);
},String.prototype.getPureText=function(){
return this.replace(/<\/?[^>]*\/?>/g,"");
},{
htmlDecode:function(t){
return t.htmlDecode();
},
htmlEncode:function(t){
return t.htmlEncode();
},
getPureText:function(t){
return t.getPureText();
}
};
});define("appmsg/report.js",["biz_common/dom/event.js","appmsg/cdn_img_lib.js","biz_wap/utils/mmversion.js","biz_common/utils/report.js"],function(e){
"use strict";
function t(){
var t=e("biz_wap/utils/mmversion.js"),o=e("biz_common/utils/report.js"),r=!1,a=window.performance||window.msPerformance||window.webkitPerformance;
return function(){
if(Math.random()<.1){
var e=window.webp?2e3:1e3,n=[];
n.push("1="+e),t.isIOS&&n.push("2="+e),t.isAndroid&&n.push("3="+e);
var i=window.logs.pageinfo.content_length;
if(i&&n.push("4="+i),e=a?2e3:1e3,n.push("5="+e),t.isIOS&&n.push("6="+e),t.isAndroid&&n.push("7="+e),
a){
if(a.memory){
var r=a.memory;
!!r.jsHeapSizeLimit&&n.push("8="+r.jsHeapSizeLimit/1e3),!!r.totalJSHeapSize&&n.push("9="+r.totalJSHeapSize/1e3),
!!r.usedJSHeapSize&&n.push("10="+r.usedJSHeapSize/1e3);
}
if(a.timing){
var s=a.timing,p=s.navigationStart,d=s.responseEnd,g=d-p,m=s.connectEnd==s.fetchStart;
n.push("11="+(m?2e3:1e3)),n.push("12="+g),"wifi"==networkType?n.push("13="+g):"2g/3g"==networkType&&n.push("14="+g);
}
}
o("http://isdspeed.qq.com/cgi-bin/r.cgi?flag1=7839&flag2=7&flag3=8&"+n.join("&"));
}
}(),a&&a.timing?(r=a.timing.navigationStart,function(){
if(!(Math.random()>.5)&&a.getEntries){
for(var e=[],t=a.getEntries(),n=[],i=0,r=t.length;r>i;++i){
var s=t[i],p=s.name;
if(p&&"script"==s.initiatorType&&/^.*(res\.wx\.qq\.com)(.*)\.js$/g.test(p)){
{
var d=s.duration;
s.startTime,s.responseEnd;
}
-1!=p.indexOf("/js/biz_wap/moon")?(d=Math.round(d),e.push("1="+d),"wifi"==networkType?e.push("2="+d):"2g/3g"==networkType&&e.push("3="+d),
e.push("4="+(10>=d?2e3:1e3))):n.push({
s:s.startTime,
e:s.responseEnd,
t:s.duration
});
}else;
}
if(n=n.sort(function(e){
return e.s<e.s?-1:1;
}),n&&n.length>0){
for(var g=0,m=0,u=0,i=0,f=n.length;f>i;++i){
var s=n[i],h=m-s.s;
h>0&&(s.t-=h),h>0&&s.e>m&&(u+=h),g=s.s,m=s.e;
}
u=Math.round(u),e.push("5="+u),"wifi"==networkType?e.push("6="+u):"2g/3g"==networkType&&e.push("7="+u);
}
if("undefined"!=typeof moon){
var c=moon.hit_num,w=moon.mod_num;
e.push("8="+Math.round(1e3+1e3*(c/w)));
}
o("http://isdspeed.qq.com/cgi-bin/r.cgi?flag1=7839&flag2=7&flag3=11&"+e.join("&"));
}
}(),function(){
function e(){
if(-1==i.indexOf("NetType/"))return!1;
for(var e=["2G","cmwap","cmnet","uninet","uniwap","ctwap","ctnet"],t=0,n=e.length;n>t;++t)if(-1!=i.indexOf(e[t]))return!0;
return!1;
}
var t=write_sceen_time-r,n=first_sceen__time-r,a=page_endtime-r;
if(window.logs.pagetime={
wtime:t,
ftime:n,
ptime:a
},!(Math.random()>.5)){
var s=["navigationStart","unloadEventStart","unloadEventEnd","redirectStart","redirectEnd","fetchStart","domainLookupStart","domainLookupEnd","connectStart","connectEnd","requestStart","responseStart","responseEnd","domLoading","domInteractive","domContentLoadedEventStart","domContentLoadedEventEnd","domComplete","loadEventStart","loadEventEnd","secureConnectionStart"],p=[],d=[];
p.push("flag1=7839&flag2=7&flag3=9"),d.push(e()?"flag1=7839&flag2=7&flag3=12":"wifi"==networkType?"flag1=7839&flag2=7&flag3=5":"2g/3g"==networkType?"flag1=7839&flag2=7&flag3=6":"flag1=7839&flag2=7&flag3=7");
for(var g=0,m=s.length;m>g;++g){
s[g]=window.performance.timing[s[g]];
var u=s[g]-s[0];
u>0&&(p.push(g+"="+u),d.push(g+"="+u));
}
-1!=i.indexOf("MicroMessenger")?(p.push("21="+t+"&22="+n+"&23="+a),d.push("21="+t+"&22="+n+"&23="+a)):(p.push("24="+t+"&25="+n+"&26="+a),
d.push("24="+t+"&25="+n+"&26="+a)),p.push("27="+t+"&28="+n+"&29="+a),d.push("27="+t+"&28="+n+"&29="+a),
o("http://isdspeed.qq.com/cgi-bin/r.cgi?"+p.join("&")),o("http://isdspeed.qq.com/cgi-bin/r.cgi?"+d.join("&"));
}
}(),void function(){
var e=document.getElementById("js_toobar"),t=document.getElementById("page-content"),i=window.innerHeight||document.documentElement.clientHeight;
if(t&&!(Math.random()>.1)){
var r=function(){
var s=window.pageYOffset||document.documentElement.scrollTop,p=e.offsetTop;
if(s+i>=p){
for(var d,g,m=t.getElementsByTagName("img"),u={},f=[],h=0,c=0,w=0,l=0,v=m.length;v>l;++l){
var E=m[l];
d=E.getAttribute("data-src")||E.getAttribute("src"),g=E.getAttribute("src"),d&&(d.isCDN()?c++:w++,
h++,u[g]={});
}
if(f.push("1="+1e3*h),f.push("2="+1e3*c),f.push("3="+1e3*w),a.getEntries){
var y=a.getEntries(),S=window.logs.img.download,T=[0,0,0],_=[0,0,0];
h=c=0;
for(var l=0,k=y.length;k>l;++l){
var b=y[l],j=b.name;
j&&"img"==b.initiatorType&&u[j]&&(j.isCDN()&&(_[0]+=b.duration,c++),T[0]+=b.duration,
h++,u[j]={
startTime:b.startTime,
responseEnd:b.responseEnd
});
}
T[0]>0&&h>0&&(T[2]=T[0]/h),_[0]>0&&c>0&&(_[2]=_[0]/c);
for(var l in S)if(S.hasOwnProperty(l)){
for(var M=S[l],q=0,z=0,O=0,x=0,H=0,v=M.length;v>H;++H){
var d=M[H];
if(u[d]&&u[d].startTime&&u[d].responseEnd){
var A=u[d].startTime,C=u[d].responseEnd;
q=Math.max(q,C),z=z?Math.min(z,A):A,d.isCDN()&&(O=Math.max(q,C),x=z?Math.min(z,A):A);
}
}
T[1]+=Math.round(q-z),_[1]+=Math.round(O-x);
}
for(var L=4,I=7,l=0;3>l;l++)T[l]=Math.round(T[l]),_[l]=Math.round(_[l]),T[l]>0&&(f.push(L+l+"="+T[l]),
"wifi"==networkType?f.push(L+l+6+"="+T[l]):"2g/3g"==networkType&&f.push(L+l+12+"="+T[l])),
_[l]>0&&(f.push(I+l+"="+_[l]),"wifi"==networkType?f.push(I+l+6+"="+_[l]):"2g/3g"==networkType&&f.push(I+l+12+"="+_[l]));
}
o("http://isdspeed.qq.com/cgi-bin/r.cgi?flag1=7839&flag2=7&flag3=10&"+f.join("&")),
n.off(window,"scroll",r,!1);
}
};
n.on(window,"scroll",r,!1);
}
}()):!1;
}
var n=e("biz_common/dom/event.js"),i=navigator.userAgent;
e("appmsg/cdn_img_lib.js"),n.on(window,"load",function(){
if(""==networkType&&-1!=i.indexOf("MicroMessenger")){
var e={
"network_type:fail":"fail",
"network_type:edge":"2g/3g",
"network_type:wwan":"2g/3g",
"network_type:wifi":"wifi"
};
JSAPI.invoke("getNetworkType",{},function(n){
networkType=e[n.err_msg],t();
});
}else t();
},!1);
});define("biz_common/dom/class.js",[],function(){
"use strict";
function s(s,a){
return s.classList?s.classList.contains(a):s.className.match(new RegExp("(\\s|^)"+a+"(\\s|$)"));
}
function a(s,a){
s.classList?s.classList.add(a):this.hasClass(s,a)||(s.className+=" "+a);
}
function e(a,e){
if(a.classList)a.classList.remove(e);else if(s(a,e)){
var c=new RegExp("(\\s|^)"+e+"(\\s|$)");
a.className=a.className.replace(c," ");
}
}
function c(c,l){
s(c,l)?e(c,l):a(c,l);
}
return{
hasClass:s,
addClass:a,
removeClass:e,
toggleClass:c
};
});define("appmsg/report_and_source.js",["biz_common/utils/string/html.js","biz_common/dom/event.js","biz_wap/utils/ajax.js","biz_wap/jsapi/core.js"],function(require,exports,module){
"use strict";
function viewSource(){
var redirectUrl=sourceurl.indexOf("://")<0?"http://"+sourceurl:sourceurl;
redirectUrl="http://"+location.host+"/mp/redirect?url="+encodeURIComponent(sourceurl);
var opt={
url:"/mp/advertisement_report"+location.search+"&report_type=3&action_type=0&url="+encodeURIComponent(sourceurl)+"&__biz="+biz+"&r="+Math.random(),
type:"GET",
async:!1
};
return tid?opt.success=function(res){
try{
res=eval("("+res+")");
}catch(e){
res={};
}
res&&0==res.ret?location.href=redirectUrl:viewSource();
}:(opt.timeout=2e3,opt.complete=function(){
location.href=redirectUrl;
}),ajax(opt),!1;
}
require("biz_common/utils/string/html.js");
var DomEvent=require("biz_common/dom/event.js"),ajax=require("biz_wap/utils/ajax.js"),title=msg_title.htmlDecode(),sourceurl=msg_source_url.htmlDecode(),js_report_article=document.getElementById("js_report_article"),JSAPI=require("biz_wap/jsapi/core.js");
DomEvent.tap(js_report_article,function(){
var e=["/mp/infringement?url=",encodeURIComponent(location.href),"&title=",encodeURIComponent(title),"&__biz=",biz].join("");
return location.href=e+"#wechat_redirect",!1;
});
var js_view_source=document.getElementById("js_view_source");
DomEvent.on(js_view_source,"click",function(){
return viewSource(),!1;
});
});define("appmsg/page_pos.js",["biz_common/utils/string/html.js","biz_common/dom/event.js","biz_wap/utils/ajax.js","biz_common/utils/cookie.js","appmsg/cdn_img_lib.js"],function(e){
"use strict";
function t(e){
for(var t=5381,n=0;n<e.length;n++)t=(t<<5)+t+e.charCodeAt(n),t&=2147483647;
return t;
}
function n(e,t){
if(e&&!(e.length<=0))for(var n,o,i,a=/http(s)?\:\/\/([^\/]*)(\?|\/)?/,m=0,l=e.length;l>m;++m)n=e[m],
n&&(o=n.getAttribute(t),o&&(i=o.match(a),i&&i[2]&&(c[i[2]]=!0)));
}
function o(e){
for(var t=0,n=w.length;n>t;++t)if(w[t]==e)return!0;
return!1;
}
function i(){
c={},n(document.getElementsByTagName("a"),"href"),n(document.getElementsByTagName("link"),"href"),
n(document.getElementsByTagName("iframe"),"src"),n(document.getElementsByTagName("script"),"src"),
n(document.getElementsByTagName("img"),"src");
var e=[];
for(var t in c)c.hasOwnProperty(t)&&(window.networkType&&"wifi"==window.networkType&&!g&&o(t)&&(g=!0),
e.push(t));
return c={},e.join(",");
}
function a(){
var e,t=window.pageYOffset||document.documentElement.scrollTop,n=document.getElementById("js_content"),o=document.documentElement.clientHeight||window.innerHeight,a=document.body.scrollHeight,m=Math.ceil(a/o),l=(window.logs.read_height||t)+o,c=document.getElementById("js_toobar").offsetTop,w=n.getElementsByTagName("img")||[],_=Math.ceil(l/o)||1,f=document.getElementById("media"),u=50,h=0,p=0,v=0,y=0,b=l+u>c?1:0;
_>m&&(_=m);
var T=function(t){
if(t)for(var n=0,o=t.length;o>n;++n){
var i=t[n];
if(i){
h++;
var a=i.getAttribute("src"),m=i.getAttribute("data-type");
a&&0==a.indexOf("http")&&(p++,a.isCDN()&&(v++,-1!=a.indexOf("?tp=webp")&&y++),m&&(e["img_"+m+"_cnt"]=e["img_"+m+"_cnt"]||0,
e["img_"+m+"_cnt"]++));
}
}
e.download_cdn_webp_img_cnt=y||0,e.download_img_cnt=p||0,e.download_cdn_img_cnt=v||0;
},O=window.appmsgstat||{},j=window.logs.img||{},x=window.logs.pagetime||{},E=j.load||{},z=j.read||{},B=[],N=[],S=0,k=0,D=0;
for(var I in z)I&&0==I.indexOf("http")&&z.hasOwnProperty(I)&&N.push(I);
for(var I in E)I&&0==I.indexOf("http")&&E.hasOwnProperty(I)&&B.push(I);
for(var M=0,Y=B.length;Y>M;++M){
var P=B[M];
P&&P.isCDN()&&(-1!=P.indexOf("/0")&&S++,-1!=P.indexOf("/640")&&k++,-1!=P.indexOf("/300")&&D++);
}
var e={
__biz:biz,
title:msg_title.htmlDecode(),
mid:mid,
idx:idx,
read_cnt:O.read_num||0,
like_cnt:O.like_num||0,
screen_height:o,
screen_num:m,
video_cnt:window.logs.video_cnt||0,
img_cnt:h||0,
read_screen_num:_||0,
is_finished_read:b,
scene:source,
content_len:s.content_length||0,
start_time:page_begintime,
end_time:(new Date).getTime(),
img_640_cnt:k,
img_0_cnt:S,
img_300_cnt:D,
wtime:x.wtime||0,
ftime:x.ftime||0,
ptime:x.ptime||0,
reward_heads_total:window.logs.reward_heads_total||0,
reward_heads_fail:window.logs.reward_heads_fail||0
};
if(window.networkType&&"wifi"==window.networkType&&(e.wifi_all_imgs_cnt=B.length,
e.wifi_read_imgs_cnt=N.length),window.logs.webplog&&4==window.logs.webplog.total){
var A=window.logs.webplog;
e.webp_total=1,e.webp_lossy=A.lossy,e.webp_lossless=A.lossless,e.webp_alpha=A.alpha,
e.webp_animation=A.animation;
}
T(!!f&&f.getElementsByTagName("img")),T(w);
var C=(new Date).getDay(),H=i();
(g||0!==user_uin&&Math.floor(user_uin/100)%7==C)&&(e.domain_list=H),g&&(e.html_content=d),
r({
url:"/mp/appmsgreport?action=page_time",
type:"POST",
data:e,
async:!1,
timeout:2e3
});
}
function m(e,t){
try{
localStorage.setItem(e,t);
}catch(n){
for(var o=localStorage.length-1;o>=0;){
var i=localStorage.key(o);
i.match(/^\d+$/)&&localStorage.removeItem(i),o--;
}
}
}
e("biz_common/utils/string/html.js");
{
var l=e("biz_common/dom/event.js"),r=e("biz_wap/utils/ajax.js");
e("biz_common/utils/cookie.js");
}
e("appmsg/cdn_img_lib.js");
var d,s={};
!function(){
if(d=document.getElementsByTagName("html"),d&&1==!!d.length){
d=d[0].innerHTML;
var e=d.replace(/[\x00-\xff]/g,""),t=d.replace(/[^\x00-\xff]/g,"");
s.content_length=1*t.length+3*e.length+"<!DOCTYPE html><html></html>".length;
}
window.logs.pageinfo=s;
}();
var c={},g=!1,w=["wap.zjtoolbar.10086.cn","125.88.113.247","115.239.136.61","134.224.117.240","hm.baidu.com","c.cnzz.com","w.cnzz.com","124.232.136.164","img.100msh.net","10.233.12.76","wifi.witown.com","211.137.132.89"],_=null,f=0,u=msg_link.split("?").pop(),h=t(u);
window.localStorage&&(l.on(window,"load",function(){
f=1*localStorage.getItem(h);
var e=location.href.indexOf("scrolltodown")>-1?!0:!1,t=(document.getElementById("img-content"),
document.getElementById("js_cmt_area"));
if(e&&t&&t.offsetTop){
var n=t.offsetTop;
window.scrollTo(0,n-25);
}else window.scrollTo(0,f);
}),l.on(window,"unload",function(){
if(m(n,f),window._adRenderData&&"undefined"!=typeof JSON&&JSON.stringify){
var e=JSON.stringify(window._adRenderData),t=+new Date,n=[biz,sn,mid,idx].join("_");
localStorage.setItem("adinfo_"+n,e),localStorage.setItem("adinfo_time_"+n,t);
}
a();
}),window.logs.read_height=0,l.on(window,"scroll",function(){
var e=window.pageYOffset||document.documentElement.scrollTop;
window.logs.read_height=Math.max(window.logs.read_height,e),clearTimeout(_),_=setTimeout(function(){
f=window.pageYOffset,m(h,f);
},500);
}),l.on(document,"touchmove",function(){
var e=window.pageYOffset||document.documentElement.scrollTop;
window.logs.read_height=Math.max(window.logs.read_height,e),clearTimeout(_),_=setTimeout(function(){
f=window.pageYOffset,m(h,f);
},500);
}));
});define("appmsg/cdn_speed_report.js",["biz_common/dom/event.js","biz_wap/jsapi/core.js","biz_wap/utils/ajax.js"],function(e){
"use strict";
function n(){
function e(e){
var n=[];
for(var i in e)n.push(i+"="+encodeURIComponent(e[i]||""));
return n.join("&");
}
if(networkType){
var n=window.performance||window.msPerformance||window.webkitPerformance;
if(n&&"undefined"!=typeof n.getEntries){
var i,t,a=100,o=document.getElementsByTagName("img"),s=o.length,p=navigator.userAgent,m=!1;
/micromessenger\/(\d+\.\d+)/i.test(p),t=RegExp.$1;
for(var g=0,w=o.length;w>g;g++)if(i=parseInt(100*Math.random()),!(i>a)){
var d=o[g].getAttribute("src");
if(d&&!(d.indexOf("mp.weixin.qq.com")>=0)){
for(var f,c=n.getEntries(),_=0;_<c.length;_++)if(f=c[_],f.name==d){
r({
type:"POST",
url:"/mp/appmsgpicreport?__biz="+biz+"#wechat_redirect",
data:e({
rnd:Math.random(),
uin:uin,
version:version,
client_version:t,
device:navigator.userAgent,
time_stamp:parseInt(+new Date/1e3),
url:d,
img_size:o[g].fileSize||0,
user_agent:navigator.userAgent,
net_type:networkType,
appmsg_id:window.appmsgid||"",
sample:s>100?100:s,
delay_time:parseInt(f.duration)
})
}),m=!0;
break;
}
if(m)break;
}
}
}
}
}
var i=e("biz_common/dom/event.js"),t=e("biz_wap/jsapi/core.js"),r=e("biz_wap/utils/ajax.js"),a={
"network_type:fail":"fail",
"network_type:edge":"2g/3g",
"network_type:wwan":"2g/3g",
"network_type:wifi":"wifi"
};
t.invoke("getNetworkType",{},function(e){
networkType=a[e.err_msg],n();
}),i.on(window,"load",n,!1);
});define("appmsg/qqmusic.js",["biz_common/dom/event.js","biz_common/tmpl.js","biz_wap/utils/qqmusic_player.js","biz_wap/utils/ajax.js","biz_common/dom/class.js","biz_wap/utils/localstorage.js","pages/love_comment.js","pages/report.js","pages/version4video.js"],function(i){
"use strict";
function e(i,e,m,o){
var n=i+"_"+m;
g.musicSupport&&(d.tap(u("qqmusic_play_"+n),function(){
var i=g.musicList[n];
i.player.play({
mid_str:o
});
}),d.tap(u("qqmusic_home_"+n),function(){
var e=g.musicList[n],t=["/mp/music?scene=1&comment_id=",e.comment_id,"#wechat_redirect"].join("");
s(i,m),g.unloadStatus="jump_detail",y.detail_click[m]=1,window.location.href=t;
})),1*window.show_comment===1&&d.tap(u("qqmusic_love_icon_"+n),function(){
t(i,e,m),c(e);
});
}
function t(i,e,t){
var m=e+"_"+t,n=u("qqmusic_main_"+m),s="";
if(n){
_.hasClass(n,"loved")?(s="0","undefined"!=typeof g.loverCount[e]&&(g.loverCount[e]-=1)):(s="1",
"undefined"!=typeof g.loverCount[e]&&(g.loverCount[e]+=1));
for(var c=0;c<g.musicLen;c++){
var a=u("qqmusic_main_"+e+"_"+c);
a&&("0"===s?_.removeClass(a,"loved"):"1"===s&&_.addClass(a,"loved"));
}
o(s,e);
}
}
function m(){
"jump_detail"!=g.unloadStatus&&l.remove(g.cachekey);
var i=g.reportData;
i.musicid=i.musicid.join(";"),i.hasended=i.hasended.join(";"),i.commentid=i.commentid.join(";"),
i.mtitle=i.mtitle.join(";#"),i.detail_click=i.detail_click.join(";"),f.musicreport({
data:i
});
}
function o(i,e){
v.love_request({
like:i,
comment_id:e,
reportType:1,
action:1
});
}
function n(){
var i=l.get(g.cachekey);
if(i){
try{
if(i=JSON.parse(i)||{},!i.time||(new Date).getTime()-g.cacheTime>1*i.time)return;
}catch(e){
return;
}
for(var t in g.musicList)if(i[t]){
var m=g.musicList[t],o=m.player,n=i[t].playTime;
1*n>0&&!i[t].paused&&(o.play({
mid_str:i[t].media_id
}),o.setPlayTime(n));
}
}
}
function s(i,e){
var t={
time:(new Date).getTime()
};
for(var m in g.musicList){
var o=g.musicList[m],n=o.player,s=n.getAudioObj(),c=m.split("_"),a=c[0],u=c[1];
t[m]={
paused:s.paused,
playTime:n.getPlayTime(),
musicid:a,
index:u,
media_id:o.media_id,
cur:a==i&&u==e?"1":"0"
};
}
l.set(g.cachekey,JSON.stringify(t));
}
function c(i){
for(var e=0;e<g.musicLen;e++){
var t=u("love_text_"+i+"_"+e);
t&&(t.innerText="undefined"!=typeof g.loverCount[i]&&1*g.loverCount[i]>0?1*g.loverCount[i]:"赞");
}
}
function a(){
var i=[];
for(var e in g.commentIdObj)i.push(e);
v.getLoveCount({
comment_id:i.join(","),
callback:function(i){
for(var e=i.topic_list,t=0,m=e.length;m>t;t++){
var o=e[t];
g.loverCount[o.topic_id]=o.like_count;
for(var n=0;n<g.musicLen;n++){
var s=u("qqmusic_main_"+o.topic_id+"_"+n);
s&&(1*o.like_status===1?_.addClass(s,"loved"):_.removeClass(s,"loved"));
}
c(o.topic_id);
}
}
});
}
function u(i){
return document.getElementById(i);
}
var d=i("biz_common/dom/event.js"),r=i("biz_common/tmpl.js"),p=i("biz_wap/utils/qqmusic_player.js"),_=(i("biz_wap/utils/ajax.js"),
i("biz_common/dom/class.js")),l=i("biz_wap/utils/localstorage.js"),v=i("pages/love_comment.js"),f=i("pages/report.js"),h=i("pages/version4video.js"),g={
imgroot:"https://imgcache.qq.com/music/photo/mid_album_68",
love_url:"/mp/interestlike?#wechat_redirect",
cachekey:"qqmusicStatus",
cacheTime:6e5,
musicSupport:h.isSupportMusic(),
musicList:{},
commentIdObj:{},
loverCount:{},
musicLen:0,
reportData:{
mid:window.mid,
__biz:window.biz,
idx:window.idx,
musicid:[],
hasended:[],
commentid:[],
scene_type:0,
mtitle:[],
detail_click:[],
app_btn_kv:0,
app_btn_click:0,
app_btn_type:0
}
};
if(window.reportMid=[],h.isShowMpMusic()){
var j=u("js_content");
if(j){
var q=j.getElementsByTagName("qqmusic")||[];
if(!(q.length<=0)){
g.musicLen=q.length;
for(var y=g.reportData,b=0,w=0,C=q.length;C>w;w++){
var k=q[w],L={};
if(L.musicid=k.getAttribute("musicid"),L.comment_id=k.getAttribute("commentid"),
L.musicid&&"undefined"!=L.musicid&&L.comment_id&&"undefined"!=L.comment_id){
window.reportMid.push(L.musicid),L.media_id=k.getAttribute("mid"),L.posIndex=b++,
L.musicImgPart=k.getAttribute("albumurl")||"",L.music_img=g.imgroot+L.musicImgPart,
L.audiourl=k.getAttribute("audiourl"),L.singer=k.getAttribute("singer"),L.music_name=k.getAttribute("music_name"),
y.musicid.push(L.musicid),y.commentid.push(L.comment_id),y.hasended.push(0),y.mtitle.push(L.music_name),
y.detail_click.push(0),g.musicSupport?(L.musicSupport=!0,L.player=p({
id:L.musicid,
media_id:L.media_id,
comment_id:L.comment_id,
posIndex:L.posIndex,
play:function(){
var i=u("qqmusic_main_"+this.comment_id+"_"+this.posIndex);
i&&(_.addClass(i,"qqmusic_playing"),g.reportData.hasended[this.posIndex]=1,f.report({
type:1,
comment_id:this.comment_id,
action:4
}));
},
pause:function(){
var i=u("qqmusic_main_"+this.comment_id+"_"+this.posIndex);
i&&(_.removeClass(i,"qqmusic_playing"),f.report({
type:1,
comment_id:this.comment_id,
action:5
}));
}
})):L.musicSupport=!1;
var x=document.createElement("div");
x.innerHTML=r.render("qqmusic_tpl",L),k.parentNode.appendChild(x.children[0]),g.commentIdObj[L.comment_id]=1,
g.musicList[L.musicid+"_"+L.posIndex]=L,e(L.musicid,L.comment_id,L.posIndex,L.media_id);
}
}
return d.on(window,"unload",m),g.musicSupport&&n(),1*window.show_comment===1&&setTimeout(function(){
a();
},0),g.musicList;
}
}
}
});define("appmsg/iframe.js",["pages/version4video.js","biz_common/dom/event.js"],function(e){
"use strict";
function t(e){
var t=0;
e.contentDocument&&e.contentDocument.body.offsetHeight?t=e.contentDocument.body.offsetHeight:e.Document&&e.Document.body&&e.Document.body.scrollHeight?t=e.Document.body.scrollHeight:e.document&&e.document.body&&e.document.body.scrollHeight&&(t=e.document.body.scrollHeight);
var i=e.parentElement;
if(i&&(e.style.height=t+"px"),/MSIE\s(7|8)/.test(navigator.userAgent)&&e.contentWindow&&e.contentWindow.document){
var n=e.contentWindow.document.getElementsByTagName("html");
n&&n.length&&(n[0].style.overflow="hidden");
}
}
{
var i,n=e("pages/version4video.js"),o=e("biz_common/dom/event.js"),r=document.getElementsByTagName("iframe");
/MicroMessenger/.test(navigator.userAgent);
}
window.reportVid=[];
for(var s=0,d=r.length;d>s;++s){
i=r[s];
var c=i.getAttribute("data-src"),a=i.className||"",m=i.getAttribute("src")||c;
if(n.isShowMpVideo()&&m&&0==m.indexOf("http://v.qq.com/iframe/player.html")){
var p=m.match(/[\?&]vid\=([^&]*)/),l=p[1],u=i.parentElement.offsetWidth,f=Math.ceil(3*u/4)+41;
window.reportVid.push(l),m="/mp/videoplayer?scene=1&source=4&vid="+l+["&mid=",appmsgid,"&idx=",itemidx||idx,"&__biz=",biz,"&uin=",uin,"&key=",key,"&pass_ticket=",pass_ticket,"&scene=",source,"&version=",version,"&devicetype=",window.devicetype||""].join(""),
i.setAttribute("width",u),i.setAttribute("height",f),i.style.zIndex="",i.setAttribute("src",m);
}else if(c&&(c.indexOf("newappmsgvote")>-1&&a.indexOf("js_editor_vote_card")>=0||0==c.indexOf("http://mp.weixin.qq.com/bizmall/appmsgcard")&&a.indexOf("card_iframe")>=0||c.indexOf("appmsgvote")>-1||c.indexOf("mp.weixin.qq.com/mp/getcdnvideourl")>-1)){
if(c=c.replace(/^http:/,location.protocol),a.indexOf("card_iframe")>=0)i.setAttribute("src",c.replace("#wechat_redirect",["&uin=",uin,"&key=",key,"&pass_ticket=",pass_ticket,"&scene=",source,"&msgid=",appmsgid,"&msgidx=",itemidx||idx,"&version=",version,"&devicetype=",window.devicetype||""].join("")));else{
var g=c.indexOf("#wechat_redirect")>-1,v=["&uin=",uin,"&key=",key,"&pass_ticket=",pass_ticket].join("");
a.indexOf("vote_iframe")>=0&&(v+=["&appmsgid=",mid,"&appmsgidx=",idx].join(""));
var h=g?c.replace("#wechat_redirect",v):c+v;
i.setAttribute("src",h);
}
-1==c.indexOf("mp.weixin.qq.com/mp/getcdnvideourl")&&!function(e){
e.onload=function(){
t(e);
};
}(i),i.appmsg_idx=s;
}
if(c&&c.indexOf("mp.weixin.qq.com/mp/getcdnvideourl")>-1&&u>0){
var y=u,x=3*y/4;
i.width=y,i.height=x,i.style.setProperty&&(i.style.setProperty("width",y+"px","important"),
i.style.setProperty("height",x+"px","important"));
}
}
if(o.on(window,"resize",function(){
for(var e=document.getElementsByTagName("iframe"),t=0,i=e.length;i>t;t++){
var n=e[t],o=n.getAttribute("src");
if(o&&-1!=o.indexOf("/mp/videoplayer")){
var r=n.parentElement.offsetWidth,s=Math.ceil(3*r/4)+41;
setTimeout(function(e,t,i){
return function(){
e.setAttribute("width",t),e.setAttribute("height",i);
};
}(n,r,s),0);
}
}
},!1),window.iframe_reload=function(){
for(var e=0,n=r.length;n>e;++e){
i=r[e];
var o=i.getAttribute("src");
o&&(o.indexOf("newappmsgvote")>-1||o.indexOf("appmsgvote")>-1)&&t(i);
}
},"getElementsByClassName"in document)for(var w,b=document.getElementsByClassName("video_iframe"),s=0;w=b.item(s++);)w.setAttribute("scrolling","no"),
w.style.overflow="hidden";
});define("appmsg/review_image.js",["biz_common/dom/event.js","biz_wap/jsapi/core.js","biz_common/utils/url/parse.js","appmsg/cdn_img_lib.js"],function(e){
"use strict";
function t(e,t){
r.invoke("imagePreview",{
current:e,
urls:t
});
}
function i(e){
var i=[],r=e.container;
r=r?r.getElementsByTagName("img"):[];
for(var n=0,p=r.length;p>n;n++){
var m=r.item(n),c=m.getAttribute("data-src")||m.getAttribute("src"),o=m.getAttribute("data-type");
if(c){
for(;-1!=c.indexOf("?tp=webp");)c=c.replace("?tp=webp","");
m.dataset&&m.dataset.s&&c.isCDN()&&(c=c.replace(/\/640$/,"/0"),c=c.replace(/\/640\?/,"/0?")),
c.isCDN()&&(c=s.addParam(c,"wxfrom","3",!0)),e.is_https_res&&(c=c.http2https()),
o&&(c=s.addParam(c,"wxtype",o,!0)),i.push(c),function(e){
a.on(m,"click",function(){
return t(e,i),!1;
});
}(c);
}
}
}
var a=e("biz_common/dom/event.js"),r=e("biz_wap/jsapi/core.js"),s=e("biz_common/utils/url/parse.js");
return e("appmsg/cdn_img_lib.js"),i;
});define("appmsg/outer_link.js",["biz_common/dom/event.js"],function(e){
"use strict";
function n(e){
var n=e.container;
if(!n)return!1;
for(var r=n.getElementsByTagName("a")||[],i=0,o=r.length;o>i;++i)!function(n){
var i=r[n],o=i.getAttribute("href");
if(!o)return!1;
var a=0,c=i.innerHTML;
/^[^<>]+$/.test(c)?a=1:/^<img[^>]*>$/.test(c)&&(a=2),!!e.changeHref&&(o=e.changeHref(o,a)),
t.on(i,"click",function(){
return location.href=o,!1;
},!0);
}(i);
}
var t=e("biz_common/dom/event.js");
return n;
});define("biz_wap/jsapi/core.js",[],function(){
"use strict";
document.domain="qq.com";
var i={
ready:function(i){
"undefined"!=typeof top.window.WeixinJSBridge&&top.window.WeixinJSBridge.invoke?i():document.addEventListener?document.addEventListener("WeixinJSBridgeReady",i,!1):document.attachEvent&&(document.attachEvent("WeixinJSBridgeReady",i),
document.attachEvent("onWeixinJSBridgeReady",i));
},
invoke:function(i,e,n){
this.ready(function(){
return"object"!=typeof top.window.WeixinJSBridge?(alert("请在微信中打开此链接！"),!1):void top.window.WeixinJSBridge.invoke(i,e,n);
});
},
call:function(i){
this.ready(function(){
return"object"!=typeof top.window.WeixinJSBridge?!1:void top.window.WeixinJSBridge.call(i);
});
},
on:function(i,e){
this.ready(function(){
return"object"==typeof top.window.WeixinJSBridge&&top.window.WeixinJSBridge.on?void top.window.WeixinJSBridge.on(i,e):!1;
});
}
};
return i;
});define("biz_common/dom/event.js",[],function(){
"use strict";
function t(t,e,n,i){
a.isPc||a.isWp?o(t,"click",i,e,n):o(t,"touchend",i,function(t){
var n=t.changedTouches[0];
return Math.abs(a.y-n.clientY)<=5&&Math.abs(a.x-n.clientX)<=5?e.call(this,t):void 0;
},n);
}
function e(t,e){
if(!t||!e||t.nodeType!=t.ELEMENT_NODE)return!1;
var n=t.webkitMatchesSelector||t.msMatchesSelector||t.matchesSelector;
return n?n.call(t,e):(e=e.substr(1),t.className.indexOf(e)>-1);
}
function n(t,n,o){
for(;t&&!e(t,n);)t=t!==o&&t.nodeType!==t.DOCUMENT_NODE&&t.parentNode;
return t;
}
function o(e,o,i,r,c){
var s,d,u;
return"input"==o&&a.isPc,e?("function"==typeof i&&(c=r,r=i,i=""),"string"!=typeof i&&(i=""),
e==window&&"load"==o&&/complete|loaded/.test(document.readyState)?r({
type:"load"
}):"tap"==o?t(e,r,c,i):(s=function(t){
var e=r(t);
return e===!1&&(t.stopPropagation&&t.stopPropagation(),t.preventDefault&&t.preventDefault()),
e;
},i&&"."==i.charAt(0)&&(u=function(t){
var o=t.target||t.srcElement,r=n(o,i,e);
return r?(t.delegatedTarget=r,s(t)):void 0;
}),d=u||s,r[o+"_handler"]=d,e.addEventListener?void e.addEventListener(o,d,!!c):e.attachEvent?void e.attachEvent("on"+o,d,!!c):void 0)):void 0;
}
function i(t,e,n,o){
if(t){
var i=n[e+"_handler"]||n;
return t.removeEventListener?void t.removeEventListener(e,i,!!o):t.detachEvent?void t.detachEvent("on"+e,i,!!o):void 0;
}
}
var r=navigator.userAgent,a={
isPc:/(WindowsNT)|(Windows NT)|(Macintosh)/i.test(navigator.userAgent),
isWp:/Windows\sPhone/i.test(r)
};
return a.isPc||o(document,"touchstart",function(t){
var e=t.changedTouches[0];
a.x=e.clientX,a.y=e.clientY;
}),{
on:o,
off:i,
tap:t
};
});define("appmsg/copyright_report.js",["biz_common/dom/event.js"],function(e){
"use strict";
function n(e){
var n=["/mp/copyrightreport?action=report&biz=",biz,"&scene=",e.scene,"&card_pos=",window.__appmsgCgiData.card_pos,"&ori_username=",source_username,"&user_uin=",user_uin,"&uin=",uin,"&key=",key,"&pass_ticket=",pass_ticket,"&t=",Math.random()].join(""),o=new Image;
o.src=n.substr(0,1024);
}
function o(){
var e=__appmsgCgiData;
"2"==e.copyright_stat&&"1"==e.card_pos?n({
scene:"1",
card_pos:"1"
}):"2"==e.copyright_stat&&"0"==e.card_pos&&i.on(window,"load",function(){
i.on(window,"scroll",t);
});
}
function t(){
for(var e=window.pageYOffset||document.documentElement.scrollTop,o=r("copyright_info"),s=r("page-content"),a=0;o&&s!==o;)a+=o.offsetTop,
o=o.parentElement;
e+c.innerHeight>a&&(n({
scene:"1",
card_pos:"0"
}),i.off(window,"scroll",t),t=null);
}
function r(e){
return document.getElementById(e);
}
var i=e("biz_common/dom/event.js"),c={
innerHeight:window.innerHeight||document.documentElement.clientHeight
};
return{
card_click_report:n,
card_pv_report:o
};
});define("appmsg/async.js",["biz_common/utils/string/html.js","biz_common/dom/event.js","biz_wap/utils/ajax.js","biz_common/dom/class.js","biz_common/tmpl.js","pages/version4video.js","appmsg/cdn_img_lib.js","biz_common/utils/url/parse.js","appmsg/a.js","appmsg/like.js","appmsg/comment.js","appmsg/reward_entry.js"],function(require,exports,module){
"use strict";
function saveCopy(e){
var t={};
for(var a in e)if(e.hasOwnProperty(a)){
var n=e[a],i=typeof n;
n="string"==i?n.htmlDecode():n,"object"==i&&(n=saveCopy(n)),t[a]=n;
}
return t;
}
function fillVedio(e){
if(vedio_iframes&&vedio_iframes.length>0)for(var t,a,n,i=0,r=vedio_iframes.length;r>i;++i)t=vedio_iframes[i],
a=t.iframe,n=t.src,e&&(n=n.replace(/\&encryptVer=[^\&]*/gi,""),n=n.replace(/\&platform=[^\&]*/gi,""),
n=n.replace(/\&cKey=[^\&]*/gi,""),n=n+"&encryptVer=6.0&platform=61001&cKey="+e),
a.setAttribute("src",n);
}
function fillData(e){
var t=e.adRenderData||{
advertisement_num:0
};
if(!t.flag&&t.advertisement_num>0){
var a=t.advertisement_num,n=t.advertisement_info;
window.adDatas.num=a;
for(var i=0;a>i;++i){
var r=null,o=n[i];
if(o.biz_info=o.biz_info||{},o.app_info=o.app_info||{},o.pos_type=o.pos_type||0,
o.logo=o.logo||"",100==o.pt)r={
usename:o.biz_info.user_name,
pt:o.pt,
url:o.url,
traceid:o.traceid,
adid:o.aid,
is_appmsg:!0
};else if(102==o.pt)r={
appname:o.app_info.app_name,
versioncode:o.app_info.version_code,
pkgname:o.app_info.apk_name,
androiddownurl:o.app_info.apk_url,
md5sum:o.app_info.app_md5,
signature:o.app_info.version_code,
rl:o.rl,
traceid:o.traceid,
pt:o.pt,
type:o.type,
adid:o.aid,
is_appmsg:!0
};else if(101==o.pt)r={
appname:o.app_info.app_name,
app_id:o.app_info.app_id,
icon_url:o.app_info.icon_url,
appinfo_url:o.app_info.appinfo_url,
rl:o.rl,
traceid:o.traceid,
pt:o.pt,
ticket:o.ticket,
type:o.type,
adid:o.aid,
is_appmsg:!0
};else if(103==o.pt||104==o.pt){
var d=o.app_info.down_count||0,s=o.app_info.app_size||0,m=o.app_info.app_name||"",p=o.app_info.category,_=["万","百万","亿"];
if(d>=1e4){
d/=1e4;
for(var c=0;d>=10&&2>c;)d/=100,c++;
d=d.toFixed(1)+_[c]+"次";
}else d=d.toFixed(1)+"次";
s>=1024?(s/=1024,s=s>=1024?(s/1024).toFixed(2)+"MB":s.toFixed(2)+"KB"):s=s.toFixed(2)+"B",
p=p?p[0]||"其他":"其他";
for(var l=["-","(",":",'"',"'","：","（","—","“","‘"],f=-1,u=0,g=l.length;g>u;++u){
var v=l[u],w=m.indexOf(v);
-1!=w&&(-1==f||f>w)&&(f=w);
}
-1!=f&&(m=m.substring(0,f)),o.app_info._down_count=d,o.app_info._app_size=s,o.app_info._category=p,
o.app_info.app_name=m,r={
appname:o.app_info.app_name,
app_rating:o.app_info.app_rating||0,
app_id:o.app_info.app_id,
channel_id:o.app_info.channel_id,
md5sum:o.app_info.app_md5,
rl:o.rl,
pkgname:o.app_info.apk_name,
androiddownurl:o.app_info.apk_url,
versioncode:o.app_info.version_code,
appinfo_url:o.app_info.appinfo_url,
traceid:o.traceid,
pt:o.pt,
ticket:o.ticket,
type:o.type,
adid:o.aid,
is_appmsg:!0
};
}
var y=o.image_url;
require("appmsg/cdn_img_lib.js");
var h=require("biz_common/utils/url/parse.js");
y&&y.isCDN()&&(y=y.replace(/\/0$/,"/640"),y=y.replace(/\/0\?/,"/640?"),o.image_url=h.addParam(y,"wxfrom","50",!0)),
adDatas.ads["pos_"+o.pos_type]={
a_info:o,
adData:r
};
}
var b=function(e){
var t=window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop;
"undefined"!=typeof e&&(t=e);
10>=t&&(k.style.display="block",DomEvent.off(window,"scroll",b));
},j=document.getElementById("js_bottom_ad_area"),k=document.getElementById("js_top_ad_area"),I=adDatas.ads;
for(var D in I)if(0==D.indexOf("pos_")){
var r=I[D],o=!!r&&r.a_info;
if(r&&o)if(0==o.pos_type)j.innerHTML=TMPL.render("t_ad",o);else if(1==o.pos_type){
k.style.display="none",k.innerHTML=TMPL.render("t_ad",o),DomEvent.on(window,"scroll",b);
var x=0;
window.localStorage&&(x=1*localStorage.getItem(D)||0),window.scrollTo(0,x),b(x);
}
}
require("appmsg/a.js");
}
var z=e.appmsgstat||{};
window.appmsgstat||(window.appmsgstat=z),z.show&&(!function(){
var e=document.getElementById("js_read_area"),t=document.getElementById("like");
e.style.display="block",t.style.display="inline",z.liked&&Class.addClass(t,"praised"),
t.setAttribute("like",z.liked?"1":"0");
var a=document.getElementById("likeNum"),n=document.getElementById("readNum"),i=z.read_num,r=z.like_num;
i||(i=1),r||(r="赞"),parseInt(i)>1e5?i="100000+":"",parseInt(r)>1e5?r="100000+":"",
n&&(n.innerHTML=i),a&&(a.innerHTML=r);
}(),require("appmsg/like.js")),1==e.comment_enabled&&require("appmsg/comment.js"),
-1!=ua.indexOf("MicroMessenger")&&e.reward&&(rewardEntry=require("appmsg/reward_entry.js"),
rewardEntry.handle(e.reward,getCountPerLine()));
}
function getAsyncData(){
var is_need_ticket="";
vedio_iframes&&vedio_iframes.length>0&&(is_need_ticket="&is_need_ticket=1");
var is_need_ad=1,_adInfo=null;
if(window.localStorage)try{
var key=[biz,sn,mid,idx].join("_");
_adInfo=localStorage.getItem("adinfo_"+key);
try{
_adInfo=eval("("+_adInfo+")");
}catch(e){
_adInfo=null;
}
var _adInfoSaveTime=1*localStorage.getItem("adinfo_time_"+key),_now=+new Date;
_adInfo&&18e4>_now-1*_adInfoSaveTime&&1*_adInfo.advertisement_num>0?is_need_ad=0:(localStorage.removeItem("adinfo_"+key),
localStorage.removeItem("adinfo_time_"+key));
}catch(e){
is_need_ad=1,_adInfo=null;
}
document.getElementsByClassName&&-1!=navigator.userAgent.indexOf("MicroMessenger")||(is_need_ad=0);
var screen_num=Math.ceil(document.body.scrollHeight/(document.documentElement.clientHeight||window.innerHeight)),both_ad=screen_num>=2?1:0;
ajax({
url:"/mp/getappmsgext?__biz="+biz+"&mid="+mid+"&sn="+sn+"&idx="+idx+"&scene="+source+"&title="+encodeURIComponent(msg_title.htmlDecode())+"&ct="+ct+"&devicetype="+devicetype.htmlDecode()+"&version="+version.htmlDecode()+"&f=json&r="+Math.random()+is_need_ticket+"&is_need_ad="+is_need_ad+"&comment_id="+comment_id+"&is_need_reward="+is_need_reward+"&both_ad="+both_ad+"&reward_uin_count="+(is_need_reward?3*getCountPerLine():0),
type:"GET",
async:!0,
success:function(ret){
var tmpret=ret;
if(ret)try{
try{
ret=eval("("+tmpret+")");
}catch(e){
var img=new Image;
return void(img.src=("http://mp.weixin.qq.com/mp/jsreport?1=1&key=3&content=biz:"+biz+",mid:"+mid+",uin:"+uin+"[key3]"+encodeURIComponent(tmpret)+"&r="+Math.random()).substr(0,1024));
}
if(fillVedio(ret.appmsgticket?ret.appmsgticket.ticket:""),ret.ret)return;
var adRenderData={};
if(0==is_need_ad)adRenderData=_adInfo,adRenderData||(adRenderData={
advertisement_num:0
});else{
if(ret.advertisement_num>0&&ret.advertisement_info){
var d=ret.advertisement_info;
adRenderData.advertisement_info=saveCopy(d);
}
adRenderData.advertisement_num=ret.advertisement_num;
}
1==is_need_ad&&(window._adRenderData=adRenderData),fillData({
adRenderData:adRenderData,
appmsgstat:ret.appmsgstat,
comment_enabled:ret.comment_enabled,
reward:{
reward_total:ret.reward_total_count,
self_head_img:ret.self_head_img,
reward_head_imgs:ret.reward_head_imgs||[],
can_reward:ret.can_reward,
timestamp:ret.timestamp
}
});
}catch(e){
var img=new Image;
return img.src=("http://mp.weixin.qq.com/mp/jsreport?1=1&key=1&content=biz:"+biz+",mid:"+mid+",uin:"+uin+"[key1]"+encodeURIComponent(e.toString())+"&r="+Math.random()).substr(0,1024),
void(console&&console.error(e));
}
},
error:function(){
var e=new Image;
e.src="http://mp.weixin.qq.com/mp/jsreport?1=1&key=2&content=biz:"+biz+",mid:"+mid+",uin:"+uin+"[key2]ajax_err&r="+Math.random();
}
});
}
function getCountPerLine(){
return DomEvent.on(window,"resize",function(){
onResize(),rewardEntry&&rewardEntry.render(getCountPerLine());
}),onResize();
}
function onResize(){
var e=window.innerWidth||document.documentElement.clientWidth;
try{
e=document.getElementById("page-content").getBoundingClientRect().width;
}catch(t){}
var a=30,n=34,i=Math.floor(.9*(e-a)/n);
return document.getElementById("js_reward_inner")&&(document.getElementById("js_reward_inner").style.width=i*n+"px"),
getCountPerLine=function(){
return i;
},i;
}
require("biz_common/utils/string/html.js");
var iswifi=!1,ua=navigator.userAgent,in_mm=-1!=ua.indexOf("MicroMessenger"),DomEvent=require("biz_common/dom/event.js"),offset=200,ajax=require("biz_wap/utils/ajax.js"),Class=require("biz_common/dom/class.js"),TMPL=require("biz_common/tmpl.js"),rewardEntry,iframes=document.getElementsByTagName("iframe"),iframe,js_content=document.getElementById("js_content"),vedio_iframes=[],w=js_content.offsetWidth,h=3*w/4;
window.logs.video_cnt=0;
for(var i=0,len=iframes.length;len>i;++i){
iframe=iframes[i];
var src=iframe.getAttribute("data-src"),realsrc=iframe.getAttribute("src")||src;
if(realsrc){
var Version4video=require("pages/version4video.js");
if(!Version4video.isShowMpVideo()&&0==realsrc.indexOf("http://v.qq.com/iframe/player.html")||0==realsrc.indexOf("http://z.weishi.com/weixin/player.html")){
realsrc=realsrc.replace(/width=\d+/g,"width="+w),realsrc=realsrc.replace(/height=\d+/g,"height="+h),
in_mm||0!=realsrc.indexOf("http://v.qq.com/iframe/player.html")?iframe.setAttribute("src",realsrc):vedio_iframes.push({
iframe:iframe,
src:realsrc
}),iframe.width=w,iframe.height=h,iframe.style.setProperty&&(iframe.style.setProperty("width",w+"px","important"),
iframe.style.setProperty("height",h+"px","important")),window.logs.video_cnt++;
continue;
}
}
}
window.adDatas={
ads:{},
num:0
};
var js_toobar=document.getElementById("js_toobar"),innerHeight=window.innerHeight||document.documentElement.clientHeight,onScroll=function(){
var e=window.pageYOffset||document.documentElement.scrollTop,t=js_toobar.offsetTop;
e+innerHeight+offset>=t&&(getAsyncData(),DomEvent.off(window,"scroll",onScroll));
};
iswifi?(DomEvent.on(window,"scroll",onScroll),onScroll()):getAsyncData();
});
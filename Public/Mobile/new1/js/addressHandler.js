function setCheckBox()
{
	document.getElementById('man').style.backgroundColor = '#3492E9';
	
	document.getElementById('man').addEventListener('touchend',function(){
		document.getElementById('man').style.backgroundColor = '#3492E9';
		document.getElementById('woman').style.backgroundColor = '#EEEEEE';
	},false)
	
	document.getElementById('woman').addEventListener('touchend',function(){
		document.getElementById('man').style.backgroundColor = '#EEEEEE';
		document.getElementById('woman').style.backgroundColor = '#3492E9';
	},false)
}

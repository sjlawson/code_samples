function getDetails(args){
	var options={
		width:1060, 
		showPrint:false, 
		showClose:true, 
		outsideClickCloses:false, 
		colorTheme:'black',
		padding: 0
	}
	fb.start('/apps/accounting/ccrs/floatbox/detail/'+args, options);
	console.log(args);
}
 
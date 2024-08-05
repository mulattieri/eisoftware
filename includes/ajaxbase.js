var ajaxurl = "ajaxquery.php?tipo="; // The server-side script
function MooAjax(tipo,param){
				var url = ajaxurl; //"ajaxquery.php";
				if (param.substr(0,1)!="&"){param="&"+param;}
				/*
				param+="&ipuser="+$('ipuser').value;
				param+="&idazienda="+$('idazienda').value;
				if ($('actualstep').value=="STO") {
					param+="&collassa=true";
				}
				//ie6 tiene nella cache l'url ajax, quindi è necessario modificarlo
				param=param+ "&rndtime="+Math.random();	
				if (param.indexOf('idmarca')==-1){
					param=param+"&idmarca="+$('idmarca').value;
				}
				*/
				/**
				 * The simple way for an Ajax request, use onRequest/onComplete/onFailure
				 * to do add your own Ajax depended code.
				 */
				 //$('loading').empty().addClass('ajax-loading');
				 //var log ;
				 var booProsegui=1;
				 switch (tipo){
				 	case "UPDIDQMS":
				 		var artmp=param.split("&");
				 		var valattuale='';
				 		var i;
				 		for (i=0;i<artmp.length;i++){
				 		//for (i in artmp){
				 			var artmp2=artmp[i].split("=");
				 			
				 			if (artmp2[0]=="idqmsattuale"){
				 				//alert(artmp2[0]);
				 				valattuale=artmp2[1];
				 				if (valattuale=="undefined") valattuale="";	
				 			}
				 		}
				 		var idqms=prompt('Inserire il codice QMS', valattuale);
				 		param=param+"&idqms="+idqms;
				 		if ((idqms=='')||(idqms=='0')||(idqms==null)){
				 			booProsegui=0;
				 		}
				 		break;
				 }
				url+=tipo+param;
				if (booProsegui==1){
				 
					new Ajax(url, {
						method: 'get',
						update: 'riepilogo',
						onComplete:
						function() {
							if (tipo=="UPDIDQMS"){
								var retval="row"+$('riepilogo').innerHTML;
								$(retval).style.visibility='hidden';
								//alert($('riepilogo').innerHTML);
									
							}
							//$('ajaxloading').setStyle('visibility', 'hidden');
							//AjResponse();
						}
					}).request();
				}

}
function filtertable (phrase, _id){
	if (document.getElementById(_id)){
		var words = phrase.value.toLowerCase().split(" ");
		var table = document.getElementById(_id);
		var ele;
		for (var r = 1; r < table.rows.length; r++){
			ele = table.rows[r].innerHTML.replace(/<[^>]+>/g,"");
		        var displayStyle = 'none';
		        for (var i = 0; i < words.length; i++) {
			    if (ele.toLowerCase().indexOf(words[i])>=0)
				displayStyle = '';
			    else {
				displayStyle = 'none';
				break;
			    }
		        }
			table.rows[r].style.display = displayStyle;
		}
	}
}
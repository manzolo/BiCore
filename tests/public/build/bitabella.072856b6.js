(self.webpackChunk=self.webpackChunk||[]).push([[698],{7757:(a,e,t)=>{"use strict";t(9826),t(3210),t(2772),t(1058),t(4678);var o=t(455),n=t(7825),i=t(1369),l=t.n(i),r=t(6547),c=t(9755);document.addEventListener("DOMContentLoaded",(function(a){a.preventDefault();var e=o.Z.getMainTabella();new o.Z(e).caricatabella()})),c(document).on("click",".filterable .btn-filter",(function(a){var e=c(this).parents(".filterable").find(".filters input.colonnatabellafiltro");!0===e.prop("readonly")?(e.prop("readonly",!1),c.each(e,(function(a,e){c(this).attr("placeholder",c(this).attr("placeholder").trim()),c(this).closest("th").removeClass("sorting sorting_asc sorting_desc")})),e.first().focus()):e.val("").prop("readonly",!0)})),c(document).on("keypress",".filterable .filters input",(function(a){var e=a.keyCode||a.which;if("9"!=e&&"13"==e){var t=this.dataset.nomecontroller,n=new o.Z(t),i=new Array;c(".colonnatabellafiltro").each((function(a){if(""!=c(this).val()){var e=c(this).data("tipocampo"),t=c(this).val();if(void 0!==c(this).data("decodifiche")&&null!==c(this).data("decodifiche")){var o=c(this).data("decodifiche"),l=Array();c.each(o,(function(a,e){-1!==e.toLowerCase().indexOf(t.toLowerCase())&&l.push(a)}));var r={nomecampo:c(this).data("nomecampo"),operatore:"IN",valore:l}}else switch(e){case"string":case"text":var s=encodeURIComponent(t);r={nomecampo:c(this).data("nomecampo"),operatore:"CONTAINS",valore:s};break;case"integer":r={nomecampo:c(this).data("nomecampo"),operatore:"=",valore:parseInt(t)};break;case"decimal":r={nomecampo:c(this).data("nomecampo"),operatore:"=",valore:parseFloat(t)};break;case"boolean":var d=t.toUpperCase(),m=t;switch(d){case"SI":m=!0;break;case"NO":m=!1}r={nomecampo:c(this).data("nomecampo"),operatore:"=",valore:m};break;case"date":var b=n.getDateTimeTabella(t+" 00:00:00");r={nomecampo:c(this).data("nomecampo"),operatore:"=",valore:{date:b}};break;case"datetime":b=n.getDateTimeTabella(t),r={nomecampo:c(this).data("nomecampo"),operatore:"=",valore:{date:b}};break;default:r={nomecampo:c(this).data("nomecampo"),operatore:"=",valore:t}}i.push(r)}})),n.setDataParameterTabella("filtri",JSON.stringify(i)),n.setDataParameterTabella("paginacorrente","1"),n.caricatabella()}})),c(document).on("click","th.sorting .colonnatabellafiltro[readonly], th.sorting_asc .colonnatabellafiltro[readonly], th.sorting_desc .colonnatabellafiltro[readonly]",(function(a){var e=this.dataset.nomecampo,t=this.dataset.nomecontroller,i="ASC",l=new o.Z(t),r=l.getParametriTabellaDataset(),c=JSON.parse(n.Z.getTabellaParameter(r.colonneordinamento));void 0!==c[e]&&(i="ASC"===c[e]?"DESC":"ASC"),l.setDataParameterTabella("colonneordinamento",'{"'+e+'": "'+i+'" }'),l.caricatabella()})),c(document).ready((function(){c(document).on("click",".tabellarefresh",(function(a){a.preventDefault();var e=this.dataset.nomecontroller;new o.Z(e).caricatabella()})),c(document).on("click",".tabelladel",(function(a){a.preventDefault();var e=this.dataset.nomecontroller;new o.Z(e).eliminaselezionati()})),c(document).on("click",".paginascelta",(function(a){a.preventDefault();var e=this.dataset.nomecontroller,t=new o.Z(e);t.getParametriTabellaDataset().paginacorrente=n.Z.setTabellaParameter(this.dataset.paginascelta),t.caricatabella()})),c(document).on("click",".tabellaadd",(function(a){a.preventDefault();var e=this.dataset.nomecontroller;new o.Z(e).aggiungirecord()})),c(document).on("click",".tabelladownload",(function(a){a.preventDefault();var e=this.dataset.nomecontroller;new o.Z(e).download()})),c(document).on("click",".birimuovifiltri",(function(a){var e=this.dataset.nomecontroller,t=new o.Z(e);t.setDataParameterTabella("filtri",JSON.stringify([])),t.caricatabella()})),c(document).on("click",".bibottonieditinline",(function(a){var e=this.closest("tr").dataset.bitableid,t=c(this).closest("tr").closest("table").attr("id"),n=this.closest("tr").closest("table").dataset.nomecontroller,i=this.dataset.azione,s=c("#"+t+" > tbody > tr.inputeditinline[data-bitableid='"+e+"'] :input");if("conferma"===i){var d=Array();s.each((function(a,e){var t,o=e.closest("td").dataset.nomecampo,n=e.closest("td").dataset.tipocampo,i=c(e).attr("disabled");t="boolean"===n?c(e).is(":checked"):c(e).val(),o&&void 0===i&&d.push({fieldname:o,fieldvalue:t,fieldtype:n})}));var m=this.closest("tr").dataset.token,b=Routing.generate(n+"_aggiorna",{id:e,token:m});c.ajax({url:b,type:"POST",data:{values:d},async:!0,error:function(a,e,t){return l().alert({size:"large",closeButton:!1,title:'<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',message:r.Z.showErrori(a.responseText)}),!1},beforeSend:function(a){},success:function(a){var t=new o.Z(n);t.reseteditinline(s),c("#table"+n+" > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='"+e+"']").addClass("sr-only"),c("#table"+n+" > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella"+n+"[data-biid='"+e+"']").removeClass("sr-only"),t.caricatabella()}})}"annulla"===i&&(new o.Z(n).reseteditinline(s),c("#table"+n+" > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='"+e+"']").addClass("sr-only"),c("#table"+n+" > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella"+n+"[data-biid='"+e+"']").removeClass("sr-only"))}))}))}},0,[[7757,666,755,981,994,93,872]]]);
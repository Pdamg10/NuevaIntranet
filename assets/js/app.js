(function(){'use strict';
var CFG=Object.assign({contenido:'/data/contenido.json',noticias:'/api/noticias.php',respaldo:'/data/noticias.json',contacto:'/api/contacto.php',cv:'/api/cv.php'},window.ALCASA_CFG||{});
var $=function(s,r){return(r||document).querySelector(s)},PH='/assets/img/placeholder.svg',C={};
var esc=function(s){var d=document.createElement('div');d.textContent=s==null?'':s;return d.innerHTML};
var getJSON=function(u){return fetch(u,{cache:'no-cache'}).then(function(r){if(!r.ok)throw 0;return r.json()})};
var NAV=[['Inicio','/index.html#inicio'],['Empresa','/index.html#empresa'],['Productos','/index.html#productos'],['Sala de Prensa','/prensa.html'],['Galería','/index.html#galeria'],['Empleo','/index.html#empleo'],['Contacto','/index.html#contacto']];
function layout(){var h=$('#site-header'),f=$('#site-footer'),k=C.contacto||{};
 if(h)h.innerHTML='<header class="top"><div class="wrap"><a href="/index.html"><img src="/assets/img/logo-alcasa.png" alt="CVG Alcasa"></a><nav>'+NAV.map(function(n){return'<a href="'+n[1]+'">'+n[0]+'</a>'}).join('')+'</nav></div></header>';
 if(f)f.innerHTML='<footer><div class="wrap"><strong>CVG Aluminio del Caroní, S.A.</strong><br>'+esc(k.direccion)+'<br><a href="mailto:'+esc(k.email)+'">'+esc(k.email)+'</a> · RIF '+esc(k.rif)+'<br>© '+new Date().getFullYear()+' Todos los derechos reservados.</div></footer>'}
function norm(n){var p=function(ks){for(var i=0;i<ks.length;i++)if(n[ks[i]])return n[ks[i]];return''};
 return{titulo:p(['titulo','title']),resumen:p(['resumen','subtitulo','descripcion','extracto']),imagen:p(['imagen','portada','miniatura','imagen_url','thumbnail','foto']),fecha:p(['fecha','created_at','date']),url:p(['url','enlace','link'])||(C.contacto&&C.contacto.periodico)||'#'}}
function fmt(f){var d=new Date(f);return isNaN(d)?esc(f):d.toLocaleDateString('es-VE',{day:'numeric',month:'long',year:'numeric'})}
function card(n){return'<a class="nitem" href="'+esc(n.url)+'"><img loading="lazy" src="'+esc(n.imagen||PH)+'" alt="" onerror="this.onerror=null;this.src=\''+PH+'\'"><div><h3>'+esc(n.titulo)+'</h3>'+(n.fecha?'<time>'+fmt(n.fecha)+'</time>':'')+'<p>'+esc(n.resumen)+'</p></div></a>'}
function noticias(){var box=$('#noticias');if(!box)return;var max=+box.dataset.max||0,more=$('#mas');
 getJSON(CFG.noticias).catch(function(){return getJSON(CFG.respaldo)}).then(function(l){
  var all=(l||[]).map(norm).filter(function(n){return n.titulo}),shown=max||8;
  var draw=function(){box.innerHTML=all.slice(0,shown).map(card).join('')||'<p>No hay noticias disponibles por ahora.</p>';if(more)more.hidden=shown>=all.length||!!max};
  if(more)more.onclick=function(){shown+=8;draw()};draw()}).catch(function(){box.innerHTML='<p>No se pudieron cargar las noticias.</p>'})}
function home(){if(!$('#productos-grid'))return;
 $('#empresa-txt').textContent=C.empresa.texto;$('#mision').textContent=C.empresa.mision;$('#vision').textContent=C.empresa.vision;
 $('#productos-grid').innerHTML=C.productos.map(function(p){return'<div class="card"><img src="'+esc(p.imagen||PH)+'" alt="'+esc(p.nombre)+'"><small>'+esc(p.linea)+'</small><h3>'+esc(p.nombre)+'</h3><p>'+esc(p.desc)+'</p></div>'}).join('');
 $('#videos-grid').innerHTML=C.videos.map(function(v){return'<div class="card">'+(v.src?'<video class="video" controls preload="none" poster="'+esc(v.poster)+'" src="'+esc(v.src)+'"></video>':'<div class="video">Video próximamente</div>')+'<strong>'+esc(v.titulo)+'</strong></div>'}).join('');
 $('#interes').innerHTML='<option value="">Línea o producto de interés</option>'+C.productos.map(function(p){return'<option>'+esc(p.nombre)+'</option>'}).join('')+'<option>Comercialización</option><option>Catálogo de productos</option><option>Otro</option>';
 var enviar=function(fm,url,msg,ok){fm.onsubmit=function(e){e.preventDefault();msg.className='';msg.textContent='Enviando…';
  fetch(url,{method:'POST',body:new FormData(fm)}).then(function(r){return r.json().then(function(j){return r.ok&&j.success?j:Promise.reject(j)})})
  .then(function(){msg.className='ok';msg.textContent=ok;fm.reset();var l=$('.fname',fm);if(l)l.textContent=''})
  .catch(function(j){msg.className='err';msg.textContent=(j&&j.error)||'No se pudo enviar. Intenta nuevamente.'})}};
 enviar($('#contacto-form'),CFG.contacto,$('#msg'),'Mensaje enviado. Nos pondremos en contacto contigo.');
 var cv=$('#cv-form');if(cv){var o=function(a){return'<option value="">Selecciona…</option>'+a.map(function(x){return'<option>'+esc(x)+'</option>'}).join('')};
  $('#cv-nivel').innerHTML=o(C.cv.niveles).replace('Selecciona…','Nivel de instrucción *');$('#cv-area').innerHTML=o(C.cv.areas).replace('Selecciona…','Área de interés *');
  var fi=$('#cv-archivo');fi.onchange=function(){var f=fi.files[0],l=$('.fname',cv);if(f&&f.size>10485760){alert('El archivo supera 10 MB');fi.value='';l.textContent=''}else l.textContent=f?f.name:''};
  enviar(cv,CFG.cv,$('#cv-msg'),'Postulación recibida. Gracias por tu interés en CVG Alcasa.')}}
getJSON(CFG.contenido).then(function(d){C=d}).catch(function(){}).then(function(){layout();home();noticias();
 var pl=$('#periodico');if(pl&&C.contacto)pl.href=C.contacto.periodico})})();

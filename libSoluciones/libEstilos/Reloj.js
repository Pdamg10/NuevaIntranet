//
//
// $Id: Reloj.js,v 1.1 2009-11-17 20:34:27 ad20006 Exp $
// $Date: 2009-11-17 20:34:27 $
// $Author: ad20006 $
// $Revision: 1.1 $

var Hoy = new Date();
function Reloj() { 
    Hora = Hoy.getHours() 
    Minutos = Hoy.getMinutes() 
    Segundos = Hoy.getSeconds() 
    if (Hora<=9) Hora = "0" + Hora 
    if (Minutos<=9) Minutos = "0" + Minutos 
    if (Segundos<=9) Segundos = "0" + Segundos 
    var Script 
    Script = Hora + ":" + Minutos + ":" + Segundos 
    document.getElementById('Fecha_Reloj').innerHTML = Script
    Hoy.setSeconds(Hoy.getSeconds() +1)
    setTimeout("Reloj()",1000) 
}

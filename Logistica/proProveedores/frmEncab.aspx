<%@ Page language="c#" Codebehind="frmEncab.aspx.cs" AutoEventWireup="false" Inherits="sisProve.frmEncab" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>frmEncab</title>
		<meta name="GENERATOR" Content="Microsoft FrontPage 6.0">
		<meta name="CODE_LANGUAGE" Content="C#">
		<meta name="vs_defaultClientScript" content="JavaScript">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<LINK href="../../libSoluciones/libEstilos/cssFrm.css" type="text/css" rel="stylesheet">
<style type="text/css">		
body{
    margin: 0;
    padding: 0;
    width: 100%;
}
#hellobar-bar {
    font-family: "Open Sans", sans-serif;
    width: 100%;
    margin: 0;
    height: 30px;
    display: table;
    font-size: 17px;
    font-weight: 400;
    padding: .33em .5em;
    -webkit-font-smoothing: antialiased;
    color: #5c5e60;
    position: fixed;
    background-color: white;
    box-shadow: 0 1px 3px 2px rgba(0,0,0,0.15);
}
#hellobar-bar.regular {
    height: 30px;
    font-size: 14px;
    padding: .2em .5em;
}
.hb-content-wrapper {
    text-align: center;
    text-align: center;
    position: relative;
    display: table-cell;
    vertical-align: middle;
}
.hb-content-wrapper p {
    margin-top: 0;
    margin-bottom: 0;
}
.hb-text-wrapper {
    margin-right: .67em;
    display: inline-block;
    line-height: 1.3;
}
.hb-text-wrapper .hb-headline-text {
    font-size: 1em;
    display: inline-block;
    vertical-align: middle;
}
#hellobar-bar .hb-cta {
    display: inline-block;
    vertical-align: middle;
    margin: 5px 0;
    color: #ffffff;
    background-color: #22af73;
    border-color: #22af73
}
.hb-cta-button {
    opacity: 1;
    color: #fff;
    display: block;
    cursor: pointer;
    line-height: 1.5;
    max-width: 22.5em;
    text-align: center;
    position: relative;
    border-radius: 3px;
    white-space: nowrap;
    margin: 1.75em auto 0;
    text-decoration: none;
    padding: 0;
    overflow: hidden;
}
.hb-cta-button .hb-text-holder {
    border-radius: inherit;
    padding: 5px 15px;
}
.hb-close-wrapper {
    display: table-cell;
    width: 1.6em;
}
.hb-close-wrapper .icon-close {
    font-size: 14px;
    top: 15px;
    right: 25px;
    width: 15px;
    height: 15px;
    opacity: .3;
    color: #000;
    cursor: pointer;
    position: absolute;
    text-align: center;
    line-height: 15px;
    z-index: 1000;
    text-decoration: none;
}
</style>
		<script language="JavaScript">
			if (screen.width=="1024")
				{
				document.write('<link  rel="stylesheet" type="text/css" href="../../libSoluciones/libEstilos/ccFrm1024x768.css">');
				}
				else 
				if ( screen.width == "800")
					{
						document.write('<link  rel="stylesheet" type="text/css" media="screen" href="../../libSoluciones/libEstilos/ccFrm800x600.css">'); 
					} 
					else 
					if ( screen.width == "1280" || screen.width == "1152" )
						{
							document.write('<link  rel="stylesheet" type="text/css" media="screen" href="../../libSoluciones/libEstilos/ccFrm1152x840.css">'); 
						} 
		</script>

  <Script Language="JavaScript">

    function DameLaFechaHora() {

      var hora = new Date()

      var hrs = hora.getHours();

      var min = hora.getMinutes();

      var hoy = new Date();

      var m = new Array();

      var d = new Array()

      var an= hoy.getYear();

      m[0]="Enero";  m[1]="Febrero";  m[2]="Marzo";

      m[3]="Abril";   m[4]="Mayo";  m[5]="Junio";

      m[6]="Julio";    m[7]="Agosto";   m[8]="Septiembre";

      m[9]="Octubre";   m[10]="Noviembre"; m[11]="Diciembre";

      document.write("Son las "+hrs+":"+min+" (");

      document.write(hoy.getDate());

      document.write(" de ");

      document.write(m[hoy.getMonth()]);

      document.write(")");

    }
 
 </Script>
 
  <Script Language="JavaScript">

    function DameLaFechaHora() {

      var hora = new Date()

      var hrs = hora.getHours();

      var min = hora.getMinutes();

      var hoy = new Date();

      var m = new Array();

      var d = new Array()

      var an= hoy.getYear();

      m[0]="Enero";  m[1]="Febrero";  m[2]="Marzo";

      m[3]="Abril";   m[4]="Mayo";  m[5]="Junio";

      m[6]="Julio";    m[7]="Agosto";   m[8]="Septiembre";

      m[9]="Octubre";   m[10]="Noviembre"; m[11]="Diciembre";

      document.write("Son las "+hrs+":"+min+" (");

      document.write(hoy.getDate());

      document.write(" de ");

      document.write(m[hoy.getMonth()]);

      document.write(")");

    }

  </Script>

		
	</HEAD>
	<body MS_POSITIONING="GridLayout">
		<TABLE class="hellobar-bar" class="tablalogo" id="logoAlcasa" cellSpacing="1" cellPadding="1" width="96" border="0">
			<TR>
				<TD class="logoalcasa"></TD>
			</TR>
		</TABLE>
		<TABLE class="hellobar-bar" class="tablaCintillo" id="Encabezado" cellSpacing="1" cellPadding="1" width="776"
			border="0">
			<TR>
				<TD class="cintillo"></TD>
			</TR>
		</TABLE>
		<TABLE class="hellobar-bar"  class="tablaCvg" id="logoCvg" cellSpacing="1" cellPadding="1" width="104" border="0">
			<TR>
				<TD class="logocvg"></TD>
			</TR>
		</TABLE>
		<form id="Form1" method="post" runat="server">
			<p>
		    <img border="0" src="../../sitio/BANNER%202023/Nuevo Cintillo.png" width="802" height="57">
		    </p>
		    <hr>
		</form>

  <script>

    DameLaFechaHora();

  </script>

</body>
  
  </HTML>
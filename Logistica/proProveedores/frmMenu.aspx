<%@ Page language="c#" Codebehind="frmMenu.aspx.cs" AutoEventWireup="false" Inherits="sisProve.frmMenu" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>frmMenu</title>
		<meta content="Microsoft FrontPage 6.0" name="GENERATOR">
		<meta content="C#" name="CODE_LANGUAGE">
		<meta content="JavaScript" name="vs_defaultClientScript">
		<meta content="http://schemas.microsoft.com/intellisense/ie5" name="vs_targetSchema">
		<LINK href="../../libSoluciones/libEstilos/cssMenu.css" type="text/css" rel="stylesheet">
		<LINK href="../../libSoluciones/libEstilos/cssSpryAccordion.css" type="text/css" rel="stylesheet">
		<LINK href="../../libSoluciones/libEstilos/cssfrm.css" type="text/css" rel="stylesheet">
		<script src="../../libSoluciones/libEstilos/SpryAccordion.js" type="text/Jscript"></script>
		<script language="javascript" type="text/javascript">
	        function abrirVentana(url) 
	         {
	            window.open(url, 'rbottom')
	         }
	         
	         function cerrarse()
	         {
				window.parent.close()
			  }
				
		</script>
<STYLE type="text/css">
<!--
A:link {COLOR: gray; TEXT-DECORATION: none}
A:visited {COLOR: red; TEXT-DECORATION: none}
A:active {TEXT-DECORATION: none}
A:hover {COLOR: blue; TEXT-DECORATION: underline} -->
</STYLE>

<STYLE type="text/css">
<!--
A:link {COLOR: gray; TEXT-DECORATION: none}
A:visited {COLOR: red; TEXT-DECORATION: none}
A:active {TEXT-DECORATION: none}
A:hover {COLOR: blue; TEXT-DECORATION: underline} -->
</STYLE>
		
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
	
	</HEAD>	
	<body class="fondoPlantilla1" topMargin="30" MS_POSITIONING="GridLayout">
		<form id="Form1" method="post" runat="server">
			<!-- Inicio del menu --><A name="top"></A>
			<DIV id="total" style="Z-INDEX: 101; LEFT: 16px; WIDTH: 224px; POSITION: absolute; TOP: 200px; HEIGHT: 506px">
				<P class="oculto">&nbsp;</P>
				<DIV id="navBar">
					<DIV class="relatedLinks">
						<DIV class="Accordion" id="Acc1" style="WIDTH: 160px; HEIGHT: 382px" runat="server">
							<DIV class="AccordionPanel">
								<DIV class="AccordionPanelTab">
									<DIV align="center"> <!-- Opciones del menu -->
										<H3 onclick='abrirVentana("frmAtenc.aspx")' title="Inicio">Atención Proveedores</H3>
									</DIV>
								</DIV>
							</DIV>
							<DIV class="AccordionPanel">
								<DIV class="AccordionPanelTab">
									<DIV align="center">
										<H3 title="Formulario + Insntructivo">Inscripción</H3>
									</DIV>
								</DIV>
								<DIV class="AccordionPanelContent"><!-- Opciones del sub-menu -->
									<UL>
										<LI>
											<A href="DOC/SU-032F.doc" title="Formulario Inscripcion" target="_blank">Formulario</A>
										<LI>
											<A href="DOC/SU-032I.doc" title="Instructivo para llenar Formulario" target="_blank">
												Instructivo</A>
										</LI>
									</UL>
								</DIV>
							</DIV>
							<DIV class="AccordionPanel"><!-- Opciones del menu -->
								<DIV class="AccordionPanelTab">
									<DIV>
										<h3 onclick='abrirVentana("frmRecau.aspx")' title="Recaudos">Recaudos</h3>
									</DIV>
								</DIV>
							</DIV>
							<DIV class="AccordionPanel"><!-- Opciones del menu -->
								<DIV class="AccordionPanelTab">
									<DIV>
										<h3 onclick="cerrarse()" title="Salir de la aplicación">Salir</h3>
									</DIV>
								</DIV>
							</DIV>
						</DIV>
					</DIV>
				</DIV>
				<!-- Fin de cursos y comienzo de otros enlaces --></DIV>
			<SCRIPT type="text/javascript">
                    var acc1 = new Spry.Widget.Accordion ("Acc1", { useFixedPanelHeights: false, defaultPanel:15});
			</SCRIPT>
			<DIV>&nbsp;</DIV>
		</form>
		<DIV></DIV>
	</body>
</HTML>
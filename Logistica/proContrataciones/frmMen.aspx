<%@ Page language="c#" Codebehind="frmMen.aspx.cs" AutoEventWireup="false" Inherits="proCont.frmMen" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>frmMen</title>
		<meta name="GENERATOR" Content="Microsoft FrontPage 6.0">
		<meta name="CODE_LANGUAGE" Content="C#">
		<meta name="vs_defaultClientScript" content="JavaScript">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<script type="text/javascript" language="javascript" src="../../libSoluciones/libEstilos/SpryAccordion.js"></script>
		<meta content="http://schemas.microsoft.com/intellisense/ie5" name="vs_targetSchema">
		<LINK href="../../libSoluciones/libEstilos/cssMenu.css" type="text/css" rel="stylesheet">
		<LINK href="../../libSoluciones/libEstilos/cssSpryAccordion.css" type="text/css" rel="stylesheet">
		<LINK href="../../libSoluciones/libEstilos/cssfrm.css" type="text/css" rel="stylesheet">
		<script src="../../libSoluciones/libEstilos/SpryAccordion.js" type="text/Jscript"></script>
		<!--
		$Id: frmMen.aspx,v 1.6 2009-12-18 15:49:22 ad20006 Exp $
		$Date: 2009-12-18 15:49:22 $
		$Author: ad20006 $
		$Revision: 1.6 $
		-->
  	
	<script language="javascript" type="text/javascript"  > 	
	if (screen.width=="1024")
		{
		document.write('<link  rel="stylesheet" type="text/css" media="screen" href="../../libSoluciones/libEstilos/ccFrm1024x768.css">');
		document.write('<link  type="text/css" rel="stylesheet" href="../../libSoluciones/libEstilos/cssMenu1024x768.css">');
		document.write('<link  type="text/css" rel="stylesheet" href="../../../libSoluciones/libEstilos/cssSpryAccordion1024x768.css">');
		}
	else 
		if ( screen.width == "1152" || screen.width == "1280")
		{
		document.write('<link  rel="stylesheet" type="text/css" media="screen" href="../../libSoluciones/libEstilos/ccFrm1152x840.css">');
		document.write('<LINK  type="text/css" rel="stylesheet" href="../../libSoluciones/libEstilos/cssMenu1152x840.css">');
		document.write('<LINK  type="text/css" rel="stylesheet" href="../../libSoluciones/libEstilos/cssSpryAccordion1152x840.css">');
		} 
	else 
		if ( screen.width == "800")
		{
		document.write('<link  rel="stylesheet" type="text/css" media="screen" href="../../libSoluciones/libEstilos/ccFrm800x600.css">');
		document.write('<LINK  type="text/css" rel="stylesheet" href="../../libSoluciones/libEstilos/cssMenu800x600.css">');
		document.write('<LINK  type="text/css" rel="stylesheet" href="../../libSoluciones/libEstilos/cssAcocordion800x600.css">');
		} 
	</script>
		
		<script type="text/javascript" language="javascript">
			
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
A:link {COLOR: red; TEXT-DECORATION: none}
A:visited {COLOR: gray; TEXT-DECORATION: none}
A:active {TEXT-DECORATION: none}
A:hover {COLOR: blue; TEXT-DECORATION: underline} -->
</STYLE>
		
	</HEAD>
	
	<BODY class="fondoPlantilla1">
						<DIV id="total" style="Z-INDEX: 101; LEFT: 1px; WIDTH: 224px; POSITION: absolute; TOP: 200px; HEIGHT: 506px">
                            <P class="oculto">&nbsp;</P>						
							<DIV id="navBar">
								<DIV class="relatedLinks">
									<DIV class="Accordion" id="Acc1" style="width: 172px; height: 510px">
										<DIV align="center">
										<DIV class="AccordionPanel">
											<DIV class="AccordionPanelContent">
												<UL>
												<!--<LI>
														<A href="frmProcCA.aspx" target="rbottom">Ver Procesos</A>
													</LI>-->
												</UL> 
											</DIV>
										</DIV>
										</DIV>
										
										<DIV class="AccordionPanel">
											<DIV class="AccordionPanelTab">
												<DIV align="center">
													<H3>Concurso Abierto</H3>
												</DIV>
											</DIV>
											<DIV class="AccordionPanelContent">
												<UL>
													<LI>
														<A href="Publicaciones/frmCA2_070218.htm" target="rbottom">Manifestación de voluntad</A> 
												<!--<LI>
														<A href="frmProcCC.aspx" target="rbottom">Ver Procesos</A>
													<LI>-->
													</LI>
												</UL>
											</DIV>
										</DIV>

										<DIV class="AccordionPanel">
											<DIV class="AccordionPanelTab">
												<DIV align="center">
													<H3>Concurso Cerrado</H3>
												</DIV>
											</DIV>
											<DIV class="AccordionPanelContent">
												<UL>
													<LI>
														<A href="frmConst.aspx" target="rbottom">Invitación a Participar</A> 
												<!--<LI>
														<A href="frmProcCC.aspx" target="rbottom">Ver Procesos</A>
													<LI>-->
													</LI>
												</UL>
											</DIV>
										</DIV>
																			
										<DIV class="AccordionPanel">
											<DIV class="AccordionPanelTab">
												<DIV align="center">
													<H3>Adjudicación</H3>
												</DIV>
											</DIV>
											<DIV class="AccordionPanelContent">
												<UL>
													<LI>
														<A href="Publicaciones/frmAdj.htm" target="rbottom">Adjudicacion directa</A> 
												<!--<LI>
														<A href="frmConst.aspx" target="rbottom">Ver Proceso</A>
													<LI>-->
													</LI>
												</UL>
											</DIV>
										</DIV>
										<DIV class="AccordionPanel">
											<DIV class="AccordionPanelTab">
												<DIV align="center">
													<H3>Normativa Legal</H3> 
												</DIV>
											</DIV>
											<DIV class="AccordionPanelContent">
												<UL>
													<LI>
														<A href="frmL39165.aspx" target="rbottom">Ley N° 39.165</A>
													<LI>
														<A href="frmL39181.aspx" target="rbottom">Ley N° 39.181</A>
													<LI>
													</LI>
												</UL>
											</DIV>
										</DIV>
										<DIV class="AccordionPanelTab">
											<DIV>
												<h3 onclick='abrirVentana("frmContacto.htm")'>Contactos
												</h3>
											</DIV>
										</DIV>
										<DIV class="AccordionPanelTab">
											<DIV>
												<h3 onclick='cerrarse()'>Salir</h3>
											</DIV>
										</DIV>
									</DIV>
								</DIV>
								<DIV class="relatedLinks">
								</DIV>
								<!-- Fin de menu y comienzo de otros enlaces -->
							</DIV>
							<SCRIPT type="text/javascript">
							
							var acc1 = new Spry.Widget.Accordion ("Acc1", { useFixedPanelHeights: false, defaultPanel:25});
							</SCRIPT>
							<DIV>
								<p align="center"></DIV>
						</DIV>
			</BODY>
</HTML>
<%@ Page language="c#" Codebehind="frmPublicac.aspx.cs" AutoEventWireup="false" Inherits="proCont.frmPublicac" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>frmPublicac</title>
		<meta name="GENERATOR" Content="Microsoft Visual Studio .NET 7.1">
		<meta name="CODE_LANGUAGE" Content="C#">
		<meta name="vs_defaultClientScript" content="JavaScript">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
<!--
	$Id: frmPublicac.aspx,v 1.1 2009-11-18 20:29:17 ad20006 Exp $
	$Date: 2009-11-18 20:29:17 $
	$Author: ad20006 $
	$Revision: 1.1 $
-->


	</HEAD>
	<body MS_POSITIONING="GridLayout">
		<form id="Form1" method="post" runat="server">
			<asp:Label id="lblFirst" style="Z-INDEX: 101; LEFT: 208px; POSITION: absolute; TOP: 8px" runat="server"
				Font-Names="Arial" Font-Size="Medium" Font-Bold="True" ForeColor="#FF0000">Coordinación de Contrataciones</asp:Label>
			<asp:Label id="Label1" style="Z-INDEX: 102; LEFT: 288px; POSITION: absolute; TOP: 48px" runat="server"
				Font-Names="Arial" Font-Size="X-Large" Font-Bold="True" ForeColor="White" Font-Overline="True"
				BackColor="Red">AVISOS</asp:Label>
			<asp:HyperLink id="LinkAnexoReporte" style="Z-INDEX: 103; LEFT: 208px; POSITION: absolute; TOP: 120px"
				runat="server" Target="_blank" NavigateUrl="\\Alcasa26\procontrataciones\Publicaciones\AvisoHoyCA.pdf"
				ForeColor="Black" Font-Bold="True" Font-Names="Arial" Font-Size="X-Small" Width="304px">Manifestación de Voluntad (Concurso Abierto)</asp:HyperLink>
			<asp:HyperLink id="LinkCC" style="Z-INDEX: 104; LEFT: 216px; POSITION: absolute; TOP: 152px" runat="server"
				Target="_blank" NavigateUrl="\\Alcasa26\procontrataciones\Publicaciones\AvisoHoyCC.pdf" ForeColor="Black"
				Font-Bold="True" Font-Names="Arial" Font-Size="X-Small" Width="288px">Invitación a Participar (Concurso Cerrado)</asp:HyperLink>
		</form>
		<!--	<marquee style="FONT-WEIGHT: bold; FONT-SIZE: 12px; Z-INDEX: 105; LEFT: 8px; COLOR: #0000ff; FONT-FAMILY: Arial; POSITION: absolute; TOP: 448px; TEXT-DECORATION: blink">::: 
			Bienvenidos al Sistema de Consulta de la Coordinación de Contrataciones :::</marquee>
			-->
	</body>
</HTML>

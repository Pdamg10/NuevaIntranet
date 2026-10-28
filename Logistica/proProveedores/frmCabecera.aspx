<%@ Page language="c#" Codebehind="frmCabecera.aspx.cs" AutoEventWireup="false" Inherits="proCont.frmCabecera" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>frmCabecera</title>
		<meta name="GENERATOR" Content="Microsoft FrontPage 6.0">
		<meta name="CODE_LANGUAGE" Content="C#">
		<meta name="vs_defaultClientScript" content="JavaScript">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<!--
		$Id: frmCabecera.aspx,v 1.2 2009-11-24 14:25:25 ad20006 Exp $
		$Date: 2009-11-24 14:25:25 $
		$Author: ad20006 $
		$Revision: 1.2 $
        -->
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
	</HEAD>
	<body MS_POSITIONING="GridLayout">
		<TABLE class="tablalogo" id="logoAlcasa" cellSpacing="1" cellPadding="1" width="96" border="0">
			<TR>
				<TD class="logoalcasa"></TD>
			</TR>
		</TABLE>
		<TABLE class="tablaCintillo" id="Encabezado" cellSpacing="1" cellPadding="1" width="776"
			border="0">
			<TR>
				<TD class="cintillo"></TD>
			</TR>
		</TABLE>
		<TABLE class="tablaCvg" id="logoCvg" cellSpacing="1" cellPadding="1" width="104" border="0">
			<TR>
				<TD class="logocvg"></TD>
			</TR>
		</TABLE>
		<form id="Form1" method="post" runat="server">
			<p>
		<img border="0" src="../../sitio/BANNER%202023/Cinta%20780%20x%2054.png" width="802" height="57">
		    <hr>
		    <p>
		<asp:Label CssClass="fecha" id="lblFecha" runat="server"></asp:Label><asp:Label id="lblHora" CssClass ="hora" runat="server"></asp:Label></p>
		</form>
	</body>
</HTML>
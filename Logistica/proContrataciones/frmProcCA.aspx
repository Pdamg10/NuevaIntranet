<%@ Page language="c#" Codebehind="frmProcCA.aspx.cs" AutoEventWireup="false" Inherits="proCont.frmProcCA" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>frmProcCA</title>
		<meta name="GENERATOR" Content="Microsoft Visual Studio .NET 7.1">
		<meta name="CODE_LANGUAGE" Content="C#">
		<meta name="vs_defaultClientScript" content="JavaScript">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<!--
	$Id: frmProcCA.aspx,v 1.2 2009-11-26 13:22:19 ad20006 Exp $
	$Date: 2009-11-26 13:22:19 $
	$Author: ad20006 $
	$Revision: 1.2 $
-->
	</HEAD>
	<body MS_POSITIONING="GridLayout">
		<form id="Form1" method="post" runat="server">
			<asp:Label id="lblprueba" style="Z-INDEX: 102; LEFT: 344px; POSITION: absolute; TOP: 0px" runat="server"
				Font-Bold="True" Font-Size="Small" Font-Names="Arial" ForeColor="#FF0000" BackColor="White">Procesos Vigentes</asp:Label>
			<asp:DataGrid id="DataGrid1" style="Z-INDEX: 101; LEFT: 0px; POSITION: absolute; TOP: 56px" runat="server"
				Font-Size="XX-Small" Font-Names="Arial" BackColor="White" Width="921px" BorderColor="#CCCCCC"
				BorderStyle="None" BorderWidth="1px" CellPadding="4" ForeColor="Black" GridLines="Horizontal">
				<FooterStyle ForeColor="Black" BackColor="#CCCC99"></FooterStyle>
				<SelectedItemStyle Font-Bold="True" ForeColor="White" BackColor="#CC3333"></SelectedItemStyle>
				<HeaderStyle Font-Bold="True" ForeColor="White" BackColor="#333333"></HeaderStyle>
				<Columns>
					<asp:ButtonColumn Text="Ver" HeaderText="Documento de la Publicaci&#243;n" CommandName="Select"></asp:ButtonColumn>
				</Columns>
				<PagerStyle HorizontalAlign="Right" ForeColor="Black" BackColor="White"></PagerStyle>
			</asp:DataGrid>
			<asp:HyperLink id="LinkAnexoReporte" style="Z-INDEX: 103; LEFT: 232px; POSITION: absolute; TOP: 32px"
				runat="server" Font-Names="Arial" Font-Bold="True" Target="_blank" ToolTip="Hacer click aquí para visualizar la publicación de la contratación"
				Font-Size="X-Small">Nro. de Contratación</asp:HyperLink>
			<asp:Label id="Label1" style="Z-INDEX: 104; LEFT: 8px; POSITION: absolute; TOP: 32px" runat="server"
				Font-Names="Arial" Font-Size="X-Small" Font-Bold="True">Publicación del Concurso Abierto:</asp:Label>
		</form>
	</body>
</HTML>

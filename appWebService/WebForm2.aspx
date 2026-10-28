<%@ Page language="c#" Codebehind="WebForm2.aspx.cs" AutoEventWireup="false" Inherits="appWebService.WebForm2" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>WebForm2</title>
		<meta name="GENERATOR" Content="Microsoft Visual Studio .NET 7.1">
		<meta name="CODE_LANGUAGE" Content="C#">
		<meta name="vs_defaultClientScript" content="JavaScript">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	</HEAD>
	<body MS_POSITIONING="GridLayout">
		<form id="Form1" method="post" runat="server">
			<asp:Button style="Z-INDEX: 101; POSITION: absolute; TOP: 112px; LEFT: 112px" id="Button1" runat="server"
				Text="Invocar Web Services Personal"></asp:Button>
			<asp:TextBox style="Z-INDEX: 102; POSITION: absolute; TOP: 80px; LEFT: 184px" id="txtFicha" runat="server"></asp:TextBox>
			<asp:Label style="Z-INDEX: 103; POSITION: absolute; TOP: 80px; LEFT: 112px" id="lblFicha" runat="server">Ficha</asp:Label>
			<asp:Label style="Z-INDEX: 104; POSITION: absolute; TOP: 192px; LEFT: 120px" id="lblRespuesta"
				runat="server" Width="272px" Height="48px">Respuesta</asp:Label>
		</form>
	</body>
</HTML>

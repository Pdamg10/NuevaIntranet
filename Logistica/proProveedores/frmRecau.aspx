<%@ Page language="c#" Codebehind="frmRecau.aspx.cs" AutoEventWireup="false" Inherits="sisProve1.WebForm1" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
	<HEAD>
		<title>WebForm1</title>
		<meta content="Microsoft Visual Studio .NET 7.1" name="GENERATOR">
		<meta content="C#" name="CODE_LANGUAGE">
		<meta content="JavaScript" name="vs_defaultClientScript">
		<meta content="http://schemas.microsoft.com/intellisense/ie5" name="vs_targetSchema">
	
	<STYLE type="text/css">
          <!--
          A:link {COLOR: gray; TEXT-DECORATION: none}
          A:visited {COLOR: gray; TEXT-DECORATION: none}
          A:active {TEXT-DECORATION: none}
          A:hover {COLOR: blue; TEXT-DECORATION: underline} -->
    </STYLE>

	</HEAD>
	<body MS_POSITIONING="GridLayout">
		<form id="Form1" method="post" runat="server">
			<asp:Label id="Label1" style="Z-INDEX: 101; LEFT: 112px; POSITION: absolute; TOP: 8px" runat="server"
				Width="488px" Height="40px" Font-Size="Medium" Font-Bold="True" Font-Names="Arial" ForeColor="#C00000"> Registro de Proveedores CVG-Alcasa</asp:Label>
			<asp:Panel id="PanelDisFabNac" style="Z-INDEX: 109; LEFT: 56px; POSITION: absolute; TOP: 128px"
				runat="server" Width="632px" Height="392px" Visible="False" Font-Names="Arial" Font-Size="Smaller">
				<P><STRONG>Distribuidor Autorizado De Empresas Fabricantes Nacionales</STRONG>
				</P>
				<UL>
					<LI>
					Inscripción del Registro Nacional de Contratistas.
				    <LI>
					Acta Constitutiva y de las ultimas modificaciones.
					<LI>
					Balance General y Estado de Resultados.
					<LI>
					Solvencia del I.V.S.S.
					<LI>
					Solvencia del I.N.C.E.S.
					<LI>
					Solvencia de Hidrobolivar (si la empresa reside en la zona).
					<LI>
					Solvencia de Impuestos Municipales.
					<LI>
					Registro de Informacion Fiscal.
					<LI>
					Declaracion del ISRL.

					<LI>
					Señalar los productos y anexar Catálogos.
					<LI>
					Las marcas deben relacionarse de acuerdo a lo establecido en la carta de 
					distribución autorizada.
					<LI>
					Listado de bienes que ofrece.
					<LI>
					Listado de Clientes.
					<LI>
					Listado de Maquinarias y Equipos, modelo y capacidad.
					<LI>
					Referencias bancarias, vigentes.
					<LI>
					Referencias comerciales, vigentes.
					<LI>
					Certificado norma ISO.
					<LI>
						Otra información adicional que contribuya a su calificación.
					</LI>
				</UL>
				<P><STRONG><FONT color="#990000">Toda la documentación debe ser presentada en original y 
							copia para ser cotejado.</FONT></STRONG>&nbsp;&nbsp;</P>
				<P><STRONG></STRONG>&nbsp;</P>
			</asp:Panel>
			<asp:Panel id="PanelDisSerAut" style="Z-INDEX: 108; LEFT: 56px; POSITION: absolute; TOP: 128px"
				runat="server" Width="632px" Height="384px" Visible="False" Font-Names="Arial" Font-Size="Smaller">
				<P><STRONG>Distribuidores O Servicios Autorizados </STRONG>
				</P>
				<P><STRONG>Si&nbsp;es Persona Natural o Jurídica</STRONG>
				</P>
				<UL>
				    <LI>
					Inscripción del Registro Nacional de Contratistas.
				    <LI>
					Acta Constitutiva y de las ultimas modificaciones.
					<LI>
					Balance General y Estado de Resultados.
					<LI>
					Solvencia del I.V.S.S.
					<LI>
					Solvencia del I.N.C.E.S.
					<LI>
					Solvencia de Hidrobolivar (si la empresa reside en la zona).
					<LI>
					Solvencia de Impuestos Municipales.
					<LI>
					Registro de Informacion Fiscal.
					<LI>
					Declaracion del ISRL.
					<LI>
					Carta de Distribuidor o Servicio Autorizado otorgada por la Empresa Fabricante.
					<LI>
					Listado de bienes que ofrece.
					<LI>
						Listado del personal técnico y profesional (especialidad y experiencia).
					</LI>
				</UL>
				<P><STRONG><FONT color="#990000">Toda la documentación debe ser presentada en original y 
							copia para ser cotejado.</FONT></STRONG>&nbsp;&nbsp;</P>
				<P><STRONG></STRONG>&nbsp;</P>
			</asp:Panel>
			<asp:Panel id="PanelExtSDom" style="Z-INDEX: 107; LEFT: 56px; POSITION: absolute; TOP: 128px"
				runat="server" Width="632px" Height="376px" Visible="False" Font-Names="Arial" Font-Size="Smaller">
				<P><STRONG>Empresas Extranjeras Sin Domicilio En Venezuela </STRONG>
				</P>
				<UL>
					<LI>
					Documento constitutivo y de las últimas modificaciones estatutarias de la 
					empresa extranjera.
					<LI>
					Leyes que regulan a la empresa extranjera.
					<LI>
					Acta de Asamblea donde se designa el representante legal en Venezuela.
					<LI>
					Estados Financieros del último ejercicio económico (Balance General, Estado de 
					Resultado, expresados en moneda de curso legal (Bs.)
					<LI>
					Informe de traducción o conversión (a Bs.) de los Estados Financieros, el cual 
					debe especificar la tasa de cambio utilizada.
					<LI>
					Copia de la declaración del ISLR del último ejercicio económico.
					<LI>
					Registro de Información Fiscal (FEIN-RIF).
					<LI>
						Registro de Información Fiscal (RIF) del Representante Legal en Venezuela.
					</LI>
				</UL>
				<P><STRONG><FONT color="#003399">TODA la documentación de las Empresas Extranjeras sin 
							domicilio en Venezuela, deben presentar la documentación legalizada ante el 
							consulado de Venezuela en el país de origen.</FONT></STRONG>
				</P>
				<P><STRONG><FONT color="#990000">Toda la documentación debe ser presentada en original y 
							copia para ser cotejado.</FONT></STRONG>&nbsp;&nbsp;</P>
				<P><STRONG></STRONG>&nbsp;</P>
			</asp:Panel>
			<asp:Panel id="PanelExtCSuc" style="Z-INDEX: 106; LEFT: 56px; POSITION: absolute; TOP: 128px"
				runat="server" Width="632px" Height="368px" Visible="False" Font-Names="Arial" Font-Size="Smaller">
				<P><STRONG>Empresa Extranjera Con Sucursal En Venezuela</STRONG>
				</P>
				<UL>
					<LI>
					Documento Constitutivo y de las últimas modificaciones estatutarias de la 
					empresa extranjera.
					<LI>
					Acta de Asamblea donde se acuerda la apertura de la sucursal en Venezuela y se 
					designa el Representante Legal.
					<LI>
					Estados Financieros del último ejercicio económico (Balance General, Estado de 
					Resultado, expresados en moneda de curso legal (Bs.).
					<LI>
					Informe de traducción o conversión (a Bs.) de los Estados Financieros.
					<LI>
					Registro de Información Fiscal (FEIN-RIF) de la empresa (Si lo tuviere).
					<LI>
					Registro de Información Fiscal (RIF) del Representante Legal en Venezuela.
					<LI>
						Declaración del ISLR.
					</LI>
				</UL>
				<P><FONT color="#003399"><STRONG>Lo referente a documentación legal para las empresas 
							extrajeras debe ser traducido por un intérprete público. </STRONG></FONT>
				</P>
				<P><STRONG><FONT color="#990000">Toda la documentación debe ser presentada en original y 
							copia para ser cotejado</FONT></STRONG></P>
				<P><STRONG></STRONG>&nbsp;</P>
			</asp:Panel>
			<asp:Panel id="PanelPers" style="Z-INDEX: 105; LEFT: 56px; POSITION: absolute; TOP: 128px"
				runat="server" Width="632px" Height="360px" Visible="False" Font-Names="Arial" Font-Size="Smaller">
				<P><STRONG>Firmas Personales, Asociaciones Civiles, Fundaciones Y Personas Naturales 
						Comerciantes</STRONG>
				</P>
				<UL>
					<LI>
					Las empresas consultoras deben consignar certificado de&nbsp; inscripción en el 
					Registro Electrónico de Consultores,&nbsp; emitido por SENCAMER.
					<LI>
					Balance General y Estado de Resultados.
					<LI>
					Acta Constitutiva y de las últimas modificaciones estatutarias (no aplica para 
					las personas naturales).
					<LI>
					Síntesis Curricular (Persona Natural).
					<LI>
					Solvencia del I.V.S.S.
					<LI>
					Solvencia del I.N.C.E.S.
					<LI>
					Solvencia de Impuestos Municipales.
					<LI>
					Solvencia de Hidrobolivar (Si la empresa reside en la zona)
					<LI>
					Declaración del ISLR.
					<LI>
						Registro de Información Fiscal (RIF).
					</LI>
				</UL>
				<P><STRONG><FONT color="#990000">Toda la documentación debe ser presentada en original y 
							copia para ser cotejado.</FONT></STRONG>&nbsp;&nbsp;</P>
				<P><STRONG></STRONG>&nbsp;</P>
			</asp:Panel>
			<asp:Panel id="PanelCASASRL" style="Z-INDEX: 104; LEFT: 56px; POSITION: absolute; TOP: 128px"
				runat="server" Width="632px" Height="352px" Visible="False" Font-Names="Arial" Font-Size="Smaller">
				<P><STRONG>Compañías Anónimas, Sociedades Anónimas Y Sociedades De Responsabilidad 
						Limitada</STRONG>
				</P>
				<UL>
					<LI>
					Inscripción del Registro Nacional de Contratistas.
					<LI>
					&nbsp;Acta Constitutiva y de las últimas modificaciones.
					<LI>
					&nbsp;Balance General y Estado de Resultados.
					<LI>
					&nbsp;Solvencia del I.V.S.S.
					<LI>
					&nbsp;Solvencia del I.N.C.E.S.
					<LI>
					&nbsp;Solvencia de Impuestos Municipales.
					<LI>
					&nbsp;Solvencia de Hidrobolivar ( si la empresa reside en la zona) .
					<LI>
					&nbsp;Declaración del ISLR.
					<LI>
						&nbsp;Registro de Información Fiscal (RIF).</LI></UL>
				<P><STRONG><FONT color="#990000">&nbsp;Toda la documentación debe ser presentada en 
							original y copia para ser cotejado.</FONT></STRONG>&nbsp;
				</P>
				<P><STRONG></STRONG>&nbsp;</P>
			</asp:Panel>
			<TABLE id="Table1" style="Z-INDEX: 102; LEFT: 8px; WIDTH: 748px; POSITION: absolute; TOP: 72px; HEIGHT: 48px"
				borderColor="activeborder" cellSpacing="1" cellPadding="1" width="748" align="center"
				border="2">
				<TR>
					<TD>&nbsp;
						<asp:LinkButton id="LnkBttCoop" runat="server" Width="64px" Height="16px" ToolTip="Asociaciones Cooperativas"
							Font-Names="Arial" Font-Size="X-Small">Cooperativa</asp:LinkButton>&nbsp;
						<asp:LinkButton id="LnkBttCASASRL" runat="server" Width="104px" Height="16px" ToolTip="Compañías Anónimas, Sociedades Anónimas Y Sociedades De Responsabilidad Limitada"
							Font-Names="Arial" Font-Size="X-Small">C.A, S.A., S.R.L.</asp:LinkButton>
						<asp:LinkButton id="LnkBttPers" runat="server" Width="64px" Height="16px" ToolTip="Firmas Personales, Asociaciones Civiles, Fundaciones Y Personas Naturales Comerciantes"
							Font-Names="Arial" Font-Size="X-Small">Personales</asp:LinkButton>&nbsp;
						<asp:LinkButton id="LnkBttExtCSuc" runat="server" Width="112px" Height="16px" ToolTip="Empresa Extranjera Con Sucursal En Venezuela"
							Font-Names="Arial" Font-Size="X-Small">Extranj. C/Sucurs.</asp:LinkButton>
						<asp:LinkButton id="LnkBttExtSDom" runat="server" Width="104px" Height="16px" ToolTip="Empresas Extranjeras Sin Domicilio En Venezuela"
							Font-Names="Arial" Font-Size="X-Small">Extranj. S/Domic.</asp:LinkButton>&nbsp;
						<asp:LinkButton id="LnkBttDisSerAut" runat="server" Width="118px" Height="16px" ToolTip="Distribuidores O Servicios Autorizados"
							Font-Names="Arial" Font-Size="X-Small">Distrib. Serv. Autor.</asp:LinkButton>
						<asp:LinkButton id="LnkBttDisFabNac" runat="server" Width="120px" Height="16px" ToolTip="Distribuidor Autorizado De Empresas Fabricantes Nacionales"
							Font-Names="Arial" Font-Size="X-Small">Distrib. Fabric. Nac.</asp:LinkButton></TD>
				</TR>
			</TABLE>
			<asp:Panel id="PanelCoop" style="Z-INDEX: 103; LEFT: 56px; POSITION: absolute; TOP: 128px"
				runat="server" Width="632px" Height="344px" Visible="False" Font-Names="Arial" Font-Size="Smaller">
				<P><STRONG>Requisitos De Inscripción En El Registro De Proveedores De C.V.G. Alcasa</STRONG>
				</P>
				<P><STRONG>Asociaciones Cooperativas</STRONG>
				</P>
				<UL>
					<LI>
					Acta Constitutiva.
					<LI>
					Inscripción del Registro Nacional de Contratistas.
					<LI>
					Balance General y Estado de Resultados.
					<LI>
					Solvencia del I.V.S.S.
					<LI>
					Solvencia del I.N.C.E.S.
					<LI>
					Certificado de Fiel Cumplimiento.
					<LI>
					Solvencia de Hidrobolivar (si la empresa reside en la zona).
					<LI>
					Impuestos Municipales.
					<LI>
					Registro de Información Fiscal (RIF).
					<LI>
					Declaración del ISLR (Último ejercicio económico).
					<LI>
						Inscripción en la SUNACOOP.
					</LI>
				</UL>
				<P><FONT color="#cc0000"><STRONG>Toda la documentación debe ser presentada en original y 
							copia para ser cotejado.</STRONG></FONT>
				</P>
			</asp:Panel>
			<asp:Label id="Label2" style="Z-INDEX: 110; LEFT: 168px; POSITION: absolute; TOP: 40px" runat="server"
				Font-Bold="True" Font-Size="Medium" Height="32px" Width="344px" Font-Names="Arial" ForeColor="#C00000">Requisitos de Inscripción</asp:Label>
			<asp:Panel id="PanelMsg" style="Z-INDEX: 111; LEFT: 90px; POSITION: absolute; TOP: 120px" runat="server"
				Font-Names="Arial" Font-Bold="True" Font-Size="Medium" Height="152px" Width="540px">
				<P><FONT color="#0033ff"></FONT>&nbsp;</P>
				<P><FONT color="#ff0000">Para visualizar los recaudos, seleccione&nbsp;el tipo de 
						empresa correspondiente en los link ubicados en el recuadro superior</FONT>&nbsp;</P>
			</asp:Panel></form>
	</body>
</HTML>

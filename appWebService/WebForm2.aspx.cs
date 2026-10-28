using System;
using System.Collections;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Web;
using System.Web.SessionState;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.HtmlControls;

namespace appWebService
{
	/// <summary>
	/// Descripción breve de WebForm2.
	/// </summary>
	public class WebForm2 : System.Web.UI.Page
	{
		protected System.Web.UI.WebControls.TextBox txtFicha;
		protected System.Web.UI.WebControls.Label lblFicha;
		protected System.Web.UI.WebControls.Label lblRespuesta;
		protected System.Web.UI.WebControls.Button Button1;
	
		private void Page_Load(object sender, System.EventArgs e)
		{
			// Introducir aquí el código de usuario para inicializar la página
		}

		#region Código generado por el Diseñador de Web Forms
		override protected void OnInit(EventArgs e)
		{
			//
			// CODEGEN: llamada requerida por el Diseñador de Web Forms ASP.NET.
			//
			InitializeComponent();
			base.OnInit(e);
		}
		
		/// <summary>
		/// Método necesario para admitir el Diseñador. No se puede modificar
		/// el contenido del método con el editor de código.
		/// </summary>
		private void InitializeComponent()
		{    
			this.Button1.Click += new System.EventHandler(this.Button1_Click);
			this.Load += new System.EventHandler(this.Page_Load);

		}
		#endregion

		private void Button1_Click(object sender, System.EventArgs e)
		{
			lblRespuesta.Text = "";
			wsPersonal.Login objLogin = new wsPersonal.Login();
			objLogin.usuario = "Bayo";
			objLogin.clave = "basura";

			wsPersonal.Personal ws = new appWebService.wsPersonal.Personal();
			ws.LoginValue = objLogin;

			lblRespuesta.Text = ws.ObtenerInformacion(txtFicha.Text);
			
		}
	}
}

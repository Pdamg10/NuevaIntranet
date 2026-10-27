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

namespace proCont
{
	/// <summary>
	/// Descripción breve de frmCabecera.
	/// $Id: frmCabecera.aspx.cs,v 1.1 2009-11-18 20:29:17 ad20006 Exp $
	/// $Date: 2009-11-18 20:29:17 $
	/// $Revision: 1.1 $
	/// $Author: ad20006 $
	/// </summary>
	public class frmCabecera : System.Web.UI.Page
	{
		protected System.Web.UI.WebControls.Label lblHora;
		protected System.Web.UI.WebControls.Label lblFecha;
	
		private void Page_Load(object sender, System.EventArgs e)
		{
			
			DateTime ThisMoment;                                                                                                                                                                                                                       
			ThisMoment= DateTime.Now;

			DateTime ActualDt = DateTime.Now;
				
			
			lblFecha.Text = "Día: " + ThisMoment.ToShortDateString();


			string hora = ThisMoment.ToLongTimeString();

			lblHora.Text = "Hora : " + hora;


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
			this.Load += new System.EventHandler(this.Page_Load);

		}
		#endregion
	}
}

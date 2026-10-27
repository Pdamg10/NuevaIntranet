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
using System.Data.SqlClient;
using System.Configuration;
using System.Data.OleDb;


namespace proCont
{
	/// <summary>
	///Página de Inicio donde se tiene acceso a las publicaciones del día.
	/// $Id: frmPublicac.aspx.cs,v 1.1 2009-11-18 20:29:17 ad20006 Exp $
	/// $Date: 2009-11-18 20:29:17 $
	/// $Revision: 1.1 $
	/// $Author: ad20006 $
	/// </summary>
	public class frmPublicac : System.Web.UI.Page
	{
		protected System.Web.UI.WebControls.Label lblFirst;
		protected System.Web.UI.WebControls.LinkButton lnkAviso1;
		protected System.Web.UI.WebControls.LinkButton lnkAviso2;
		protected System.Web.UI.WebControls.Label Label1;
		protected System.Web.UI.WebControls.HyperLink LinkAnexoReporte;
		protected System.Web.UI.WebControls.LinkButton LinkAnexoReporte2;
		protected System.Web.UI.WebControls.HyperLink LinkCC;
		string conex;
	
		private void Page_Load(object sender, System.EventArgs e)
		{
				conex = ConfigurationSettings.AppSettings["StringConexionBD"];
			    //consultar();
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


//		private void consultar()
//		{
//		    //string nombrereporte="AvisoHoyCA";
//            //string nombre= nombrereporte.ToString();
//			
//				string ubicacion= "http://Alcasa26/proContrataciones/Publicaciones/AvisoHoyCA.pdf";
//				ubicacion.Trim();
//				LinkAnexoReporte.NavigateUrl =ubicacion;
//			
//		}

		
	}
}

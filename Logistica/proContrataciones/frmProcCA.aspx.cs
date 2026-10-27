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

namespace proCont
{
	/// <summary>
	/// Descripción breve de frmProcCA.
	/// $Id: frmProcCA.aspx.cs,v 1.3 2009-11-26 13:22:19 ad20006 Exp $
	/// $Date: 2009-11-26 13:22:19 $
	/// $Revision: 1.3 $
	/// $Author: ad20006 $
	/// </summary>
	public class frmProcCA : System.Web.UI.Page
	{
		protected System.Web.UI.WebControls.DataGrid DataGrid1;
		protected System.Web.UI.WebControls.Label lblprueba;
		string conex, sql, ubicacion;
		int resultado;
		protected System.Web.UI.WebControls.Label Label1;
		protected System.Web.UI.WebControls.HyperLink LinkAnexoReporte;
		
	
		private void Page_Load(object sender, System.EventArgs e)
		{
			conex = ConfigurationSettings.AppSettings["StringConexionBD"];
			cargarGrid();
		}

		private void cargarGrid()
		{
			SqlConnection miconex = new SqlConnection(conex);
			DataGrid1.SelectedIndex = -1;
			sql ="" ;
			sql = " exec ListarLicitacionesVigentes_new1 1 " ;
		    SqlDataAdapter daListar = new SqlDataAdapter(sql, miconex);
			DataSet DsProceso = new DataSet();
			daListar.Fill(DsProceso);
			DataGrid1.DataSource = DsProceso;
			DataGrid1.DataBind();
			miconex.Close();
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
			this.DataGrid1.SelectedIndexChanged += new System.EventHandler(this.DataGrid1_SelectedIndexChanged);
			this.Load += new System.EventHandler(this.Page_Load);

		}
		#endregion

		private void DataGrid1_SelectedIndexChanged(object sender, System.EventArgs e)
		{
			if (DataGrid1.SelectedIndex != -1)
			{
				string nrolicitacion = DataGrid1.Items[DataGrid1.SelectedIndex].Cells[1].Text.Trim();
				SqlConnection micon = new SqlConnection(conex);
				sql = "exec sp_consultar_ubicacion '" + nrolicitacion + "'";
				SqlCommand cmdbuscar= new SqlCommand(sql,micon);
				micon.Open();
				SqlDataReader myreader = cmdbuscar.ExecuteReader();
				if (myreader.Read())
				{ 
					resultado = Convert.ToInt32((myreader.GetValue(0)));
					if (resultado == -1)
					{
						RegisterStartupScript( "","<script language = javascript> alert( 'No existe publicación para este proceso' ); </script>" ) ;
					}
					else
					{
						ubicacion = Convert.ToString((myreader.GetValue(1)));
                    	string nombre= nrolicitacion;
						nombre.Trim();
						LinkAnexoReporte.Text=nombre;
						ubicacion.Trim();
						LinkAnexoReporte.NavigateUrl=ubicacion;
					}
					myreader.Close(); 
				}
				micon.Close() ; 
			}
			else
			{
				RegisterStartupScript("param1","<script language=javascript>alert('Debe seleccionar un registro para ver la publicación');</script>");
				return;
			}
	
		}
	}
}

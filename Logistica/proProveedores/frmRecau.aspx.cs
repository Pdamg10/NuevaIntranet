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

namespace sisProve1
{
	/// <summary>
	/// $Id: frmRecau.aspx.cs,v 1.1 2009-11-19 13:27:58 eb15322 Exp $
	/// $Date: 2009-11-19 13:27:58 $
	/// $Author: eb15322 $
	/// $Revision: 1.1 $
	/// 
	/// Descripción: Recaudos
	/// </summary>
	public class WebForm1 : System.Web.UI.Page
	{
		protected System.Web.UI.WebControls.LinkButton LnkBttCoop;
		protected System.Web.UI.WebControls.Panel PanelCoop;
		protected System.Web.UI.WebControls.Panel PanelCASASRL;
		protected System.Web.UI.WebControls.LinkButton LnkBttPers;
		protected System.Web.UI.WebControls.LinkButton LnkBttCASASRL;
		protected System.Web.UI.WebControls.LinkButton LnkBttExtSDom;
		protected System.Web.UI.WebControls.LinkButton LnkBttExtCSuc;
		protected System.Web.UI.WebControls.LinkButton LnkBttDisFabNac;
		protected System.Web.UI.WebControls.Panel PanelPers;
		protected System.Web.UI.WebControls.Panel PanelExtCSuc;
		protected System.Web.UI.WebControls.Panel PanelExtSDom;
		protected System.Web.UI.WebControls.Panel PanelDisSerAut;
		protected System.Web.UI.WebControls.Panel PanelDisFabNac;
		protected System.Web.UI.WebControls.Label Label2;
		protected System.Web.UI.WebControls.LinkButton LnkBttDisSerAut;
		protected System.Web.UI.WebControls.Panel PanelMsg;
		protected System.Web.UI.WebControls.Label Label1;
	
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
			this.LnkBttCoop.Click += new System.EventHandler(this.LnkBttCoop_Click);
			this.LnkBttCASASRL.Click += new System.EventHandler(this.LnkBttCASASRL_Click);
			this.LnkBttPers.Click += new System.EventHandler(this.LnkBttPers_Click);
			this.LnkBttExtCSuc.Click += new System.EventHandler(this.LnkBttExtCSuc_Click);
			this.LnkBttExtSDom.Click += new System.EventHandler(this.LnkBttExtSDom_Click);
			this.LnkBttDisSerAut.Click += new System.EventHandler(this.LnkBttDisSerAut_Click);
			this.LnkBttDisFabNac.Click += new System.EventHandler(this.LnkBttDisFabNac_Click);
			this.Load += new System.EventHandler(this.Page_Load);

		}
		#endregion

		private void LnkBttCoop_Click(object sender, System.EventArgs e)
		{
			PanelCoop.Visible = true;
			PanelCASASRL.Visible = false;
			PanelPers.Visible = false;
			PanelExtCSuc.Visible = false;
			PanelExtSDom.Visible = false;
			PanelDisSerAut.Visible = false;
			PanelDisFabNac.Visible = false;
			PanelMsg.Visible = false;


		}

		private void LnkBttCASASRL_Click(object sender, System.EventArgs e)
		{
			PanelCoop.Visible = false;
			PanelCASASRL.Visible = true;
			PanelPers.Visible = false;
			PanelExtCSuc.Visible = false;
			PanelExtSDom.Visible = false;
			PanelDisSerAut.Visible = false;
			PanelDisFabNac.Visible = false;
			PanelMsg.Visible = false;
		}

		private void LnkBttPers_Click(object sender, System.EventArgs e)
		{
			PanelCoop.Visible = false;
			PanelCASASRL.Visible = false;
			PanelPers.Visible = true;
			PanelExtCSuc.Visible = false;
			PanelExtSDom.Visible = false;
			PanelDisSerAut.Visible = false;
			PanelDisFabNac.Visible = false;
			PanelMsg.Visible = false;
		}

		private void LnkBttExtCSuc_Click(object sender, System.EventArgs e)
		{
			PanelCoop.Visible = false;
			PanelCASASRL.Visible = false;
			PanelPers.Visible = false;
			PanelExtCSuc.Visible = true;
			PanelExtSDom.Visible = false;
			PanelDisSerAut.Visible = false;
			PanelDisFabNac.Visible = false;
			PanelMsg.Visible = false;
		}

		private void LnkBttExtSDom_Click(object sender, System.EventArgs e)
		{
			PanelCoop.Visible = false;
			PanelCASASRL.Visible = false;
			PanelPers.Visible = false;
			PanelExtCSuc.Visible = false;
			PanelExtSDom.Visible = true;
			PanelDisSerAut.Visible = false;
			PanelDisFabNac.Visible = false;
			PanelMsg.Visible = false;
		}

		private void LnkBttDisSerAut_Click(object sender, System.EventArgs e)
		{
			PanelCoop.Visible = false;
			PanelCASASRL.Visible = false;
			PanelPers.Visible = false;
			PanelExtCSuc.Visible = false;
			PanelExtSDom.Visible = false;
			PanelDisSerAut.Visible = true;
			PanelDisFabNac.Visible = false;
			PanelMsg.Visible = false;
		}

		private void LnkBttDisFabNac_Click(object sender, System.EventArgs e)
		{
			PanelCoop.Visible = false;
			PanelCASASRL.Visible = false;
			PanelPers.Visible = false;
			PanelExtCSuc.Visible = false;
			PanelExtSDom.Visible = false;
			PanelDisSerAut.Visible = false;
			PanelDisFabNac.Visible = true;
			PanelMsg.Visible = false;
		}
	}
}
